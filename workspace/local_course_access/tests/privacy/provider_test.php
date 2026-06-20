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

namespace local_course_access\privacy;

use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\writer;

/**
 * Privacy provider tests for local_course_access.
 *
 * @package    local_course_access
 * @copyright  2025 Rurak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_course_access\privacy\provider
 */
final class provider_test extends \core_privacy\tests\provider_testcase {
    /**
     * Create a course, an active condition and a student selection.
     *
     * @return array{0:\stdClass,1:\stdClass,2:\context_course,3:int} course, student, context, conditionid
     */
    private function setup_selection(): array {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/local/course_access/lib.php');
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        local_course_access_save_conditions($course->id, [[
            'id' => null, 'name' => 'Shift', 'description' => '', 'custom_id' => '',
            'enabled' => 1, 'allowchange' => 1, 'sortorder' => 0,
            'options' => [
                ['id' => null, 'name' => 'Morning', 'value' => 'morning', 'sortorder' => 0],
                ['id' => null, 'name' => 'Afternoon', 'value' => 'afternoon', 'sortorder' => 1],
            ],
        ]]);
        $conditionid = (int) $DB->get_field('local_course_access_cond', 'id', ['courseid' => $course->id]);
        $optionid = (int) $DB->get_field(
            'local_course_access_options',
            'id',
            ['conditionid' => $conditionid, 'value' => 'morning']
        );

        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');
        local_course_access_save_selection($student->id, $course->id, $conditionid, $optionid);

        // Add a history row so the history paths are exercised too.
        $DB->insert_record('local_course_access_history', (object) [
            'userid' => $student->id, 'courseid' => $course->id, 'conditionid' => $conditionid,
            'old_optionid' => null, 'new_optionid' => $optionid, 'changed_by' => $student->id,
            'timecreated' => time(),
        ]);

        return [$course, $student, \context_course::instance($course->id), $conditionid];
    }

    /**
     * The provider describes both data tables.
     */
    public function test_get_metadata(): void {
        $collection = new \core_privacy\local\metadata\collection('local_course_access');
        $collection = provider::get_metadata($collection);
        $tables = [];
        foreach ($collection->get_collection() as $item) {
            $tables[] = $item->get_name();
        }
        $this->assertContains('local_course_access_sel', $tables);
        $this->assertContains('local_course_access_history', $tables);
    }

    /**
     * The user's course context is discoverable.
     */
    public function test_get_contexts_for_userid(): void {
        [, $student, $context] = $this->setup_selection();
        $contextlist = provider::get_contexts_for_userid($student->id);
        $this->assertEqualsCanonicalizing([$context->id], $contextlist->get_contextids());
    }

    /**
     * The user is discoverable within the course context.
     */
    public function test_get_users_in_context(): void {
        [, $student, $context] = $this->setup_selection();
        $userlist = new \core_privacy\local\request\userlist($context, 'local_course_access');
        provider::get_users_in_context($userlist);
        $this->assertContains((int) $student->id, $userlist->get_userids());
    }

    /**
     * Export writes the user's selection data for the context.
     */
    public function test_export_user_data(): void {
        [, $student, $context] = $this->setup_selection();
        $this->export_context_data_for_user($student->id, $context, 'local_course_access');
        $this->assertTrue(writer::with_context($context)->has_any_data());
    }

    /**
     * Deleting for a user removes their rows in the context.
     */
    public function test_delete_data_for_user(): void {
        global $DB;
        [$course, $student, $context, $conditionid] = $this->setup_selection();

        $contextlist = new approved_contextlist($student, 'local_course_access', [$context->id]);
        provider::delete_data_for_user($contextlist);

        $this->assertFalse($DB->record_exists('local_course_access_sel', ['userid' => $student->id]));
        $this->assertFalse($DB->record_exists('local_course_access_history', ['userid' => $student->id]));
    }

    /**
     * Deleting all users in a context removes every row.
     */
    public function test_delete_data_for_all_users_in_context(): void {
        global $DB;
        [$course, $student, $context] = $this->setup_selection();

        provider::delete_data_for_all_users_in_context($context);

        $this->assertFalse($DB->record_exists('local_course_access_sel', ['courseid' => $course->id]));
        $this->assertFalse($DB->record_exists('local_course_access_history', ['courseid' => $course->id]));
    }

    /**
     * Deleting a specified user list removes those users' rows.
     */
    public function test_delete_data_for_users(): void {
        global $DB;
        [$course, $student, $context] = $this->setup_selection();

        $userlist = new approved_userlist($context, 'local_course_access', [$student->id]);
        provider::delete_data_for_users($userlist);

        $this->assertFalse($DB->record_exists('local_course_access_sel', ['userid' => $student->id]));
    }
}
