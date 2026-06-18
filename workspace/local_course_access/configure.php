<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Course conditions configuration page
 *
 * @package    local_course_access
 * @copyright  2025 Rurak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/local/course_access/lib.php');

$courseid = required_param('courseid', PARAM_INT);
$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);

require_login($course);
$context = context_course::instance($course->id);
require_capability('local/course_access:configure', $context);

$PAGE->set_url('/local/course_access/configure.php', ['courseid' => $course->id]);
$PAGE->set_context($context);
$PAGE->set_title(get_string('configureconditions', 'local_course_access'));
$PAGE->set_heading($course->fullname);

// Handle Form Submission
if ($data = data_submitted() && confirm_sesskey()) {
    try {
        // Get condition data from $_POST directly
        if (!isset($_POST['condition']) || !is_array($_POST['condition'])) {
            throw new moodle_exception('error', 'local_course_access', '', 'No se recibieron datos de la condición');
        }

        $raw_condition = $_POST['condition'];

        // Clean and validate condition data
        $condition_id = isset($raw_condition['id']) && is_numeric($raw_condition['id']) ? intval($raw_condition['id']) : null;
        $condition_name = isset($raw_condition['name']) ? clean_param($raw_condition['name'], PARAM_TEXT) : '';
        $condition_desc = isset($raw_condition['description']) ? clean_param($raw_condition['description'], PARAM_TEXT) : '';
        $custom_id = isset($raw_condition['custom_id']) ? clean_param($raw_condition['custom_id'], PARAM_TEXT) : '';
        $enabled = isset($raw_condition['enabled']) ? 1 : 0;
        $allowchange = isset($raw_condition['allowchange']) ? 1 : 0;

        if (empty($condition_name)) {
            throw new moodle_exception('error', 'local_course_access', '', 'El nombre de la condición es requerido');
        }

        // Validate custom_id (must not be empty): default to the course shortname.
        if (empty($custom_id)) {
            $custom_id = local_course_access_default_customid($course);
        }

        // Structure for lib function (expects array of conditions)
        $condition_data = [
            'id' => $condition_id,
            'name' => $condition_name,
            'description' => $condition_desc,
            'custom_id' => $custom_id,
            'enabled' => $enabled,
            'allowchange' => $allowchange,
            'sortorder' => 0,
            'options' => []
        ];

        // Process options
        if (isset($raw_condition['options']) && is_array($raw_condition['options'])) {
            foreach ($raw_condition['options'] as $oid => $odata) {
                if (!is_array($odata)) {
                    continue;
                }

                $option_id = isset($odata['id']) && is_numeric($odata['id']) ? intval($odata['id']) : null;
                $option_name = isset($odata['name']) ? clean_param($odata['name'], PARAM_TEXT) : '';
                $option_value = isset($odata['value']) ? clean_param($odata['value'], PARAM_TEXT) : '';

                if (empty($option_name) || empty($option_value)) {
                    continue; // Skip empty options
                }

                $condition_data['options'][] = [
                    'id' => $option_id,
                    'name' => $option_name,
                    'value' => $option_value,
                    'sortorder' => intval($oid)
                ];
            }
        }

        // Validate: at least 2 options required
        if (count($condition_data['options']) < 2) {
            throw new moodle_exception('error', 'local_course_access', '', 'Se requieren al menos 2 opciones');
        }

        // Capture pre-save state to detect an activation transition (paused -> active).
        $wasactive = false;
        if ($condition_id) {
            $pre = $DB->get_record('local_course_access_conditions',
                ['id' => $condition_id, 'courseid' => $course->id]);
            $wasactive = $pre ? !empty($pre->enabled) : false;
        }

        // Pass as array of 1
        local_course_access_save_conditions($course->id, [$condition_data]);

        // Context-aware feedback.
        $saved = local_course_access_get_conditions_for_course($course->id);
        $saved = !empty($saved) ? reset($saved) : null;
        if ($saved && !empty($saved->enabled) && !$wasactive) {
            // Just activated: everyone will be asked to choose again.
            \core\notification::info(get_string('activation_reset_notice', 'local_course_access'));
        } else if ($saved && empty($saved->enabled)) {
            // Saved while paused: warn if restrictions already depend on the field.
            $rc = local_course_access_count_field_restrictions($course->id, $saved->id);
            if ($rc > 0) {
                \core\notification::warning(get_string('paused_with_restrictions', 'local_course_access', $rc));
            } else {
                \core\notification::info(get_string('saved_paused_notice', 'local_course_access'));
            }
        } else {
            \core\notification::success(get_string('changessaved'));
        }
        redirect($PAGE->url);

    } catch (Exception $e) {
        \core\notification::error($e->getMessage());
    }
}

// Get existing conditions (take the first one if exists)
$conditions = local_course_access_get_conditions_for_course($course->id);
$current_condition = !empty($conditions) ? reset($conditions) : null;

// Build the option rows for the template: existing options, or 2 empty rows for a new condition.
$options = [];
if ($current_condition && !empty($current_condition->options)) {
    $index = 0;
    foreach ($current_condition->options as $opt) {
        $options[] = [
            'index' => $index++,
            'id' => $opt->id,
            'name' => $opt->name,
            'value' => $opt->value,
        ];
    }
} else {
    // Two empty starter rows so the teacher is not faced with an empty section.
    $options[] = ['index' => 0, 'id' => '', 'name' => '', 'value' => ''];
    $options[] = ['index' => 1, 'id' => '', 'name' => '', 'value' => ''];
}

$templatecontext = [
    'formaction' => $PAGE->url->out(false),
    'courseurl' => (new moodle_url('/course/view.php', ['id' => $course->id]))->out(false),
    'sesskey' => sesskey(),
    'courseid' => $course->id,
    'conditionid' => $current_condition ? $current_condition->id : '',
    'conditionname' => $current_condition ? $current_condition->name : '',
    'conditiondescription' => $current_condition ? $current_condition->description : '',
    'customid' => $current_condition ? $current_condition->custom_id : local_course_access_default_customid($course),
    'customiddefault' => local_course_access_default_customid($course),
    // New conditions start PAUSED: the teacher configures first, then activates
    // (activating prompts every student to choose).
    'enabled' => $current_condition ? !empty($current_condition->enabled) : false,
    'options' => $options,
    'hascondition' => (bool)$current_condition,
    // Whether students may change their selection after first choosing (default off).
    'allowchange' => $current_condition ? !empty($current_condition->allowchange) : false,
    // custom_id becomes read-only once the field is in use (changing it would break restrictions).
    'customid_locked' => $current_condition
        ? local_course_access_customid_is_locked($course->id, $current_condition->id) : false,
    // Whether any student already has a selection (drives the re-activation confirm in JS).
    'hasselections' => $current_condition
        ? $DB->record_exists('local_course_access_selections', ['conditionid' => $current_condition->id]) : false,
];

// Data for the post-save "what's next" panel.
if ($current_condition) {
    $templatecontext['fielddisplayname'] = $course->shortname;
    $templatecontext['fieldshortname'] = local_course_access_get_field_shortname(
        $course->id,
        $current_condition->id,
        $current_condition->custom_id
    );
    $templatecontext['nextstep3text'] = get_string('nextsteps_step3', 'local_course_access', $course->shortname);
    $templatecontext['restrictioncount'] = local_course_access_count_field_restrictions(
        $course->id, $current_condition->id);
    $templatecontext['ispaused'] = empty($current_condition->enabled);
}

// Load the interactive behaviour (add/remove options, value autocomplete).
$PAGE->requires->js_call_amd('local_course_access/configure_form', 'init');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('configureconditions', 'local_course_access'));
echo $OUTPUT->render_from_template('local_course_access/configure_form', $templatecontext);
echo $OUTPUT->footer();
