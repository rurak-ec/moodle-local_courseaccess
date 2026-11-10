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

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');

/**
 * What a user chose for a course condition: one option (list) or several (checkboxes).
 *
 * A condition is either a list, where the student picks exactly one option, or checkboxes, where the student ticks one or
 * more options up to the condition's maximum. Every way of choosing (the blocking overlay, the selection pages, other
 * plugins) saves through {@see self::set()}, so the rules live in one place.
 *
 * The profile field that Moodle's "Restrict access" reads holds:
 * - list: the option value, for example "ENF" (restrict with "is equal to" ENF);
 * - checkboxes: every chosen value between separators, for example "|ENF|MED|" (restrict with "contains" |ENF|). The
 *   separators keep a value from matching inside a longer one ("ADM" inside "ADM-ONLINE").
 *
 * @package    local_courseaccess
 * @copyright  2026 Rurak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class selection {
    /** @var string Goes before and after each value in the profile field of a checkbox condition. */
    public const SEPARATOR = '|';

    /**
     * Whether the condition lets students choose several options (checkboxes).
     *
     * @param \stdClass $condition
     * @return bool
     */
    public static function is_multiple(\stdClass $condition): bool {
        return !empty($condition->multiple);
    }

    /**
     * A condition with its options (in display order, keyed by id).
     *
     * @param int $conditionid
     * @param int $strictness MUST_EXIST or IGNORE_MISSING
     * @return \stdClass|null
     */
    public static function get_condition(int $conditionid, int $strictness = MUST_EXIST): ?\stdClass {
        global $DB;
        $condition = $DB->get_record('local_courseaccess_cond', ['id' => $conditionid], '*', $strictness);
        if (!$condition) {
            return null;
        }
        $condition->options = $DB->get_records(
            'local_courseaccess_options',
            ['conditionid' => $conditionid],
            'sortorder ASC, id ASC'
        );
        return $condition;
    }

    /**
     * The text a teacher types in "Restrict access" to show content to the students who chose an option.
     *
     * @param \stdClass $condition
     * @param string $value The option value
     * @return string The value itself (list, with "is equal to") or the value between separators (checkboxes, "contains")
     */
    public static function restriction_value(\stdClass $condition, string $value): string {
        if (!self::is_multiple($condition)) {
            return $value;
        }
        return self::SEPARATOR . $value . self::SEPARATOR;
    }

    /**
     * What the profile field holds for a set of chosen options.
     *
     * @param \stdClass $condition
     * @param \stdClass[] $options The chosen option records, in display order
     * @return string
     */
    public static function profile_value(\stdClass $condition, array $options): string {
        $values = [];
        foreach ($options as $option) {
            $values[] = (string)$option->value;
        }
        $values = array_values(array_unique($values));
        if (!$values) {
            return '';
        }
        if (!self::is_multiple($condition)) {
            return $values[0];
        }
        return self::SEPARATOR . implode(self::SEPARATOR, $values) . self::SEPARATOR;
    }

    /**
     * The options a user has chosen for a condition, in display order.
     *
     * @param int $userid
     * @param int $conditionid
     * @return int[] Option ids
     */
    public static function get_user_optionids(int $userid, int $conditionid): array {
        global $DB;
        $ids = $DB->get_fieldset_sql(
            "SELECT s.optionid
               FROM {local_courseaccess_sel} s
               JOIN {local_courseaccess_options} o ON o.id = s.optionid
              WHERE s.userid = :userid AND s.conditionid = :conditionid
           ORDER BY o.sortorder ASC, o.id ASC",
            ['userid' => $userid, 'conditionid' => $conditionid]
        );
        return array_map('intval', $ids);
    }

    /**
     * Whether any student has already chosen: a selection row or a stored profile value exists.
     *
     * Once that happens the selection type is fixed, because the stored values and the restrictions that read them have
     * the shape of that type.
     *
     * @param int $courseid
     * @param int $conditionid
     * @return bool
     */
    public static function in_use(int $courseid, int $conditionid): bool {
        global $DB;
        if ($DB->record_exists('local_courseaccess_sel', ['conditionid' => $conditionid])) {
            return true;
        }
        $shortname = local_courseaccess_get_field_shortname($courseid, $conditionid);
        $sql = "SELECT d.id
                  FROM {user_info_data} d
                  JOIN {user_info_field} f ON f.id = d.fieldid
                 WHERE f.shortname = :shortname
                   AND " . $DB->sql_isnotempty('user_info_data', 'd.data', false, true);
        return $DB->record_exists_sql($sql, ['shortname' => $shortname]);
    }

    /**
     * Check a choice against the condition and return it in display order.
     *
     * @param \stdClass $condition The condition with its options
     * @param int[] $optionids The chosen options
     * @return int[] The chosen option ids, unique and in display order
     * @throws \moodle_exception invalidoption, mustselect, error_onlyone or error_maxselect
     */
    public static function validate(\stdClass $condition, array $optionids): array {
        $wanted = array_values(array_unique(array_map('intval', $optionids)));
        $valid = [];
        foreach ($condition->options ?? [] as $option) {
            if (in_array((int)$option->id, $wanted, true)) {
                $valid[] = (int)$option->id;
            }
        }
        if (count($valid) !== count($wanted)) {
            throw new \moodle_exception('invalidoption', 'local_courseaccess');
        }
        if (!$valid) {
            throw new \moodle_exception('mustselect', 'local_courseaccess');
        }
        if (!self::is_multiple($condition)) {
            if (count($valid) > 1) {
                throw new \moodle_exception('error_onlyone', 'local_courseaccess');
            }
            return $valid;
        }
        $max = (int)($condition->maxselect ?? 0);
        if ($max > 0 && count($valid) > $max) {
            throw new \moodle_exception('error_maxselect', 'local_courseaccess', '', $max);
        }
        return $valid;
    }

    /**
     * Save what a user chooses for a condition: it replaces whatever the user had chosen before.
     *
     * Writes the selection rows, the change history (only when the user had already chosen and the choice differs) and the
     * profile field. Saving the same choice again changes nothing, but still restores a profile field that was emptied.
     *
     * @param int $userid
     * @param int $courseid
     * @param \stdClass $condition The condition with its options
     * @param int[] $optionids The chosen options
     * @param int|null $changedby Who saves it (defaults to the user)
     * @return array changed (bool), added (int[]) and removed (int[])
     * @throws \moodle_exception When the choice is not valid for the condition
     */
    public static function set(int $userid, int $courseid, \stdClass $condition, array $optionids, ?int $changedby = null): array {
        global $DB;

        $optionids = self::validate($condition, $optionids);
        $changedby = $changedby ?? $userid;
        $conditionid = (int)$condition->id;
        $now = time();

        $transaction = $DB->start_delegated_transaction();
        try {
            $rows = $DB->get_records(
                'local_courseaccess_sel',
                ['userid' => $userid, 'conditionid' => $conditionid],
                'id ASC',
                'id, optionid'
            );
            $current = [];
            foreach ($rows as $row) {
                $current[(int)$row->optionid] = (int)$row->id;
            }
            $added = array_values(array_diff($optionids, array_keys($current)));
            $removed = array_values(array_diff(array_keys($current), $optionids));

            if (!self::is_multiple($condition) && $current && $added) {
                // A list keeps its single row: the option changes in place.
                $rowid = reset($current);
                $DB->delete_records_select(
                    'local_courseaccess_sel',
                    'userid = :userid AND conditionid = :conditionid AND id <> :id',
                    ['userid' => $userid, 'conditionid' => $conditionid, 'id' => $rowid]
                );
                $DB->update_record('local_courseaccess_sel', (object)[
                    'id' => $rowid,
                    'optionid' => $added[0],
                    'timemodified' => $now,
                ]);
                self::log($userid, $courseid, $conditionid, key($current), $added[0], $changedby, $now);
            } else {
                foreach ($removed as $optionid) {
                    $DB->delete_records('local_courseaccess_sel', ['id' => $current[$optionid]]);
                    self::log($userid, $courseid, $conditionid, $optionid, null, $changedby, $now);
                }
                foreach ($added as $optionid) {
                    $DB->insert_record('local_courseaccess_sel', (object)[
                        'userid' => $userid,
                        'courseid' => $courseid,
                        'conditionid' => $conditionid,
                        'optionid' => $optionid,
                        'timecreated' => $now,
                        'timemodified' => $now,
                    ]);
                    if ($current) {
                        self::log($userid, $courseid, $conditionid, null, $optionid, $changedby, $now);
                    }
                }
            }

            // The profile field is what Moodle's restrictions read.
            $chosen = [];
            foreach ($optionids as $optionid) {
                $chosen[] = $condition->options[$optionid];
            }
            $value = self::profile_value($condition, $chosen);
            if (self::stored_value($userid, $courseid, $conditionid) !== $value) {
                local_courseaccess_save_to_profile($userid, $courseid, $conditionid, $value);
            }

            $transaction->allow_commit();
        } catch (\Throwable $e) {
            $transaction->rollback($e);
        }

        \local_courseaccess_runtime_cache::reset();

        return ['changed' => (bool)($added || $removed), 'added' => $added, 'removed' => $removed];
    }

    /**
     * The value stored in the user's profile field for the condition.
     *
     * @param int $userid
     * @param int $courseid
     * @param int $conditionid
     * @return string|null Null when nothing is stored
     */
    public static function stored_value(int $userid, int $courseid, int $conditionid): ?string {
        global $DB;
        $shortname = local_courseaccess_get_field_shortname($courseid, $conditionid);
        $value = $DB->get_field_sql(
            "SELECT d.data
               FROM {user_info_data} d
               JOIN {user_info_field} f ON f.id = d.fieldid
              WHERE d.userid = :userid AND f.shortname = :shortname",
            ['userid' => $userid, 'shortname' => $shortname]
        );
        return $value === false ? null : (string)$value;
    }

    /**
     * Add a line to the change history.
     *
     * @param int $userid Whose choice changed
     * @param int $courseid
     * @param int $conditionid
     * @param int|null $old The option left behind (null: an option was added)
     * @param int|null $new The option chosen (null: an option was removed)
     * @param int $changedby Who changed it
     * @param int $time
     */
    private static function log(
        int $userid,
        int $courseid,
        int $conditionid,
        ?int $old,
        ?int $new,
        int $changedby,
        int $time
    ): void {
        global $DB;
        $DB->insert_record('local_courseaccess_history', (object)[
            'userid' => $userid,
            'courseid' => $courseid,
            'conditionid' => $conditionid,
            'old_optionid' => $old,
            'new_optionid' => $new,
            'changed_by' => $changedby,
            'timecreated' => $time,
        ]);
    }
}
