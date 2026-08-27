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
 * Course conditions configuration page
 *
 * @package    local_courseaccess
 * @copyright  2025 Rurak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/local/courseaccess/lib.php');

$courseid = required_param('courseid', PARAM_INT);
$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);

require_login($course);
$context = context_course::instance($course->id);
require_capability('local/courseaccess:configure', $context);

$PAGE->set_url('/local/courseaccess/configure.php', ['courseid' => $course->id]);
$PAGE->set_context($context);
$PAGE->set_title(get_string('configureconditions', 'local_courseaccess'));
$PAGE->set_heading($course->fullname);

// Handle Form Submission.
if (data_submitted() && confirm_sesskey()) {
    try {
        // The submitted condition arrives as a nested array (sesskey-protected);.
        // Every field below is cleaned with clean_param before use.
        if (!isset($_POST['condition']) || !is_array($_POST['condition'])) {
            throw new moodle_exception('error_no_condition_data', 'local_courseaccess');
        }

        $rawcondition = $_POST['condition'];

        // Clean and validate condition data.
        $conditionid = isset($rawcondition['id']) && is_numeric($rawcondition['id']) ? intval($rawcondition['id']) : null;
        $conditionname = isset($rawcondition['name']) ? clean_param($rawcondition['name'], PARAM_TEXT) : '';
        $conditiondesc = isset($rawcondition['description']) ? clean_param($rawcondition['description'], PARAM_TEXT) : '';
        $enabled = isset($rawcondition['enabled']) ? 1 : 0;
        $allowchange = isset($rawcondition['allowchange']) ? 1 : 0;

        if (empty($conditionname)) {
            throw new moodle_exception('error_condition_name_required', 'local_courseaccess');
        }

        // Structure for lib function (expects array of conditions).
        $conditiondata = [
            'id' => $conditionid,
            'name' => $conditionname,
            'description' => $conditiondesc,
            'custom_id' => (string)$course->id,
            'enabled' => $enabled,
            'allowchange' => $allowchange,
            'sortorder' => 0,
            'options' => [],
        ];

        // Process options.
        if (isset($rawcondition['options']) && is_array($rawcondition['options'])) {
            foreach ($rawcondition['options'] as $oid => $odata) {
                if (!is_array($odata)) {
                    continue;
                }

                $optionid = isset($odata['id']) && is_numeric($odata['id']) ? intval($odata['id']) : null;
                $optionname = isset($odata['name']) ? clean_param($odata['name'], PARAM_TEXT) : '';
                $optionvalue = isset($odata['value']) ? clean_param($odata['value'], PARAM_TEXT) : '';

                if (empty($optionname) || empty($optionvalue)) {
                    continue;  // Skip empty options.
                }

                $conditiondata['options'][] = [
                    'id' => $optionid,
                    'name' => $optionname,
                    'value' => $optionvalue,
                    'sortorder' => intval($oid),
                ];
            }
        }

        // Validate: at least 2 options required.
        if (count($conditiondata['options']) < 2) {
            throw new moodle_exception('error_min2options', 'local_courseaccess');
        }

        // Capture pre-save state to detect an activation transition (paused -> active).
        $wasactive = false;
        if ($conditionid) {
            $pre = $DB->get_record(
                'local_courseaccess_cond',
                ['id' => $conditionid, 'courseid' => $course->id]
            );
            $wasactive = $pre ? !empty($pre->enabled) : false;
        }

        // Pass as array of 1.
        local_courseaccess_save_conditions($course->id, [$conditiondata]);

        // Context-aware feedback.
        $saved = local_courseaccess_get_conditions_for_course($course->id);
        $saved = !empty($saved) ? reset($saved) : null;
        if ($saved && !empty($saved->enabled) && !$wasactive) {
            // Just activated: everyone will be asked to choose again.
            \core\notification::info(get_string('activation_reset_notice', 'local_courseaccess'));
        } else if ($saved && empty($saved->enabled)) {
            // Saved while paused: warn if restrictions already depend on the field.
            $rc = local_courseaccess_count_field_restrictions($course->id, $saved->id);
            if ($rc > 0) {
                \core\notification::warning(get_string('paused_with_restrictions', 'local_courseaccess', $rc));
            } else {
                \core\notification::info(get_string('saved_paused_notice', 'local_courseaccess'));
            }
        } else {
            \core\notification::success(get_string('changessaved'));
        }
        redirect($PAGE->url);
    } catch (Exception $e) {
        \core\notification::error($e->getMessage());
    }
}

// Get existing conditions (take the first one if exists).
$conditions = local_courseaccess_get_conditions_for_course($course->id);
$currentcondition = !empty($conditions) ? reset($conditions) : null;

// Build the option rows for the template: existing options, or 2 empty rows for a new condition.
$options = [];
if ($currentcondition && !empty($currentcondition->options)) {
    $index = 0;
    foreach ($currentcondition->options as $opt) {
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
    'conditionid' => $currentcondition ? $currentcondition->id : '',
    'conditionname' => $currentcondition ? $currentcondition->name : '',
    'conditiondescription' => $currentcondition ? $currentcondition->description : '',
    'fieldshortname' => local_courseaccess_get_field_shortname($course->id),
    'fielddisplayname' => local_courseaccess_get_field_shortname($course->id),
    // New conditions start PAUSED: the teacher configures first, then activates.
    'enabled' => $currentcondition ? !empty($currentcondition->enabled) : false,
    'options' => $options,
    'hascondition' => (bool)$currentcondition,
    // Whether students may change their selection after first choosing (default off).
    'allowchange' => $currentcondition ? !empty($currentcondition->allowchange) : false,
    // Whether any student already has a selection (drives the re-activation confirm in JS).
    'hasselections' => $currentcondition
        ? $DB->record_exists('local_courseaccess_sel', ['conditionid' => $currentcondition->id]) : false,
];

// Data for the post-save "what's next" panel.
if ($currentcondition) {
    $templatecontext['nextstep3text'] = get_string('nextsteps_step3', 'local_courseaccess', local_courseaccess_get_field_shortname($course->id));
    $templatecontext['restrictioncount'] = local_courseaccess_count_field_restrictions(
        $course->id,
        $currentcondition->id
    );
    $templatecontext['ispaused'] = empty($currentcondition->enabled);
}

// Load the interactive behaviour (add/remove options, value autocomplete).
$PAGE->requires->js_call_amd('local_courseaccess/configure_form', 'init');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('configureconditions', 'local_courseaccess'));
echo $OUTPUT->render_from_template('local_courseaccess/configure_form', $templatecontext);
echo $OUTPUT->footer();
