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

namespace local_courseaccess;

/**
 * Unit tests for the local_courseaccess library functions.
 *
 * @package    local_courseaccess
 * @copyright  2025 Rurak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_courseaccess_save_conditions
 */
final class lib_test extends \advanced_testcase {
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
     * Create an active condition with two options on a course.
     *
     * @param int $courseid
     * @param bool $enabled
     * @return int The condition id.
     */
    private function create_condition(int $courseid, bool $enabled = true): int {
        global $DB;
        local_courseaccess_save_conditions($courseid, [[
            'id' => null,
            'name' => 'Shift',
            'description' => '',
            'custom_id' => '',
            'enabled' => $enabled ? 1 : 0,
            'allowchange' => 0,
            'sortorder' => 0,
            'options' => [
                ['id' => null, 'name' => 'Morning', 'value' => 'morning', 'sortorder' => 0],
                ['id' => null, 'name' => 'Afternoon', 'value' => 'afternoon', 'sortorder' => 1],
            ],
        ]]);
        return (int) $DB->get_field('local_courseaccess_cond', 'id', ['courseid' => $courseid]);
    }

    /**
     * Saving a condition creates a locked custom profile field with the expected shortname.
     */
    public function test_save_conditions_creates_locked_profile_field(): void {
        global $DB;
        $course = $this->getDataGenerator()->create_course(['shortname' => 'CRS1']);
        $conditionid = $this->create_condition($course->id);

        $shortname = local_courseaccess_get_field_shortname($course->id, $conditionid);
        $this->assertSame("acc_CRS1_{$conditionid}", $shortname);

        $field = $DB->get_record('user_info_field', ['shortname' => $shortname]);
        $this->assertNotEmpty($field);
        $this->assertEquals(1, $field->locked);
        $this->assertCount(2, $DB->get_records('local_courseaccess_options', ['conditionid' => $conditionid]));
    }

    /**
     * An enabled condition cannot be saved without a name and two valid options.
     */
    public function test_activation_requirements_enforced(): void {
        $course = $this->getDataGenerator()->create_course();
        $this->expectException(\moodle_exception::class);
        local_courseaccess_save_conditions($course->id, [[
            'id' => null, 'name' => 'Shift', 'description' => '', 'custom_id' => '',
            'enabled' => 1, 'allowchange' => 0, 'sortorder' => 0,
            'options' => [['id' => null, 'name' => 'Only', 'value' => 'only', 'sortorder' => 0]],
        ]]);
    }

    /**
     * The pending condition is returned for a non-selected student and skipped otherwise.
     */
    public function test_get_pending_condition_skip_rules(): void {
        $course = $this->getDataGenerator()->create_course();
        $conditionid = $this->create_condition($course->id);

        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $teacher = $this->getDataGenerator()->create_and_enrol($course, 'editingteacher');

        // Student has not selected: the condition is pending.
        $pending = local_courseaccess_get_pending_condition($student->id, $course->id);
        $this->assertNotNull($pending);
        $this->assertEquals($conditionid, $pending->id);

        // Teachers (can configure) and admins never see the modal.
        $this->assertNull(local_courseaccess_get_pending_condition($teacher->id, $course->id));
        $this->assertNull(local_courseaccess_get_pending_condition(get_admin()->id, $course->id));

        // After selecting, the student is no longer pending.
        local_courseaccess_save_selection(
            $student->id,
            $course->id,
            $conditionid,
            $this->first_option_id($conditionid)
        );
        $this->assertNull(local_courseaccess_get_pending_condition($student->id, $course->id));
    }

    /**
     * A paused condition shows no modal.
     */
    public function test_paused_condition_not_pending(): void {
        $course = $this->getDataGenerator()->create_course();
        $this->create_condition($course->id, false);
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $this->assertNull(local_courseaccess_get_pending_condition($student->id, $course->id));
    }

    /**
     * Saving a selection writes the selections table and the profile field, and locks the custom_id.
     */
    public function test_save_selection_writes_table_and_profile(): void {
        global $DB;
        $course = $this->getDataGenerator()->create_course(['shortname' => 'CRS2']);
        $conditionid = $this->create_condition($course->id);
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $optionid = $this->first_option_id($conditionid);

        $this->assertFalse(local_courseaccess_user_has_completed_all($student->id, $course->id));

        local_courseaccess_save_selection($student->id, $course->id, $conditionid, $optionid);

        $this->assertTrue($DB->record_exists(
            'local_courseaccess_sel',
            ['userid' => $student->id, 'conditionid' => $conditionid, 'optionid' => $optionid]
        ));
        $this->assertTrue(local_courseaccess_user_has_completed_all($student->id, $course->id));

        $shortname = local_courseaccess_get_field_shortname($course->id, $conditionid);
        $field = $DB->get_record('user_info_field', ['shortname' => $shortname]);
        $value = $DB->get_field('user_info_data', 'data', ['userid' => $student->id, 'fieldid' => $field->id]);
        $this->assertSame('morning', $value);

        // A stored selection locks the custom_id.
        $this->assertTrue(local_courseaccess_customid_is_locked($course->id, $conditionid));
    }

    /**
     * Return the id of the first (Morning) option for a condition.
     *
     * @param int $conditionid
     * @return int
     */
    private function first_option_id(int $conditionid): int {
        global $DB;
        return (int) $DB->get_field(
            'local_courseaccess_options',
            'id',
            ['conditionid' => $conditionid, 'value' => 'morning']
        );
    }
}
