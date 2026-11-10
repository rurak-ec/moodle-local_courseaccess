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

#[\PHPUnit\Framework\Attributes\CoversClass(\local_courseaccess\selection::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\local_courseaccess\restrictions::class)]
/**
 * What a user chose for a condition: one option (list) or several (checkboxes).
 *
 * @package    local_courseaccess
 * @copyright  2026 Rurak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_courseaccess\selection
 * @covers     \local_courseaccess\restrictions
 */
final class selection_test extends \advanced_testcase {
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
     * A course with an active condition of three options whose values overlap on purpose (ADM inside ADM-ONLINE).
     *
     * @param bool $multiple Checkboxes (true) or a list (false)
     * @param int $maxselect
     * @return array [course, condition with its options, option ids by value]
     */
    private function course_with_condition(bool $multiple = true, int $maxselect = 0): array {
        $course = $this->getDataGenerator()->create_course(['numsections' => 2]);
        local_courseaccess_save_conditions($course->id, [[
            'id' => null, 'name' => 'Major', 'description' => '', 'enabled' => 1, 'allowchange' => 1, 'sortorder' => 0,
            'multiple' => $multiple ? 1 : 0, 'maxselect' => $maxselect,
            'options' => [
                ['id' => null, 'name' => 'Business', 'value' => 'ADM', 'sortorder' => 0],
                ['id' => null, 'name' => 'Business (online)', 'value' => 'ADM-ONLINE', 'sortorder' => 1],
                ['id' => null, 'name' => 'Medicine', 'value' => 'MED', 'sortorder' => 2],
            ],
        ]]);
        return [$course, ...$this->condition_of((int)$course->id)];
    }

    /**
     * The condition of a course, as saved.
     *
     * @param int $courseid
     * @return array [condition with its options, option ids by value]
     */
    private function condition_of(int $courseid): array {
        global $DB;
        $conditionid = (int)$DB->get_field('local_courseaccess_cond', 'id', ['courseid' => $courseid], MUST_EXIST);
        $condition = selection::get_condition($conditionid);
        $ids = [];
        foreach ($condition->options as $option) {
            $ids[$option->value] = (int)$option->id;
        }
        return [$condition, $ids];
    }

    /**
     * Restrict a course section to the users whose profile field meets an operator and a value.
     *
     * @param \stdClass $course
     * @param int $section Section number
     * @param string $operator isequalto, contains…
     * @param string $value
     */
    private function restrict_section(\stdClass $course, int $section, string $operator, string $value): void {
        global $DB;
        $availability = json_encode([
            'op' => '&',
            'c' => [[
                'type' => 'profile',
                'cf' => local_courseaccess_get_field_shortname($course->id),
                'op' => $operator,
                'v' => $value,
            ]],
            'showc' => [false],
        ]);
        $DB->set_field('course_sections', 'availability', $availability, ['course' => $course->id, 'section' => $section]);
        rebuild_course_cache($course->id, true);
    }

    /**
     * Whether Moodle lets a user into a section.
     *
     * @param \stdClass $course
     * @param int $userid
     * @param int $section Section number
     * @return bool
     */
    private function section_available(\stdClass $course, int $userid, int $section): bool {
        \availability_profile\condition::wipe_static_cache();
        get_fast_modinfo(0, 0, true);
        return (bool)get_fast_modinfo($course->id, $userid)->get_section_info($section)->available;
    }

    /**
     * The rows of the change history of a user, oldest first: [old option, new option, changed by].
     *
     * @param int $userid
     * @return array
     */
    private function history(int $userid): array {
        global $DB;
        $out = [];
        foreach ($DB->get_records('local_courseaccess_history', ['userid' => $userid], 'id ASC') as $row) {
            $out[] = [
                $row->old_optionid === null ? null : (int)$row->old_optionid,
                $row->new_optionid === null ? null : (int)$row->new_optionid,
                (int)$row->changed_by,
            ];
        }
        return $out;
    }

    /**
     * Ticking several options saves one row per option and every value, between separators, in the profile field.
     */
    public function test_checkboxes_save_a_row_per_option_and_every_value_in_the_profile(): void {
        global $DB;
        [$course, $condition, $ids] = $this->course_with_condition();
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $this->assertNotNull(local_courseaccess_get_pending_condition($student->id, $course->id));

        // Ticked in another order: it is saved in the order of the options.
        $result = selection::set($student->id, $course->id, $condition, [$ids['MED'], $ids['ADM']]);

        $this->assertTrue($result['changed']);
        $this->assertSame([$ids['ADM'], $ids['MED']], $result['added']);
        $this->assertSame([$ids['ADM'], $ids['MED']], selection::get_user_optionids($student->id, $condition->id));
        $this->assertSame(2, $DB->count_records('local_courseaccess_sel', ['userid' => $student->id]));
        $this->assertSame('|ADM|MED|', selection::stored_value($student->id, $course->id, $condition->id));
        $this->assertSame([], $this->history($student->id), 'a first choice is not a change');
        $this->assertNull(local_courseaccess_get_pending_condition($student->id, $course->id), 'no more question');
        $first = local_courseaccess_get_user_selection($student->id, $course->id, $condition->id);
        $this->assertSame($ids['ADM'], (int)$first->option_id, 'the single-option reader returns the first one');
    }

    /**
     * Moodle's own "Restrict access" opens a section to who ticked its option, and a value does not match a longer one.
     */
    public function test_a_contains_restriction_opens_only_what_was_ticked(): void {
        [$course, $condition, $ids] = $this->course_with_condition();
        $both = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $online = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $nothing = $this->getDataGenerator()->create_and_enrol($course, 'student');
        selection::set($both->id, $course->id, $condition, [$ids['ADM'], $ids['MED']]);
        selection::set($online->id, $course->id, $condition, [$ids['ADM-ONLINE']]);

        $this->restrict_section($course, 1, 'contains', selection::restriction_value($condition, 'ADM'));
        $this->restrict_section($course, 2, 'contains', selection::restriction_value($condition, 'MED'));

        $this->assertSame('|ADM|', selection::restriction_value($condition, 'ADM'));
        $this->assertTrue($this->section_available($course, $both->id, 1));
        $this->assertTrue($this->section_available($course, $both->id, 2));
        $this->assertFalse($this->section_available($course, $online->id, 1), 'ADM-ONLINE is not ADM');
        $this->assertFalse($this->section_available($course, $online->id, 2));
        $this->assertFalse($this->section_available($course, $nothing->id, 1));
    }

    /**
     * A list keeps working as before: one value in the profile and "is equal to".
     */
    public function test_a_list_saves_one_value_for_is_equal_to(): void {
        [$course, $condition, $ids] = $this->course_with_condition(false);
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');

        selection::set($student->id, $course->id, $condition, [$ids['ADM-ONLINE']]);
        $this->assertSame('ADM-ONLINE', selection::stored_value($student->id, $course->id, $condition->id));
        $this->assertSame('ADM-ONLINE', selection::restriction_value($condition, 'ADM-ONLINE'));

        $this->restrict_section($course, 1, 'isequalto', 'ADM-ONLINE');
        $this->restrict_section($course, 2, 'isequalto', 'ADM');
        $this->assertTrue($this->section_available($course, $student->id, 1));
        $this->assertFalse($this->section_available($course, $student->id, 2));

        // Another option replaces it, in the same row, and it is a change.
        $result = selection::set($student->id, $course->id, $condition, [$ids['MED']], (int)get_admin()->id);
        $this->assertSame([$ids['MED']], selection::get_user_optionids($student->id, $condition->id));
        $this->assertTrue($result['changed']);
        $this->assertSame('MED', selection::stored_value($student->id, $course->id, $condition->id));
        $this->assertSame([[$ids['ADM-ONLINE'], $ids['MED'], (int)get_admin()->id]], $this->history($student->id));
    }

    /**
     * A choice must have an option, only options of the condition, one on a list and no more than the maximum.
     */
    public function test_an_invalid_choice_is_rejected(): void {
        global $DB;
        // Each case: checkboxes?, maximum, the values chosen (OTHER: an option of another course) and the error.
        $cases = [
            'nothing ticked' => [true, 0, [], 'mustselect'],
            'nothing selected in a list' => [false, 0, [], 'mustselect'],
            'more than the maximum' => [true, 2, ['ADM', 'ADM-ONLINE', 'MED'], 'error_maxselect'],
            'two options in a list' => [false, 0, ['ADM', 'MED'], 'error_onlyone'],
            'an option of another course' => [true, 0, ['ADM', 'OTHER'], 'invalidoption'],
        ];
        [, , $otherids] = $this->course_with_condition();

        foreach ($cases as $case => [$multiple, $maxselect, $values, $errorcode]) {
            [$course, $condition, $ids] = $this->course_with_condition($multiple, $maxselect);
            $ids['OTHER'] = $otherids['MED'];
            $student = $this->getDataGenerator()->create_and_enrol($course, 'student');
            $chosen = [];
            foreach ($values as $value) {
                $chosen[] = $ids[$value];
            }
            try {
                selection::set($student->id, $course->id, $condition, $chosen);
                $this->fail("The choice should have been rejected: $case.");
            } catch (\moodle_exception $e) {
                $this->assertSame($errorcode, $e->errorcode, $case);
            }
            $this->assertFalse($DB->record_exists('local_courseaccess_sel', ['userid' => $student->id]), $case);
            $this->assertNull(selection::stored_value($student->id, $course->id, $condition->id), $case);
        }
    }

    /**
     * Up to the maximum is fine, and no maximum means any number.
     */
    public function test_the_maximum_is_the_limit(): void {
        [$course, $condition, $ids] = $this->course_with_condition(true, 2);
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');
        selection::set($student->id, $course->id, $condition, [$ids['ADM'], $ids['MED']]);
        $this->assertCount(2, selection::get_user_optionids($student->id, $condition->id));

        [$free, $freecondition, $freeids] = $this->course_with_condition(true, 0);
        $other = $this->getDataGenerator()->create_and_enrol($free, 'student');
        selection::set($other->id, $free->id, $freecondition, array_values($freeids));
        $this->assertSame('|ADM|ADM-ONLINE|MED|', selection::stored_value($other->id, $free->id, $freecondition->id));
    }

    /**
     * Changing the ticked options logs each one added and each one removed; the same choice again changes nothing.
     */
    public function test_changing_the_ticked_options_is_logged_one_by_one(): void {
        [$course, $condition, $ids] = $this->course_with_condition();
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $teacher = $this->getDataGenerator()->create_and_enrol($course, 'editingteacher');
        selection::set($student->id, $course->id, $condition, [$ids['ADM'], $ids['ADM-ONLINE']]);

        $result = selection::set($student->id, $course->id, $condition, [$ids['ADM-ONLINE'], $ids['MED']], $teacher->id);

        $this->assertSame(['changed' => true, 'added' => [$ids['MED']], 'removed' => [$ids['ADM']]], $result);
        $this->assertSame('|ADM-ONLINE|MED|', selection::stored_value($student->id, $course->id, $condition->id));
        $this->assertSame(
            [[$ids['ADM'], null, (int)$teacher->id], [null, $ids['MED'], (int)$teacher->id]],
            $this->history($student->id)
        );

        $result = selection::set($student->id, $course->id, $condition, [$ids['MED'], $ids['ADM-ONLINE']], $teacher->id);
        $this->assertSame(['changed' => false, 'added' => [], 'removed' => []], $result);
        $this->assertCount(2, $this->history($student->id));
    }

    /**
     * Re-activating a condition empties every profile: saving the same choice again puts the value back.
     */
    public function test_the_same_choice_restores_an_emptied_profile(): void {
        [$course, $condition, $ids] = $this->course_with_condition();
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');
        selection::set($student->id, $course->id, $condition, [$ids['ADM'], $ids['MED']]);
        local_courseaccess_reset_profile_values($course->id, $condition->id);
        $this->assertNull(selection::stored_value($student->id, $course->id, $condition->id));

        $result = selection::set($student->id, $course->id, $condition, [$ids['ADM'], $ids['MED']]);

        $this->assertFalse($result['changed']);
        $this->assertSame('|ADM|MED|', selection::stored_value($student->id, $course->id, $condition->id));
        $this->assertSame([], $this->history($student->id));
    }

    /**
     * The selection type can change until a student chooses; after that it is fixed.
     */
    public function test_the_type_is_fixed_once_students_chose(): void {
        [$course, $condition, $ids] = $this->course_with_condition(false);
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $data = [
            'id' => $condition->id, 'name' => 'Major', 'description' => '', 'enabled' => 1, 'allowchange' => 1, 'sortorder' => 0,
            'options' => [],
        ];
        foreach ($condition->options as $option) {
            $data['options'][] = ['id' => $option->id, 'name' => $option->name, 'value' => $option->value];
        }

        // Nobody chose yet: a list can become checkboxes.
        $this->assertFalse(selection::in_use($course->id, $condition->id));
        local_courseaccess_save_conditions($course->id, [['multiple' => 1, 'maxselect' => 2] + $data]);
        [$condition] = $this->condition_of($course->id);
        $this->assertTrue(selection::is_multiple($condition));
        $this->assertSame(2, (int)$condition->maxselect);

        selection::set($student->id, $course->id, $condition, [$ids['ADM'], $ids['MED']]);
        $this->assertTrue(selection::in_use($course->id, $condition->id));

        // Saving without saying the type keeps it, and the maximum can still change.
        local_courseaccess_save_conditions($course->id, [['maxselect' => 3] + $data]);
        [$condition] = $this->condition_of($course->id);
        $this->assertTrue(selection::is_multiple($condition));
        $this->assertSame(3, (int)$condition->maxselect);

        try {
            local_courseaccess_save_conditions($course->id, [['multiple' => 0] + $data]);
            $this->fail('The type should be locked.');
        } catch (\moodle_exception $e) {
            $this->assertSame('error_typelocked', $e->errorcode);
        }
        [$condition] = $this->condition_of($course->id);
        $this->assertTrue(selection::is_multiple($condition), 'the failed save changed nothing');
    }

    /**
     * With checkboxes a value cannot contain the separator and a maximum of one makes no sense; a list has no maximum.
     */
    public function test_the_settings_of_checkboxes_are_checked(): void {
        global $DB;
        $data = ['id' => null, 'name' => 'Major', 'description' => '', 'enabled' => 0, 'allowchange' => 0, 'sortorder' => 0];
        $options = [['name' => 'One', 'value' => 'one'], ['name' => 'Two', 'value' => 'two']];
        $withbar = [['name' => 'One', 'value' => 'one|1'], ['name' => 'Two', 'value' => 'two']];

        // A list may have a value with the separator, and never has a maximum.
        $list = $this->getDataGenerator()->create_course();
        local_courseaccess_save_conditions($list->id, [$data + ['multiple' => 0, 'maxselect' => 5, 'options' => $withbar]]);
        $saved = $DB->get_record('local_courseaccess_cond', ['courseid' => $list->id], '*', MUST_EXIST);
        $this->assertSame([0, 0], [(int)$saved->multiple, (int)$saved->maxselect]);

        // The failed saves come last: a rolled back save leaves the test's own transaction unusable for writing.
        $course = $this->getDataGenerator()->create_course();
        try {
            local_courseaccess_save_conditions($course->id, [$data + ['multiple' => 1, 'maxselect' => 1, 'options' => $options]]);
            $this->fail('A maximum of one should be rejected.');
        } catch (\moodle_exception $e) {
            $this->assertSame('error_maxselect_min', $e->errorcode);
        }
        try {
            local_courseaccess_save_conditions($course->id, [$data + ['multiple' => 1, 'options' => $withbar]]);
            $this->fail('A value with the separator should be rejected.');
        } catch (\moodle_exception $e) {
            $this->assertSame('error_valueseparator', $e->errorcode);
        }
        $this->assertFalse($DB->record_exists('local_courseaccess_cond', ['courseid' => $course->id]));
    }

    /**
     * The restrictions that read the field are counted by operator, wherever they are in the tree.
     */
    public function test_restrictions_are_counted_by_operator(): void {
        global $DB;
        [$course] = $this->course_with_condition();
        $shortname = local_courseaccess_get_field_shortname($course->id);
        $this->assertSame([], restrictions::count_by_operator($course->id, $shortname));

        $this->restrict_section($course, 1, 'contains', '|ADM|');
        // A nested set, with a condition of another kind and one of another field.
        $nested = json_encode(['op' => '&', 'showc' => [true, true], 'c' => [
            ['type' => 'date', 'd' => '>=', 't' => 1],
            ['op' => '|', 'c' => [
                ['type' => 'profile', 'cf' => $shortname, 'op' => 'isequalto', 'v' => 'ADM'],
                ['type' => 'profile', 'cf' => $shortname, 'op' => 'isequalto', 'v' => 'MED'],
                ['type' => 'profile', 'cf' => 'other_field', 'op' => 'isequalto', 'v' => 'ADM'],
            ]],
        ]]);
        $DB->set_field('course_sections', 'availability', $nested, ['course' => $course->id, 'section' => 2]);

        $counts = restrictions::count_by_operator($course->id, $shortname);
        ksort($counts);
        $this->assertSame(['contains' => 1, 'isequalto' => 2], $counts);
    }
}
