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
 * Change selection page for students
 *
 * Allows students to change their previously selected condition option.
 * Unlike the first-time modal, this is accessed voluntarily and shows
 * their current selection.
 *
 * Flow:
 * 1. Validate user permissions
 * 2. Process form submission (if POST)
 * 3. Display form with current selection
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

// Prevent admins and teachers from accessing (they should use configure.php).
if (is_siteadmin() || has_capability('local/courseaccess:configure', $context)) {
    redirect(
        new moodle_url('/course/view.php', ['id' => $courseid]),
        get_string('error', 'local_courseaccess'),
        null,
        \core\output\notification::NOTIFY_ERROR
    );
}

// Get conditions for this course.
$conditions = local_courseaccess_get_conditions_for_course($courseid);
if (empty($conditions)) {
    redirect(
        new moodle_url('/course/view.php', ['id' => $courseid]),
        get_string('noconditionals', 'local_courseaccess'),
        null,
        \core\output\notification::NOTIFY_INFO
    );
}

$condition = reset($conditions);  // Get first (and only) condition.

// Changing the selection must be explicitly allowed by the teacher for this.
// Condition. Guard the page itself (defence against direct URL access).
if (empty($condition->allowchange)) {
    redirect(
        new moodle_url('/course/view.php', ['id' => $courseid]),
        get_string('changenotallowed', 'local_courseaccess'),
        null,
        \core\output\notification::NOTIFY_INFO
    );
}

// Get options for this condition.
$options = local_courseaccess_get_options_for_condition($condition->id);
if (empty($options)) {
    redirect(
        new moodle_url('/course/view.php', ['id' => $courseid]),
        get_string('nooptionsavailable', 'local_courseaccess'),
        null,
        \core\output\notification::NOTIFY_ERROR
    );
}

// FORM PROCESSING (MUST BE BEFORE ANY OUTPUT).

if ($_SERVER['REQUEST_METHOD'] === 'POST' && confirm_sesskey()) {
    $optionid = required_param('optionid', PARAM_INT);

    try {
        // Verify option exists and belongs to this condition.
        $option = $DB->get_record('local_courseaccess_options', [
            'id' => $optionid,
            'conditionid' => $condition->id,
        ], '*', MUST_EXIST);

        // Save the new selection.
        $transaction = $DB->start_delegated_transaction();

        try {
            // Update or create selection.
            $selection = $DB->get_record('local_courseaccess_sel', [
                'userid' => $USER->id,
                'conditionid' => $condition->id,
            ]);

            $oldoptionid = $selection ? $selection->optionid : null;

            if ($selection) {
                $selection->optionid = $optionid;
                $selection->timemodified = time();
                $DB->update_record('local_courseaccess_sel', $selection);
            } else {
                $selection = new stdClass();
                $selection->userid = $USER->id;
                $selection->courseid = $courseid;
                $selection->conditionid = $condition->id;
                $selection->optionid = $optionid;
                $selection->timecreated = time();
                $selection->timemodified = time();
                $DB->insert_record('local_courseaccess_sel', $selection);
            }

            // Update profile field.
            local_courseaccess_save_to_profile($USER->id, $courseid, $condition->id, $option->value);

            // Save to history.
            $history = new stdClass();
            $history->userid = $USER->id;
            $history->conditionid = $condition->id;
            $history->old_optionid = $oldoptionid;
            $history->new_optionid = $optionid;
            $history->changed_by = $USER->id;
            $history->timecreated = time();
            $DB->insert_record('local_courseaccess_history', $history);

            $transaction->allow_commit();

            redirect(
                new moodle_url('/course/view.php', ['id' => $courseid]),
                get_string('selectionchanged', 'local_courseaccess'),
                null,
                \core\output\notification::NOTIFY_SUCCESS
            );
        } catch (Exception $e) {
            $transaction->rollback($e);
            throw $e;
        }
    } catch (Exception $e) {
        redirect(
            new moodle_url('/local/courseaccess/change_selection.php', ['courseid' => $courseid]),
            get_string('errorchanging', 'local_courseaccess'),
            null,
            \core\output\notification::NOTIFY_ERROR
        );
    }
}

// PAGE SETUP AND OUTPUT.

$PAGE->set_url('/local/courseaccess/change_selection.php', ['courseid' => $course->id]);
$PAGE->set_context($context);
$PAGE->set_title(get_string('changeselection', 'local_courseaccess'));
$PAGE->set_heading($course->fullname);
$PAGE->set_pagelayout('incourse');

// Confirm before applying a change (it re-evaluates the student's access).
$PAGE->requires->js_call_amd('local_courseaccess/change_confirm', 'init');

// Get user's current selection.
$currentselection = local_courseaccess_get_user_selection($USER->id, $courseid, $condition->id);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('changeselection', 'local_courseaccess'));

// Warning message.
echo html_writer::div(
    get_string('changeselectionwarning', 'local_courseaccess'),
    'alert alert-warning'
);

// Display condition information.
echo html_writer::start_div('card mb-3');
echo html_writer::start_div('card-body');
echo html_writer::tag('h4', format_string($condition->name), ['class' => 'card-title']);
if (!empty($condition->description)) {
    echo html_writer::div(format_text($condition->description, FORMAT_HTML), 'card-text');
}
echo html_writer::end_div();
echo html_writer::end_div();

// Display current selection.
if ($currentselection) {
    echo html_writer::start_div('alert alert-info');
    echo html_writer::tag('strong', get_string('currentselection', 'local_courseaccess') . ': ');
    echo html_writer::tag('span', format_string($currentselection->option_name));
    echo html_writer::end_div();
}

// Selection form.
echo html_writer::start_tag('form', [
    'action' => '',
    'method' => 'post',
    'id' => 'change-selection-form',
    'class' => 'card',
]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);

echo html_writer::start_div('card-body');

echo html_writer::start_div('form-group');
echo html_writer::tag('label', get_string('selectoption', 'local_courseaccess', $condition->name), [
    'for' => 'option-select',
    'class' => 'font-weight-bold',
]);
echo html_writer::start_tag('select', [
    'id' => 'option-select',
    'name' => 'optionid',
    'class' => 'form-control custom-select',
    'required' => 'required',
]);

// Add placeholder.
echo html_writer::tag('option', get_string('selectoption_default', 'local_courseaccess'), [
    'value' => '',
    'disabled' => 'disabled',
    'selected' => $currentselection ? false : 'selected',
]);

// Add options.
foreach ($options as $option) {
    $selected = ($currentselection && $currentselection->option_id == $option->id) ? 'selected' : false;
    echo html_writer::tag('option', $option->name, [
        'value' => $option->id,
        'selected' => $selected,
    ]);
}

echo html_writer::end_tag('select');
echo html_writer::end_div();

// Buttons.
echo html_writer::start_div('form-group mt-3');
echo html_writer::tag('button', get_string('saveselection', 'local_courseaccess'), [
    'type' => 'submit',
    'class' => 'btn btn-primary mr-2',
]);
echo html_writer::link(
    new moodle_url('/course/view.php', ['id' => $courseid]),
    get_string('cancel'),
    ['class' => 'btn btn-secondary']
);
echo html_writer::end_div();

echo html_writer::end_div();  // Card-body.
echo html_writer::end_tag('form');

echo $OUTPUT->footer();
