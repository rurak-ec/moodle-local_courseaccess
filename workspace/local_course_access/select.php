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

$PAGE->set_url('/local/course_access/select.php', ['courseid' => $course->id]);
$PAGE->set_context($context);
$PAGE->set_title(get_string('selectcondition', 'local_course_access'));
$PAGE->set_heading($course->fullname);
$PAGE->set_pagelayout('standard');

// Get conditions (should be only one now).
$conditions = local_course_access_get_conditions_for_course($course->id);
$condition = !empty($conditions) ? reset($conditions) : null;

if (!$condition) {
    // No conditions defined, nothing to select.
    redirect(new moodle_url('/course/view.php', ['id' => $course->id]));
}

// Check if user has already completed.
if (local_course_access_user_has_completed_all($USER->id, $course->id)) {
    redirect(new moodle_url('/course/view.php', ['id' => $course->id]));
}

// Handle Form Submission.
if (data_submitted() && confirm_sesskey()) {
    try {
        $optionid = required_param('optionid', PARAM_INT);

        // Validate option belongs to condition.
        $validoption = false;
        foreach ($condition->options as $opt) {
            if ($opt->id == $optionid) {
                $validoption = true;
                break;
            }
        }

        if ($validoption) {
            local_course_access_save_selection($USER->id, $course->id, $condition->id, $optionid);
            \core\notification::success(get_string('selectionssaved', 'local_course_access'));
            redirect(new moodle_url('/course/view.php', ['id' => $course->id]));
        } else {
            throw new moodle_exception('invalidoption', 'local_course_access');
        }
    } catch (Exception $e) {
        \core\notification::error($e->getMessage());
    }
}

// Display Form.
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('selectcondition', 'local_course_access'));

echo html_writer::start_tag('form', ['action' => $PAGE->url, 'method' => 'post']);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);

echo html_writer::div('', 'card mb-4');
echo html_writer::tag('h4', format_string($condition->name), ['class' => 'card-header']);
echo html_writer::start_div('card-body');

if (!empty($condition->description)) {
    echo html_writer::div(format_text($condition->description), 'mb-3');
}

echo html_writer::start_div('form-group');
echo html_writer::label(get_string('selectoption', 'local_course_access'), 'optionid');

$options = [];
foreach ($condition->options as $opt) {
    $options[$opt->id] = $opt->name;
}

echo html_writer::select($options, 'optionid', '', [
    'class' => 'form-control custom-select',
    'id' => 'optionid',
], ['' => get_string('choose')]);

echo html_writer::end_div();  // Form-group.
echo html_writer::end_div();  // Card-body.
echo html_writer::end_div();  // Card.

echo html_writer::div(
    html_writer::tag('button', get_string('save'), ['type' => 'submit', 'class' => 'btn btn-primary']),
    'mt-3'
);

echo html_writer::end_tag('form');

echo $OUTPUT->footer();
