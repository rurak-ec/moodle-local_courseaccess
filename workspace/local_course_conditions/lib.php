<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Library functions for Course Conditionals plugin
 *
 * @package    local_course_conditions
 * @copyright  2025 Rurak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// ============================================================================
// CONDITION MANAGEMENT FUNCTIONS
// ============================================================================

/**
 * Get all conditions for a course with their options
 *
 * Retrieves all condition records for a specific course and nests
 * the corresponding options for each condition. This is used by both
 * configure.php (admin UI) and the observer (student modal injection).
 *
 * @param int $courseid Course ID
 * @return array Array of condition objects, each with an 'options' property
 *               containing an array of option objects
 */
function local_course_conditions_get_conditions_for_course($courseid)
{
    global $DB;

    // Fetch all conditions for this course, ordered by sort order
    $conditions = $DB->get_records(
        'local_course_conditions_conditions',
        ['courseid' => $courseid],
        'sortorder ASC, timecreated ASC'
    );

    // For each condition, fetch and attach its options
    foreach ($conditions as $condition) {
        $condition->options = $DB->get_records(
            'local_course_conditions_options',
            ['conditionid' => $condition->id],
            'sortorder ASC, id ASC'
        );
    }

    return $conditions;
}

/**
 * Save conditions for a course (Transactional)
 *
 * @param int $courseid Course ID
 * @param array $conditions_data Array of condition objects/arrays from form
 * @return bool Success
 * @throws moodle_exception If validation fails
 */
function local_course_conditions_save_conditions($courseid, $conditions_data)
{
    global $DB;

    $transaction = $DB->start_delegated_transaction();

    try {
        // Get existing conditions to handle deletions
        $existing_conditions = $DB->get_records('local_course_conditions_conditions', ['courseid' => $courseid]);
        $processed_ids = [];

        foreach ($conditions_data as $cdata) {
            // Validation: Check minimum options
            if (empty($cdata['options']) || count($cdata['options']) < 2) {
                throw new moodle_exception('error_minoptions', 'local_course_conditions', '', $cdata['name']);
            }

            // Prepare condition record
            $condition = new stdClass();
            $condition->courseid = $courseid;
            $condition->name = $cdata['name'];
            $condition->description = isset($cdata['description']) ? $cdata['description'] : '';
            $condition->custom_id = isset($cdata['custom_id']) && !empty($cdata['custom_id']) ? $cdata['custom_id'] : (string)$courseid;
            $condition->enabled = isset($cdata['enabled']) ? (int)$cdata['enabled'] : 1;
            $condition->sortorder = isset($cdata['sortorder']) ? $cdata['sortorder'] : 0;
            $condition->timemodified = time();

            // Check if custom_id changed (for migration)
            $old_custom_id = null;
            if (!empty($cdata['id']) && isset($existing_conditions[$cdata['id']])) {
                $old_condition = $existing_conditions[$cdata['id']];
                if ($old_condition->custom_id != $condition->custom_id) {
                    $old_custom_id = $old_condition->custom_id;
                }
            }

            if (!empty($cdata['id']) && isset($existing_conditions[$cdata['id']])) {
                // Update existing
                $condition->id = $cdata['id'];
                $DB->update_record('local_course_conditions_conditions', $condition);
                $conditionid = $condition->id;
                $processed_ids[] = $conditionid;

                // If custom_id changed, migrate profile fields
                if ($old_custom_id !== null) {
                    local_course_conditions_migrate_profile_field($courseid, $conditionid, $old_custom_id, $condition->custom_id);
                }
            } else {
                // Create new
                $condition->timecreated = time();
                $conditionid = $DB->insert_record('local_course_conditions_conditions', $condition);
            }

            // Handle Options
            $existing_options = $DB->get_records('local_course_conditions_options', ['conditionid' => $conditionid]);
            $processed_option_ids = [];

            foreach ($cdata['options'] as $odata) {
                // Validation: Check option fields
                if (empty($odata['name']) || empty($odata['value'])) {
                    throw new moodle_exception('error_optionfields', 'local_course_conditions');
                }

                $option = new stdClass();
                $option->conditionid = $conditionid;
                $option->name = $odata['name'];
                $option->value = $odata['value'];
                $option->sortorder = isset($odata['sortorder']) ? $odata['sortorder'] : 0;
                $option->timemodified = time();

                if (!empty($odata['id']) && isset($existing_options[$odata['id']])) {
                    // Update option
                    $option->id = $odata['id'];
                    $DB->update_record('local_course_conditions_options', $option);
                    $processed_option_ids[] = $option->id;
                } else {
                    // Create option
                    $option->timecreated = time();
                    $DB->insert_record('local_course_conditions_options', $option);
                }
            }

            // Delete removed options
            foreach ($existing_options as $optid => $opt) {
                if (!in_array($optid, $processed_option_ids)) {
                    // Check if used in selections? For now, just delete or maybe block?
                    // Ideally we should check, but for this refactor we assume overwrite.
                    $DB->delete_records('local_course_conditions_options', ['id' => $optid]);
                }
            }

            // Ensure profile field exists
            local_course_conditions_create_profile_field($courseid, $conditionid, $condition->name, $condition->custom_id);
        }

        // Delete removed conditions
        foreach ($existing_conditions as $condid => $cond) {
            if (!in_array($condid, $processed_ids)) {
                // Delete options first
                $DB->delete_records('local_course_conditions_options', ['conditionid' => $condid]);
                // Delete condition
                $DB->delete_records('local_course_conditions_conditions', ['id' => $condid]);
                // Note: We leave selections and profile data as orphan for now or cleanup?
                // Cleanup is safer for privacy
                $DB->delete_records('local_course_conditions_selections', ['conditionid' => $condid]);
            }
        }

        $transaction->allow_commit();
        return true;

    } catch (Exception $e) {
        $transaction->rollback($e);
        throw $e;
    }
}

// ============================================================================
// PROFILE FIELD MANAGEMENT
// ============================================================================

/**
 * Create or update a custom user profile field for a condition
 *
 * This function creates a Moodle custom user profile field that will store
 * the student's selection for this condition. The field is used by Moodle's
 * native access restriction system to control visibility of activities.
 *
 * The profile field is:
 * - Locked (only plugin can modify it)
 * - Visible to user and teachers
 * - Has shortname format: cond_{custom_id}_{conditionid}
 * - Has name format: {CourseShortname}
 *
 * @param int $courseid Course ID
 * @param int $conditionid Condition ID
 * @param string $conditionname Condition Name (e.g., "Turno", "Grupo")
 * @param string $custom_id Custom identifier (defaults to courseid)
 * @return int|false Field ID if successful, false if course not found
 */
function local_course_conditions_create_profile_field($courseid, $conditionid, $conditionname, $custom_id = null)
{
    global $DB, $CFG;
    require_once($CFG->dirroot . '/user/profile/lib.php');

    $course = $DB->get_record('course', ['id' => $courseid], 'fullname, shortname');
    if (!$course)
        return false;

    // Use custom_id if provided, otherwise default to courseid
    if ($custom_id === null) {
        $custom_id = (string)$courseid;
    }

    // Generate a stable shortname using custom_id
    $shortname = "cond_{$custom_id}_{$conditionid}";

    // Check if custom field category exists
    $category = $DB->get_record('user_info_category', ['name' => 'Condiciones de Curso']);
    if (!$category) {
        $category = new stdClass();
        $category->name = 'Condiciones de Curso';
        $category->sortorder = $DB->count_records('user_info_category') + 1;
        $category->id = $DB->insert_record('user_info_category', $category);
    }

    // Check if field already exists
    $field = $DB->get_record('user_info_field', ['shortname' => $shortname]);
    if ($field) {
        // Update name if changed
        if ($field->name !== $course->shortname) {
            $field->name = $course->shortname;
            $DB->update_record('user_info_field', $field);
        }
        return $field->id;
    }

    // Create custom profile field
    $field = new stdClass();
    $field->shortname = $shortname;
    $field->name = $course->shortname;
    $field->datatype = 'text';
    $field->description = "Condition '{$conditionname}' for course '{$course->fullname}'";
    $field->descriptionformat = FORMAT_HTML;
    $field->categoryid = $category->id;
    $field->sortorder = $DB->count_records('user_info_field', ['categoryid' => $category->id]) + 1;
    $field->required = 0;
    $field->locked = 1;
    $field->visible = 2; // Visible to user and teachers
    $field->forceunique = 0;
    $field->signup = 0;
    $field->defaultdata = '';
    $field->defaultdataformat = FORMAT_HTML;
    $field->param1 = 30;
    $field->param2 = 2048;
    $field->param3 = 0;
    $field->param4 = 0;
    $field->param5 = 0;

    return $DB->insert_record('user_info_field', $field);
}

/**
 * Save user selection to their profile field
 *
 * This function updates the Moodle user profile field with the selected
 * option's value. This value is then used by Moodle's access restrictions
 * to show/hide activities based on the student's selection.
 *
 * @param int $userid User ID
 * @param int $courseid Course ID
 * @param int $conditionid Condition ID
 * @param string $value The option value to save (e.g., "turno_manana")
 * @return bool True on success, false if profile field doesn't exist
 */
function local_course_conditions_save_to_profile($userid, $courseid, $conditionid, $value)
{
    global $DB, $CFG;
    require_once($CFG->dirroot . '/user/profile/lib.php');

    // Find the profile field by its shortname
    $shortname = "cond_{$courseid}_{$conditionid}";
    $field = $DB->get_record('user_info_field', ['shortname' => $shortname]);

    if (!$field)
        return false;

    // Check if user already has a value for this field
    $data = $DB->get_record('user_info_data', ['userid' => $userid, 'fieldid' => $field->id]);

    if ($data) {
        // Update existing value
        $data->data = $value;
        $DB->update_record('user_info_data', $data);
    } else {
        // Create new value
        $data = new stdClass();
        $data->userid = $userid;
        $data->fieldid = $field->id;
        $data->data = $value;
        $data->dataformat = 0;
        $DB->insert_record('user_info_data', $data);
    }
    return true;
}

// ============================================================================
// SELECTION FUNCTIONS
// ============================================================================

/**
 * Save user selection for a condition
 *
 * This function performs two key actions:
 * 1. Saves the selection to the plugin's selections table (for history tracking)
 * 2. Saves the option value to the user's profile field (for Moodle restrictions)
 *
 * Called by ajax.php when student selects an option via the modal.
 *
 * @param int $userid User ID
 * @param int $courseid Course ID
 * @param int $conditionid Condition ID
 * @param int $optionid Selected option ID
 * @return bool True on success
 * @throws dml_missing_record_exception If option doesn't exist
 */
function local_course_conditions_save_selection($userid, $courseid, $conditionid, $optionid)
{
    global $DB;

    // Get the option to retrieve its value
    $option = $DB->get_record('local_course_conditions_options', ['id' => $optionid], '*', MUST_EXIST);

    // Check if user already has a selection for this condition
    $existing = $DB->get_record('local_course_conditions_selections', [
        'userid' => $userid,
        'conditionid' => $conditionid
    ]);

    if ($existing) {
        // Update existing selection
        $existing->optionid = $optionid;
        $existing->timemodified = time();
        $DB->update_record('local_course_conditions_selections', $existing);
    } else {
        // Create new selection
        $selection = new stdClass();
        $selection->userid = $userid;
        $selection->courseid = $courseid;
        $selection->conditionid = $conditionid;
        $selection->optionid = $optionid;
        $selection->timecreated = time();
        $selection->timemodified = time();
        $DB->insert_record('local_course_conditions_selections', $selection);
    }

    // CRITICAL: Save to profile field for Moodle's access restrictions
    local_course_conditions_save_to_profile($userid, $courseid, $conditionid, $option->value);

    return true;
}

/**
 * Check if user has selected ALL required conditions for a course
 * Check if user has completed all condition selections for a course
 *
 * This function determines whether a student should see the modal popup.
 * It checks the profile field directly (not the selections table) to ensure
 * the value is actually saved in the field that Moodle uses.
 *
 * Called by observer.php before deciding to inject the modal JavaScript.
 *
 * @param int $userid User ID
 * @param int $courseid Course ID
 * @return bool True if all conditions are completed (or no conditions exist)
 */
function local_course_conditions_user_has_completed_all($userid, $courseid)
{
    global $DB;

    // Get all conditions for this course
    $conditions = $DB->get_records('local_course_conditions_conditions', ['courseid' => $courseid]);

    if (empty($conditions)) {
        return true; // No conditions defined = nothing to complete
    }

    // Check each condition to see if user has a non-empty value in profile field
    foreach ($conditions as $condition) {
        // Build the profile field shortname
        $shortname = "cond_{$courseid}_{$condition->id}";

        // Check if profile field has a non-empty value
        // We join user_info_data with user_info_field to find the right field
        $sql = "SELECT d.id
                FROM {user_info_data} d
                JOIN {user_info_field} f ON f.id = d.fieldid
                WHERE d.userid = :userid 
                  AND f.shortname = :shortname 
                  AND " . $DB->sql_isNotEmpty('user_info_data', 'data', false, true);

        // If no data found or data is empty, user hasn't completed this condition
        if (!$DB->record_exists_sql($sql, ['userid' => $userid, 'shortname' => $shortname])) {
            return false;
        }
    }

    // All conditions have values
    return true;
}

// ============================================================================
// NAVIGATION
// ============================================================================

function local_course_conditions_extend_navigation_course($navigation, $course, $context)
{
    global $USER;

    // Add configuration link for teachers/managers
    if (has_capability('local/course_conditions:configure', $context)) {
        $url = new moodle_url('/local/course_conditions/configure.php', ['courseid' => $course->id]);
        $node = navigation_node::create(
            get_string('configureconditions', 'local_course_conditions'),
            $url,
            navigation_node::TYPE_SETTING,
            null,
            'local_course_conditions_configure',
            new pix_icon('i/settings', '')
        );
        $navigation->add_node($node);
        return;
    }

    // Add "Change Selection" link for students (if conditions exist and user has made a selection)
    if (!is_siteadmin()) {
        $conditions = local_course_conditions_get_conditions_for_course($course->id);
        if (!empty($conditions)) {
            $condition = reset($conditions);

            // Check if user has made a selection
            if (local_course_conditions_user_has_completed_all($USER->id, $course->id)) {
                $url = new moodle_url('/local/course_conditions/change_selection.php', ['courseid' => $course->id]);
                $node = navigation_node::create(
                    get_string('changeselection_nav', 'local_course_conditions', $condition->name),
                    $url,
                    navigation_node::TYPE_SETTING,
                    null,
                    'local_course_conditions_change',
                    new pix_icon('i/edit', '')
                );
                $navigation->add_node($node);
            }
        }
    }
}

// ============================================================================
// MODAL INJECTION
// ============================================================================

/**
 * Inject blocking modal JavaScript for condition selection
 *
 * This function is called by the observer when a student enters a course
 * but hasn't completed their condition selection. It prepares the condition
 * data and injects an AMD JavaScript module that displays a blocking modal.
 *
 * The modal will:
 * - Block all page interaction
 * - Display a dropdown with the condition's options
 * - Save the selection via AJAX
 * - Reload the page on success
 *
 * @param int $courseid Course ID
 * @param object $condition Condition object (must have 'options' property)
 */
function local_course_conditions_inject_modal($courseid, $condition)
{
    global $PAGE;

    // Prepare data structure for JavaScript module
    $data = [
        'courseid' => $courseid,
        'condition' => [
            'id' => $condition->id,
            'name' => $condition->name,
            'description' => $condition->description ?? ''
        ],
        'options' => []
    ];

    // Format options for dropdown
    foreach ($condition->options as $option) {
        $data['options'][] = [
            'id' => $option->id,
            'name' => $option->name  // Display name (e.g., "Mañana")
        ];
    }

    // Inject AMD JavaScript module with condition data
    // Module: local_course_conditions/condition_modal
    // Method: init
    $PAGE->requires->js_call_amd('local_course_conditions/condition_modal', 'init', [$data]);
}

/**
 * Get options for a specific condition
 *
 * @param int $conditionid Condition ID
 * @return array Array of option objects
 */
function local_course_conditions_get_options_for_condition($conditionid)
{
    global $DB;
    return $DB->get_records('local_course_conditions_options', ['conditionid' => $conditionid], 'sortorder ASC');
}

/**
 * Get user's current selection for a condition
 *
 * Returns an object with the user's selection details including:
 * - option_id: ID of selected option
 * - option_name: Display name of selected option
 * - option_value: Technical value stored in profile
 * - time_selected: Timestamp of selection
 *
 * @param int $userid User ID
 * @param int $courseid Course ID
 * @param int $conditionid Condition ID
 * @return stdClass|null Selection object or null if no selection exists
 */
function local_course_conditions_get_user_selection($userid, $courseid, $conditionid)
{
    global $DB;

    $sql = "SELECT s.id, s.optionid as option_id, o.name as option_name,
                   o.value as option_value, s.timecreated as time_selected
            FROM {local_course_conditions_selections} s
            JOIN {local_course_conditions_options} o ON o.id = s.optionid
            WHERE s.userid = :userid
              AND s.courseid = :courseid
              AND s.conditionid = :conditionid";

    return $DB->get_record_sql($sql, [
        'userid' => $userid,
        'courseid' => $courseid,
        'conditionid' => $conditionid
    ]);
}

// ============================================================================
// CUSTOM ID AND MIGRATION FUNCTIONS
// ============================================================================

/**
 * Migrate profile field shortnames when custom_id changes
 *
 * When a teacher changes the custom_id for a condition, this function:
 * 1. Updates the profile field shortname
 * 2. Migrates all student data from old field to new field
 * 3. Cleans up the old profile field
 *
 * This ensures that student selections remain intact and access restrictions
 * continue working with the new field name.
 *
 * @param int $courseid Course ID
 * @param int $conditionid Condition ID
 * @param string $old_custom_id Previous custom ID
 * @param string $new_custom_id New custom ID
 * @return int Number of user records migrated
 * @throws moodle_exception If migration fails
 */
function local_course_conditions_migrate_profile_field($courseid, $conditionid, $old_custom_id, $new_custom_id)
{
    global $DB;

    $transaction = $DB->start_delegated_transaction();

    try {
        // Generate old and new shortnames
        $old_shortname = "cond_{$old_custom_id}_{$conditionid}";
        $new_shortname = "cond_{$new_custom_id}_{$conditionid}";

        // Get old profile field
        $old_field = $DB->get_record('user_info_field', ['shortname' => $old_shortname]);
        if (!$old_field) {
            // Old field doesn't exist, nothing to migrate
            $transaction->allow_commit();
            return 0;
        }

        // Check if new field already exists (shouldn't happen, but be safe)
        $new_field = $DB->get_record('user_info_field', ['shortname' => $new_shortname]);
        if ($new_field) {
            // New field exists, we need to merge data
            $old_data_records = $DB->get_records('user_info_data', ['fieldid' => $old_field->id]);
            foreach ($old_data_records as $old_data) {
                // Check if user already has data in new field
                $existing_new_data = $DB->get_record('user_info_data', [
                    'userid' => $old_data->userid,
                    'fieldid' => $new_field->id
                ]);

                if ($existing_new_data) {
                    // Update existing
                    $existing_new_data->data = $old_data->data;
                    $DB->update_record('user_info_data', $existing_new_data);
                } else {
                    // Insert new
                    $new_data = new stdClass();
                    $new_data->userid = $old_data->userid;
                    $new_data->fieldid = $new_field->id;
                    $new_data->data = $old_data->data;
                    $new_data->dataformat = 0;
                    $DB->insert_record('user_info_data', $new_data);
                }
            }
            $migrated_count = count($old_data_records);
        } else {
            // Simpler case: just rename the field
            $old_field->shortname = $new_shortname;
            $DB->update_record('user_info_field', $old_field);

            // Count migrated records
            $migrated_count = $DB->count_records('user_info_data', ['fieldid' => $old_field->id]);
        }

        // Delete old field data and field if we merged
        if ($new_field) {
            $DB->delete_records('user_info_data', ['fieldid' => $old_field->id]);
            $DB->delete_records('user_info_field', ['id' => $old_field->id]);
        }

        $transaction->allow_commit();
        return $migrated_count;

    } catch (Exception $e) {
        $transaction->rollback($e);
        throw new moodle_exception('migrationfailed', 'local_course_conditions', '', $e->getMessage());
    }
}
