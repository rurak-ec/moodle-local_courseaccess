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

namespace local_courseaccess\external;

use core_external\external_api;
use local_courseaccess\selection;

#[\PHPUnit\Framework\Attributes\CoversClass(\local_courseaccess\external\save_selection::class)]
/**
 * The web service the blocking overlay saves through.
 *
 * @package    local_courseaccess
 * @copyright  2026 Rurak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_courseaccess\external\save_selection
 */
final class save_selection_test extends \advanced_testcase {
    /**
     * Load the plugin library before each test.
     */
    protected function setUp(): void {
        global $CFG;
        parent::setUp();
        require_once($CFG->dirroot . '/local/courseaccess/lib.php');
        $this->resetAfterTest();
    }

    /**
     * A course with an active condition of three options.
     *
     * @param bool $multiple Checkboxes (true) or a list (false)
     * @param int $maxselect
     * @return array [course, condition with its options, option ids by value]
     */
    private function course_with_condition(bool $multiple, int $maxselect = 0): array {
        global $DB;
        $course = $this->getDataGenerator()->create_course();
        $condition = $this->getDataGenerator()->get_plugin_generator('local_courseaccess')->create_condition([
            'courseid' => $course->id,
            'options' => 'Morning:morning,Afternoon:afternoon,Evening:evening',
            'multiple' => $multiple,
            'maxselect' => $maxselect,
        ]);
        $condition = selection::get_condition((int)$condition->id);
        $ids = [];
        foreach ($condition->options as $option) {
            $ids[$option->value] = (int)$option->id;
        }
        return [$course, $condition, $ids];
    }

    /**
     * Call the function and clean its result like the server does.
     *
     * @param int $courseid
     * @param int $conditionid
     * @param int[] $optionids
     * @return array
     */
    private function save(int $courseid, int $conditionid, array $optionids): array {
        $result = save_selection::execute($courseid, $conditionid, $optionids);
        return external_api::clean_returnvalue(save_selection::execute_returns(), $result);
    }

    /**
     * A student ticks two options in the overlay: they are saved and the overlay does not come back.
     */
    public function test_a_student_saves_several_options(): void {
        [$course, $condition, $ids] = $this->course_with_condition(true, 2);
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $this->setUser($student);

        $result = $this->save($course->id, $condition->id, [$ids['evening'], $ids['morning']]);

        $this->assertSame(['saved' => true], $result);
        $this->assertSame([$ids['morning'], $ids['evening']], selection::get_user_optionids($student->id, $condition->id));
        $this->assertSame('|morning|evening|', selection::stored_value($student->id, $course->id, $condition->id));
        \local_courseaccess_runtime_cache::reset();
        $this->assertNull(local_courseaccess_get_pending_condition($student->id, $course->id));
    }

    /**
     * A list saves its one option, as before.
     */
    public function test_a_student_saves_the_option_of_a_list(): void {
        [$course, $condition, $ids] = $this->course_with_condition(false);
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $this->setUser($student);

        $this->assertSame(['saved' => true], $this->save($course->id, $condition->id, [$ids['afternoon']]));
        $this->assertSame('afternoon', selection::stored_value($student->id, $course->id, $condition->id));
    }

    /**
     * More options than the maximum are rejected and nothing is saved.
     */
    public function test_more_than_the_maximum_is_rejected(): void {
        [$course, $condition, $ids] = $this->course_with_condition(true, 2);
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $this->setUser($student);

        try {
            $this->save($course->id, $condition->id, array_values($ids));
            $this->fail('Three options should be rejected.');
        } catch (\moodle_exception $e) {
            $this->assertSame('error_maxselect', $e->errorcode);
        }
        $this->assertSame([], selection::get_user_optionids($student->id, $condition->id));
    }

    /**
     * Once chosen, the overlay's function does not change the choice: that goes through the change page.
     */
    public function test_it_only_saves_the_first_choice(): void {
        [$course, $condition, $ids] = $this->course_with_condition(true);
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $this->setUser($student);
        $this->save($course->id, $condition->id, [$ids['morning']]);
        \local_courseaccess_runtime_cache::reset();

        try {
            $this->save($course->id, $condition->id, [$ids['evening']]);
            $this->fail('A second save should be rejected.');
        } catch (\moodle_exception $e) {
            $this->assertSame('changenotallowed', $e->errorcode);
        }
        $this->assertSame([$ids['morning']], selection::get_user_optionids($student->id, $condition->id));
    }

    /**
     * Teachers are never asked, so they have nothing to save.
     */
    public function test_a_teacher_has_nothing_pending(): void {
        [$course, $condition, $ids] = $this->course_with_condition(true);
        $teacher = $this->getDataGenerator()->create_and_enrol($course, 'editingteacher');
        $this->setUser($teacher);

        $this->expectException(\moodle_exception::class);
        $this->save($course->id, $condition->id, [$ids['morning']]);
    }

    /**
     * Someone who is not in the course cannot save.
     */
    public function test_it_needs_access_to_the_course(): void {
        [$course, $condition, $ids] = $this->course_with_condition(true);
        $this->setUser($this->getDataGenerator()->create_user());

        $this->expectException(\moodle_exception::class);
        $this->save($course->id, $condition->id, [$ids['morning']]);
    }
}
