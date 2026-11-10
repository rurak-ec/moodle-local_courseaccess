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
 * Conditional selection page for students
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

$PAGE->set_url('/local/courseaccess/select.php', ['courseid' => $course->id]);
$PAGE->set_context($context);
$PAGE->set_title(get_string('selectcondition', 'local_courseaccess'));
$PAGE->set_heading($course->fullname);
$PAGE->set_pagelayout('standard');

// Get conditions (should be only one now).
$conditions = local_courseaccess_get_conditions_for_course($course->id);
$condition = !empty($conditions) ? reset($conditions) : null;

if (!$condition) {
    // No conditions defined, nothing to select.
    redirect(new moodle_url('/course/view.php', ['id' => $course->id]));
}

// Check if user has already completed.
if (local_courseaccess_user_has_completed_all($USER->id, $course->id)) {
    redirect(new moodle_url('/course/view.php', ['id' => $course->id]));
}

$form = new \local_courseaccess\form\selection_form($PAGE->url->out(false), ['condition' => $condition]);

if ($form->get_data()) {
    \local_courseaccess\selection::set((int)$USER->id, (int)$course->id, $condition, $form->get_optionids());
    redirect(
        new moodle_url('/course/view.php', ['id' => $course->id]),
        get_string('selectionssaved', 'local_courseaccess'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('selectcondition', 'local_courseaccess'));
if (!empty($condition->description)) {
    echo html_writer::div(format_text($condition->description, FORMAT_PLAIN), 'mb-3');
}
$form->display();
echo $OUTPUT->footer();
