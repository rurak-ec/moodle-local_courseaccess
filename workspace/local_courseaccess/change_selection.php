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

if (empty($condition->options)) {
    redirect(
        new moodle_url('/course/view.php', ['id' => $courseid]),
        get_string('nooptionsavailable', 'local_courseaccess'),
        null,
        \core\output\notification::NOTIFY_ERROR
    );
}

$PAGE->set_url('/local/courseaccess/change_selection.php', ['courseid' => $course->id]);
$PAGE->set_context($context);
$PAGE->set_title(get_string('changeselection', 'local_courseaccess'));
$PAGE->set_heading($course->fullname);
$PAGE->set_pagelayout('incourse');

// What the student has chosen: ticked (checkboxes) or selected (list) to start with.
$current = \local_courseaccess\selection::get_user_optionids((int)$USER->id, (int)$condition->id);

$form = new \local_courseaccess\form\selection_form(
    $PAGE->url->out(false),
    ['condition' => $condition, 'current' => $current, 'cancel' => true],
    'post',
    '',
    ['id' => 'change-selection-form']
);

// FORM PROCESSING (MUST BE BEFORE ANY OUTPUT).

if ($form->is_cancelled()) {
    redirect(new moodle_url('/course/view.php', ['id' => $courseid]));
}

if ($form->get_data()) {
    try {
        \local_courseaccess\selection::set((int)$USER->id, $courseid, $condition, $form->get_optionids(), (int)$USER->id);
    } catch (\Throwable $e) {
        debugging('Error changing selection in local_courseaccess: ' . $e->getMessage(), DEBUG_DEVELOPER);
        redirect(
            $PAGE->url,
            get_string('errorchanging', 'local_courseaccess'),
            null,
            \core\output\notification::NOTIFY_ERROR
        );
    }
    redirect(
        new moodle_url('/course/view.php', ['id' => $courseid]),
        get_string('selectionchanged', 'local_courseaccess'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

// PAGE OUTPUT.

// Confirm before applying a change (it re-evaluates the student's access).
$PAGE->requires->js_call_amd('local_courseaccess/change_confirm', 'init');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('changeselection', 'local_courseaccess'));

// Warning message.
echo html_writer::div(
    get_string('changeselectionwarning', 'local_courseaccess'),
    'alert alert-warning'
);

if (!empty($condition->description)) {
    echo html_writer::div(format_text($condition->description, FORMAT_PLAIN), 'mb-3');
}

// Display current selection.
if ($current) {
    $names = [];
    foreach ($current as $optionid) {
        $names[] = format_string($condition->options[$optionid]->name);
    }
    echo html_writer::start_div('alert alert-info');
    echo html_writer::tag('strong', get_string('currentselection', 'local_courseaccess') . ': ');
    echo html_writer::tag('span', implode(', ', $names));
    echo html_writer::end_div();
}

$form->display();

echo $OUTPUT->footer();
