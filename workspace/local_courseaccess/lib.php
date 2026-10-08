<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Library functions for Course Conditionals plugin
 *
 * @package    local_courseaccess
 * @copyright  2025 Rurak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * In-request memory cache for local_courseaccess to avoid redundant DB queries.
 */
class local_courseaccess_runtime_cache {
    /** @var array<int, array> Cache of conditions per course */
    public static $conditions = [];
    /** @var array<string, bool> Cache of completion status [userid_courseid => bool] */
    public static $completion = [];
    /** @var array<string, stdClass|null> Cache of pending condition [userid_courseid => stdClass|null] */
    public static $pending = [];

    /**
     * Reset all cached values (e.g. after database updates or during unit tests).
     */
    public static function reset(): void {
        self::$conditions = [];
        self::$completion = [];
        self::$pending = [];
    }
}

// PROFILE FIELD SHORTNAME (single source of truth).

/**
 * Build the custom profile-field shortname for a condition.
 *
 * This is the ONE place that decides the shortname format. Every other function
 * (field creation, saving, completion checks, migration) must call this so the
 * formula can never diverge again.
 *
 * Format: acc_{custom_id}_{conditionid}, where custom_id defaults to the
 * course id when not set on the condition.
 *
 * @param int $courseid Course ID (fallback when the condition has no custom_id)
 * @param int $conditionid Condition ID
 * @param string|null $customid Optional pre-resolved custom_id. When null, it is
 *                               looked up from the condition record.
 * @return string The profile-field shortname
 */
function local_courseaccess_get_field_shortname($courseid, $conditionid = null, $customid = null) {
    return "acc_{$courseid}";
}

/**
 * Default custom_id for a course: the course ID as string.
 *
 * @param stdClass|int $course Course record (with ->id) or course id
 * @return string
 */
function local_courseaccess_default_customid($course) {
    if (is_object($course)) {
        return (string)$course->id;
    }
    return (string)$course;
}

// LIFECYCLE HELPERS (activation reset, restriction detection, custom_id lock).

/**
 * Clear every student's stored value for a condition's profile field.
 *
 * Used when a condition is (re)activated: emptying the profile field makes
 * local_courseaccess_user_has_completed_all() return false for everyone,
 * so the selection modal is shown again to all students. The selections table
 * is left intact (it is overwritten when each student re-picks).
 *
 * @param int $courseid Course ID
 * @param int $conditionid Condition ID
 * @return int Number of profile-data rows cleared
 */
function local_courseaccess_reset_profile_values($courseid, $conditionid) {
    global $DB, $CFG, $USER;

    $shortname = local_courseaccess_get_field_shortname($courseid, $conditionid);
    $field = $DB->get_record('user_info_field', ['shortname' => $shortname]);
    if (!$field) {
        return 0;
    }

    $count = $DB->count_records('user_info_data', ['fieldid' => $field->id]);
    $DB->delete_records('user_info_data', ['fieldid' => $field->id]);

    if (!empty($USER->id)) {
        require_once($CFG->dirroot . '/user/profile/lib.php');
        profile_load_custom_fields($USER);
    }
    require_once($CFG->dirroot . '/course/lib.php');
    get_fast_modinfo($courseid, 0, true);

    return $count;
}

/**
 * Count course activities/sections whose access restrictions reference this
 * condition's profile field.
 *
 * Moodle core stores a custom-field availability condition as
 * {"type":"profile","cf":"<shortname>",...} inside course_modules.availability
 * and course_sections.availability. We match the exact "cf":"<shortname>" token.
 *
 * @param int $courseid Course ID
 * @param int $conditionid Condition ID
 * @return int Number of activities + sections restricting by this field
 */
function local_courseaccess_count_field_restrictions($courseid, $conditionid) {
    global $DB;

    $shortname = local_courseaccess_get_field_shortname($courseid, $conditionid);
    $needle = '%' . $DB->sql_like_escape('"cf":"' . $shortname . '"') . '%';

    $sqlmods = "SELECT COUNT(*) FROM {course_modules}
                 WHERE course = :courseid AND " . $DB->sql_like('availability', ':needle');
    $mods = $DB->count_records_sql($sqlmods, ['courseid' => $courseid, 'needle' => $needle]);

    $sqlsecs = "SELECT COUNT(*) FROM {course_sections}
                 WHERE course = :courseid AND " . $DB->sql_like('availability', ':needle2');
    $secs = $DB->count_records_sql($sqlsecs, ['courseid' => $courseid, 'needle2' => $needle]);

    return (int)$mods + (int)$secs;
}

/**
 * Whether the custom_id (and thus the profile-field shortname) must stay fixed.
 *
 * Changing the custom_id renames the profile field. Moodle core stores access
 * restrictions by the field's shortname, so a rename orphans every existing
 * restriction (the activity would become hidden for all users). Therefore the
 * custom_id is only safe to change before the field is in use: no restrictions
 * reference it, no student has a stored value, and no selection exists.
 *
 * @param int $courseid Course ID
 * @param int $conditionid Condition ID
 * @return bool True if the custom_id must be treated as immutable
 */
function local_courseaccess_customid_is_locked($courseid, $conditionid) {
    global $DB;

    if (local_courseaccess_count_field_restrictions($courseid, $conditionid) > 0) {
        return true;
    }

    if ($DB->record_exists('local_courseaccess_sel', ['conditionid' => $conditionid])) {
        return true;
    }

    $shortname = local_courseaccess_get_field_shortname($courseid, $conditionid);
    $field = $DB->get_record('user_info_field', ['shortname' => $shortname]);
    if ($field && $DB->record_exists('user_info_data', ['fieldid' => $field->id])) {
        return true;
    }

    return false;
}

// CONDITION MANAGEMENT FUNCTIONS.

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
function local_courseaccess_get_conditions_for_course($courseid) {
    global $DB;

    if (isset(local_courseaccess_runtime_cache::$conditions[$courseid])) {
        return local_courseaccess_runtime_cache::$conditions[$courseid];
    }

    // Fetch all conditions for this course, ordered by sort order.
    $conditions = $DB->get_records(
        'local_courseaccess_cond',
        ['courseid' => $courseid],
        'sortorder ASC, timecreated ASC'
    );

    if (empty($conditions)) {
        local_courseaccess_runtime_cache::$conditions[$courseid] = [];
        return [];
    }

    // Single query to fetch all options for all conditions in this course.
    $condids = array_keys($conditions);
    [$insql, $inparams] = $DB->get_in_or_equal($condids, SQL_PARAMS_NAMED);
    $alloptions = $DB->get_records_select(
        'local_courseaccess_options',
        "conditionid $insql",
        $inparams,
        'sortorder ASC, id ASC'
    );

    foreach ($conditions as $condition) {
        $condition->options = [];
    }
    foreach ($alloptions as $opt) {
        if (isset($conditions[$opt->conditionid])) {
            $conditions[$opt->conditionid]->options[$opt->id] = $opt;
        }
    }

    local_courseaccess_runtime_cache::$conditions[$courseid] = $conditions;
    return $conditions;
}

/**
 * Save conditions for a course (Transactional)
 *
 * @param int $courseid Course ID
 * @param array $conditionsdata Array of condition objects/arrays from form
 * @return bool Success
 * @throws moodle_exception If validation fails
 */
function local_courseaccess_save_conditions($courseid, $conditionsdata) {
    global $DB;

    $transaction = $DB->start_delegated_transaction();

    try {
        // Get existing conditions to handle deletions.
        $existingconditions = $DB->get_records('local_courseaccess_cond', ['courseid' => $courseid]);
        $processedids = [];

        foreach ($conditionsdata as $cdata) {
            // Validation: Check minimum options.
            if (empty($cdata['options']) || count($cdata['options']) < 2) {
                throw new moodle_exception('error_minoptions', 'local_courseaccess', '', $cdata['name']);
            }

            $isupdate = !empty($cdata['id']) && isset($existingconditions[$cdata['id']]);
            $oldcondition = $isupdate ? $existingconditions[$cdata['id']] : null;
            $wasenabled = $oldcondition ? !empty($oldcondition->enabled) : false;

            // Prepare condition record.
            $condition = new stdClass();
            $condition->courseid = $courseid;
            $condition->name = $cdata['name'];
            $condition->description = isset($cdata['description']) ? $cdata['description'] : '';
            $condition->custom_id = (string)$courseid;
            $condition->enabled = isset($cdata['enabled']) ? (int)$cdata['enabled'] : 1;
            $condition->allowchange = isset($cdata['allowchange']) ? (int)$cdata['allowchange'] : 0;
            $condition->sortorder = isset($cdata['sortorder']) ? $cdata['sortorder'] : 0;
            $condition->timemodified = time();

            // Activation requires the minimum valid configuration: a name and at.
            // Least 2 options, each with a display name and a technical value.
            if (!empty($condition->enabled)) {
                $validoptions = 0;
                foreach ($cdata['options'] as $o) {
                    if (!empty($o['name']) && !empty($o['value'])) {
                        $validoptions++;
                    }
                }
                if (trim((string)$condition->name) === '' || $validoptions < 2) {
                    throw new moodle_exception('error_activate_requirements', 'local_courseaccess');
                }
            }

            if (!empty($cdata['id']) && isset($existingconditions[$cdata['id']])) {
                // Update existing.
                $condition->id = $cdata['id'];
                $DB->update_record('local_courseaccess_cond', $condition);
                $conditionid = $condition->id;
                $processedids[] = $conditionid;
            } else {
                // Create new.
                $condition->timecreated = time();
                $conditionid = $DB->insert_record('local_courseaccess_cond', $condition);
            }

            // Handle Options.
            $existingoptions = $DB->get_records('local_courseaccess_options', ['conditionid' => $conditionid]);
            $processedoptionids = [];

            foreach ($cdata['options'] as $odata) {
                // Validation: Check option fields.
                if (empty($odata['name']) || empty($odata['value'])) {
                    throw new moodle_exception('error_optionfields', 'local_courseaccess');
                }

                $option = new stdClass();
                $option->conditionid = $conditionid;
                $option->name = $odata['name'];
                $option->value = $odata['value'];
                $option->sortorder = isset($odata['sortorder']) ? $odata['sortorder'] : 0;
                $option->timemodified = time();

                if (!empty($odata['id']) && isset($existingoptions[$odata['id']])) {
                    // Update option.
                    $option->id = $odata['id'];
                    $DB->update_record('local_courseaccess_options', $option);
                    $processedoptionids[] = $option->id;
                } else {
                    // Create option.
                    $option->timecreated = time();
                    $newid = $DB->insert_record('local_courseaccess_options', $option);
                    $processedoptionids[] = $newid;
                }
            }

            // Delete removed options, but never an option a student has already.
            // Selected: doing so would leave selections.optionid dangling and.
            // Break get_user_selection()/change_selection.php. In-use options.
            // Are kept so existing selections (and access restrictions) survive.
            foreach ($existingoptions as $optid => $opt) {
                if (!in_array($optid, $processedoptionids)) {
                    if ($DB->record_exists('local_courseaccess_sel', ['optionid' => $optid])) {
                        continue;  // Option is in use; keep it to preserve referential integrity.
                    }
                    $DB->delete_records('local_courseaccess_options', ['id' => $optid]);
                }
            }

            // Ensure profile field exists.
            local_courseaccess_create_profile_field($courseid, $conditionid, $condition->name, $condition->custom_id);

            // Reactivation (inactive to active) clears everyone's stored value so the
            // selection modal is shown again to all students. Selection rows are kept
            // and get overwritten when each student re-picks.
            if ($isupdate && !$wasenabled && !empty($condition->enabled)) {
                local_courseaccess_reset_profile_values($courseid, $conditionid);
            }
        }

        // Delete removed conditions.
        foreach ($existingconditions as $condid => $cond) {
            if (!in_array($condid, $processedids)) {
                // Delete options first.
                $DB->delete_records('local_courseaccess_options', ['conditionid' => $condid]);
                // Delete condition.
                $DB->delete_records('local_courseaccess_cond', ['id' => $condid]);
                // Note: We leave selections and profile data as orphan for now or cleanup?
                // Cleanup is safer for privacy.
                $DB->delete_records('local_courseaccess_sel', ['conditionid' => $condid]);
            }
        }

        $transaction->allow_commit();
        local_courseaccess_runtime_cache::reset();
        return true;
    } catch (Exception $e) {
        $transaction->rollback($e);
        throw $e;
    }
}

// PROFILE FIELD MANAGEMENT.

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
 * - Has shortname format: acc_{custom_id}_{conditionid}
 * - Has name format: {CourseShortname}
 *
 * @param int $courseid Course ID
 * @param int $conditionid Condition ID
 * @param string $conditionname Condition Name (e.g., "Turno", "Grupo")
 * @param string $customid Custom identifier (defaults to courseid)
 * @return int|false Field ID if successful, false if course not found
 */
function local_courseaccess_create_profile_field($courseid, $conditionid, $conditionname, $customid = null) {
    global $DB, $CFG;
    require_once($CFG->dirroot . '/user/profile/lib.php');

    $course = $DB->get_record('course', ['id' => $courseid], 'fullname, shortname');
    if (!$course) {
        return false;
    }

    // Generate a stable shortname (single source of truth in get_field_shortname).
    $shortname = local_courseaccess_get_field_shortname($courseid, $conditionid, $customid);

    // Check if custom field category exists.
    $category = $DB->get_record('user_info_category', ['name' => get_string('profilefieldcategory', 'local_courseaccess')]);
    if (!$category) {
        $category = new stdClass();
        $category->name = get_string('profilefieldcategory', 'local_courseaccess');
        $category->sortorder = $DB->count_records('user_info_category') + 1;
        $category->id = $DB->insert_record('user_info_category', $category);
    }

    // Check if field already exists.
    $field = $DB->get_record('user_info_field', ['shortname' => $shortname]);
    if ($field) {
        // Update name if changed.
        if ($field->name !== $shortname) {
            $field->name = $shortname;
            $DB->update_record('user_info_field', $field);
        }
        return $field->id;
    }

    // Create custom profile field.
    $field = new stdClass();
    $field->shortname = $shortname;
    $field->name = $shortname;
    $field->datatype = 'text';
    $field->description = "Course access '{$conditionname}' for course ID {$courseid}";
    $field->descriptionformat = FORMAT_HTML;
    $field->categoryid = $category->id;
    $field->sortorder = $DB->count_records('user_info_field', ['categoryid' => $category->id]) + 1;
    $field->required = 0;
    $field->locked = 1;
    $field->visible = 2;  // Visible to user and teachers.
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
function local_courseaccess_save_to_profile($userid, $courseid, $conditionid, $value) {
    global $DB, $CFG, $USER;
    require_once($CFG->dirroot . '/user/profile/lib.php');

    // Find the profile field by its shortname (respects the condition's custom_id).
    $shortname = local_courseaccess_get_field_shortname($courseid, $conditionid);
    $field = $DB->get_record('user_info_field', ['shortname' => $shortname]);

    if (!$field) {
        $condition = $DB->get_record('local_courseaccess_cond', ['id' => $conditionid]);
        if ($condition) {
            local_courseaccess_create_profile_field($courseid, $conditionid, $condition->name, $condition->custom_id);
            $field = $DB->get_record('user_info_field', ['shortname' => $shortname]);
        }
        if (!$field) {
            return false;
        }
    }

    // Check if user already has a value for this field.
    $data = $DB->get_record('user_info_data', ['userid' => $userid, 'fieldid' => $field->id]);

    if ($data) {
        // Update existing value.
        $data->data = $value;
        $DB->update_record('user_info_data', $data);
    } else {
        // Create new value.
        $data = new stdClass();
        $data->userid = $userid;
        $data->fieldid = $field->id;
        $data->data = $value;
        $data->dataformat = 0;
        $DB->insert_record('user_info_data', $data);
    }

    // Refresh active session profile data if updating the currently logged-in user.
    if (!empty($USER->id) && (int)$USER->id === (int)$userid) {
        if (!isset($USER->profile) || !is_array($USER->profile)) {
            $USER->profile = [];
        }
        $USER->profile[$shortname] = $value;
        profile_load_custom_fields($USER);
    }

    // Invalidate course modinfo cache so section availability updates immediately.
    require_once($CFG->dirroot . '/course/lib.php');
    get_fast_modinfo($courseid, 0, true);

    local_courseaccess_runtime_cache::reset();

    return true;
}

// SELECTION FUNCTIONS.

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
function local_courseaccess_save_selection($userid, $courseid, $conditionid, $optionid) {
    global $DB;

    // Get the option to retrieve its value.
    $option = $DB->get_record('local_courseaccess_options', ['id' => $optionid], '*', MUST_EXIST);

    // Check if user already has a selection for this condition.
    $existing = $DB->get_record('local_courseaccess_sel', [
        'userid' => $userid,
        'conditionid' => $conditionid,
    ]);

    if ($existing) {
        // Update existing selection.
        $existing->optionid = $optionid;
        $existing->timemodified = time();
        $DB->update_record('local_courseaccess_sel', $existing);
    } else {
        // Create new selection.
        $selection = new stdClass();
        $selection->userid = $userid;
        $selection->courseid = $courseid;
        $selection->conditionid = $conditionid;
        $selection->optionid = $optionid;
        $selection->timecreated = time();
        $selection->timemodified = time();
        $DB->insert_record('local_courseaccess_sel', $selection);
    }

    // CRITICAL: Save to profile field for Moodle's access restrictions.
    local_courseaccess_save_to_profile($userid, $courseid, $conditionid, $option->value);

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
function local_courseaccess_user_has_completed_all($userid, $courseid) {
    global $DB, $USER;

    $cachekey = $userid . '_' . $courseid;
    if (isset(local_courseaccess_runtime_cache::$completion[$cachekey])) {
        return local_courseaccess_runtime_cache::$completion[$cachekey];
    }

    // Get all conditions for this course (uses runtime cache).
    $conditions = local_courseaccess_get_conditions_for_course($courseid);

    if (empty($conditions)) {
        local_courseaccess_runtime_cache::$completion[$cachekey] = true;
        return true;  // No conditions defined = nothing to complete.
    }

    // Fast path: if checking current session user and custom fields are in session, check directly.
    $checkuserprofile = (!empty($USER->id) && (int)$USER->id === (int)$userid && isset($USER->profile) && is_array($USER->profile));

    // Check each condition to see if user has a non-empty value in profile field.
    foreach ($conditions as $condition) {
        // Build the profile field shortname (reuse the already-loaded custom_id).
        $shortname = local_courseaccess_get_field_shortname($courseid, $condition->id, $condition->custom_id);

        if ($checkuserprofile && array_key_exists($shortname, $USER->profile)) {
            $val = $USER->profile[$shortname];
            if ($val === '' || $val === null || $val === false) {
                local_courseaccess_runtime_cache::$completion[$cachekey] = false;
                return false;
            }
            continue;
        }

        // Check if profile field has a non-empty value.
        // We join user_info_data with user_info_field to find the right field.
        $sql = "SELECT d.id
                FROM {user_info_data} d
                JOIN {user_info_field} f ON f.id = d.fieldid
                WHERE d.userid = :userid
                  AND f.shortname = :shortname
                  AND " . $DB->sql_isnotempty('user_info_data', 'data', false, true);

        // If no data found or data is empty, user hasn't completed this condition.
        if (!$DB->record_exists_sql($sql, ['userid' => $userid, 'shortname' => $shortname])) {
            local_courseaccess_runtime_cache::$completion[$cachekey] = false;
            return false;
        }
    }

    // All conditions have values.
    local_courseaccess_runtime_cache::$completion[$cachekey] = true;
    return true;
}

// NAVIGATION.

/**
 * Add the plugin's nodes to the course navigation.
 *
 * Teachers/managers get a "Configure conditions" link; students get a
 * "Change selection" link when the condition allows changes and they have
 * already chosen.
 *
 * @param navigation_node $navigation The course navigation node
 * @param stdClass $course The course record
 * @param \context_course $context The course context
 * @return void
 */
function local_courseaccess_extend_navigation_course($navigation, $course, $context) {
    global $USER;

    // Add configuration link for teachers/managers.
    if (has_capability('local/courseaccess:configure', $context)) {
        $url = new moodle_url('/local/courseaccess/configure.php', ['courseid' => $course->id]);
        $node = navigation_node::create(
            get_string('configureconditions', 'local_courseaccess'),
            $url,
            navigation_node::TYPE_SETTING,
            null,
            'local_courseaccess_configure',
            new pix_icon('i/settings', '')
        );
        $navigation->add_node($node);
        return;
    }

    // Add "Change Selection" link for students (if conditions exist and user has made a selection).
    if (!is_siteadmin()) {
        $conditions = local_courseaccess_get_conditions_for_course($course->id);
        if (!empty($conditions)) {
            $condition = reset($conditions);

            // Only offer the "change selection" link if the teacher allows changes.
            // For this condition AND the student has already made a selection.
            if (
                !empty($condition->allowchange)
                    && local_courseaccess_user_has_completed_all($USER->id, $course->id)
            ) {
                $url = new moodle_url('/local/courseaccess/change_selection.php', ['courseid' => $course->id]);
                $node = navigation_node::create(
                    get_string('changeselection_nav', 'local_courseaccess', $condition->name),
                    $url,
                    navigation_node::TYPE_SETTING,
                    null,
                    'local_courseaccess_change',
                    new pix_icon('i/edit', '')
                );
                $navigation->add_node($node);
            }
        }

        // Fallback modal injection for Moodle versions < 4.4 (where Output Hooks API does not exist).
        if (!class_exists('\core\hook\manager', false)) {
            $pending = local_courseaccess_get_pending_condition($USER->id, $course->id);
            if ($pending !== null) {
                local_courseaccess_inject_modal($course->id, $pending);
            }
        }
    }
}

// MODAL INJECTION.

/**
 * Decide whether a user must still complete a condition for a course.
 *
 * Centralises all the "should the blocking modal be shown?" skip rules so the
 * output hook and any other caller share one decision path:
 *  - Site admins never see the modal.
 *  - Users who can configure conditions (teachers/managers) never see it.
 *  - Users who already completed the selection never see it.
 *  - Courses without a condition, or with the condition disabled, show nothing.
 *
 * @param int $userid User ID
 * @param int $courseid Course ID
 * @return stdClass|null The pending condition (with its options), or null when
 *                       no modal should be shown.
 */
function local_courseaccess_get_pending_condition($userid, $courseid) {
    $cachekey = $userid . '_' . $courseid;
    if (array_key_exists($cachekey, local_courseaccess_runtime_cache::$pending)) {
        return local_courseaccess_runtime_cache::$pending[$cachekey];
    }

    if (is_siteadmin($userid)) {
        local_courseaccess_runtime_cache::$pending[$cachekey] = null;
        return null;
    }

    $context = \context_course::instance($courseid);
    if (has_capability('local/courseaccess:configure', $context, $userid)) {
        local_courseaccess_runtime_cache::$pending[$cachekey] = null;
        return null;
    }

    if (local_courseaccess_user_has_completed_all($userid, $courseid)) {
        local_courseaccess_runtime_cache::$pending[$cachekey] = null;
        return null;
    }

    $conditions = local_courseaccess_get_conditions_for_course($courseid);
    if (empty($conditions)) {
        local_courseaccess_runtime_cache::$pending[$cachekey] = null;
        return null;
    }

    $condition = reset($conditions);

    if (empty($condition->enabled)) {
        // Condition paused = no modal. NOTE: per-activity access restrictions are.
        // Managed by Moodle core (availability) and are NOT affected by this flag.
        local_courseaccess_runtime_cache::$pending[$cachekey] = null;
        return null;
    }

    local_courseaccess_runtime_cache::$pending[$cachekey] = $condition;
    return $condition;
}

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
function local_courseaccess_inject_modal($courseid, $condition) {
    global $PAGE;

    // Prepare data structure for JavaScript module.
    $data = [
        'courseid' => $courseid,
        'condition' => [
            'id' => $condition->id,
            'name' => $condition->name,
            'description' => $condition->description ?? '',
        ],
        'options' => [],
    ];

    // Format options for dropdown.
    foreach ($condition->options as $option) {
        $data['options'][] = [
            'id' => $option->id,
            'name' => $option->name, // Display name (for example "Morning").
        ];
    }

    // Inject AMD JavaScript module with condition data.
    // Module: local_courseaccess/condition_modal.
    // Method: init.
    $PAGE->requires->js_call_amd('local_courseaccess/condition_modal', 'init', [$data]);
}

/**
 * Get options for a specific condition
 *
 * @param int $conditionid Condition ID
 * @return array Array of option objects
 */
function local_courseaccess_get_options_for_condition($conditionid) {
    global $DB;
    return $DB->get_records('local_courseaccess_options', ['conditionid' => $conditionid], 'sortorder ASC');
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
function local_courseaccess_get_user_selection($userid, $courseid, $conditionid) {
    global $DB;

    $sql = "SELECT s.id, s.optionid as option_id, o.name as option_name,
                   o.value as option_value, s.timecreated as time_selected
            FROM {local_courseaccess_sel} s
            JOIN {local_courseaccess_options} o ON o.id = s.optionid
            WHERE s.userid = :userid
              AND s.courseid = :courseid
              AND s.conditionid = :conditionid";

    return $DB->get_record_sql($sql, [
        'userid' => $userid,
        'courseid' => $courseid,
        'conditionid' => $conditionid,
    ]);
}

// CUSTOM ID AND MIGRATION FUNCTIONS.

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
 * @param string $oldcustomid Previous custom ID
 * @param string $newcustomid New custom ID
 * @return int Number of user records migrated
 * @throws moodle_exception If migration fails
 */
function local_courseaccess_migrate_profile_field($courseid, $conditionid, $oldcustomid, $newcustomid) {
    global $DB;

    $transaction = $DB->start_delegated_transaction();

    try {
        // Generate old and new shortnames (single source of truth).
        $oldshortname = local_courseaccess_get_field_shortname($courseid, $conditionid, $oldcustomid);
        $newshortname = local_courseaccess_get_field_shortname($courseid, $conditionid, $newcustomid);

        // Get old profile field.
        $oldfield = $DB->get_record('user_info_field', ['shortname' => $oldshortname]);
        if (!$oldfield) {
            // Old field doesn't exist, nothing to migrate.
            $transaction->allow_commit();
            return 0;
        }

        // Check if new field already exists (shouldn't happen, but be safe).
        $newfield = $DB->get_record('user_info_field', ['shortname' => $newshortname]);
        if ($newfield) {
            // New field exists, we need to merge data.
            $olddatarecords = $DB->get_records('user_info_data', ['fieldid' => $oldfield->id]);
            foreach ($olddatarecords as $olddata) {
                // Check if user already has data in new field.
                $existingnewdata = $DB->get_record('user_info_data', [
                    'userid' => $olddata->userid,
                    'fieldid' => $newfield->id,
                ]);

                if ($existingnewdata) {
                    // Update existing.
                    $existingnewdata->data = $olddata->data;
                    $DB->update_record('user_info_data', $existingnewdata);
                } else {
                    // Insert new.
                    $newdata = new stdClass();
                    $newdata->userid = $olddata->userid;
                    $newdata->fieldid = $newfield->id;
                    $newdata->data = $olddata->data;
                    $newdata->dataformat = 0;
                    $DB->insert_record('user_info_data', $newdata);
                }
            }
            $migratedcount = count($olddatarecords);
        } else {
            // Simpler case: just rename the field.
            $oldfield->shortname = $newshortname;
            $DB->update_record('user_info_field', $oldfield);

            // Count migrated records.
            $migratedcount = $DB->count_records('user_info_data', ['fieldid' => $oldfield->id]);
        }

        // Delete old field data and field if we merged.
        if ($newfield) {
            $DB->delete_records('user_info_data', ['fieldid' => $oldfield->id]);
            $DB->delete_records('user_info_field', ['id' => $oldfield->id]);
        }

        $transaction->allow_commit();
        return $migratedcount;
    } catch (Exception $e) {
        $transaction->rollback($e);
        throw new moodle_exception('migrationfailed', 'local_courseaccess', '', $e->getMessage());
    }
}

/**
 * Change a user's selection for a condition and log to history.
 *
 * @param int $userid User ID
 * @param int $courseid Course ID
 * @param int $conditionid Condition ID
 * @param int $optionid New option ID
 * @param int|null $changedby ID of the user performing the change (defaults to $userid)
 * @return bool True on success
 * @throws moodle_exception If validation or database operation fails
 */
function local_courseaccess_change_user_selection(
    int $userid,
    int $courseid,
    int $conditionid,
    int $optionid,
    ?int $changedby = null
): bool {
    global $DB;

    $changedby = $changedby ?? $userid;

    // Verify option belongs to this condition.
    $option = $DB->get_record('local_courseaccess_options', [
        'id' => $optionid,
        'conditionid' => $conditionid,
    ], '*', MUST_EXIST);

    $transaction = $DB->start_delegated_transaction();

    try {
        // Retrieve current selection if exists.
        $selection = $DB->get_record('local_courseaccess_sel', [
            'userid' => $userid,
            'conditionid' => $conditionid,
        ]);

        $oldoptionid = $selection ? (int)$selection->optionid : null;

        if ($selection) {
            $selection->optionid = $optionid;
            $selection->timemodified = time();
            $DB->update_record('local_courseaccess_sel', $selection);
        } else {
            $selection = new stdClass();
            $selection->userid = $userid;
            $selection->courseid = $courseid;
            $selection->conditionid = $conditionid;
            $selection->optionid = $optionid;
            $selection->timecreated = time();
            $selection->timemodified = time();
            $DB->insert_record('local_courseaccess_sel', $selection);
        }

        // Update profile field for availability restrictions.
        local_courseaccess_save_to_profile($userid, $courseid, $conditionid, $option->value);

        // Record history entry with all required fields (including courseid!).
        $history = new stdClass();
        $history->userid = $userid;
        $history->courseid = $courseid;
        $history->conditionid = $conditionid;
        $history->old_optionid = $oldoptionid;
        $history->new_optionid = $optionid;
        $history->changed_by = $changedby;
        $history->timecreated = time();
        $DB->insert_record('local_courseaccess_history', $history);

        $transaction->allow_commit();

        local_courseaccess_runtime_cache::reset();

        return true;
    } catch (\Throwable $e) {
        $transaction->rollback($e);
        throw $e;
    }
}
