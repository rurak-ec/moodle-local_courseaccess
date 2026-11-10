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
 * The "Restrict access" conditions of a course that read a condition's profile field.
 *
 * Moodle stores the restrictions of a section or an activity as a JSON tree in its availability column; a user profile
 * condition is a leaf like {"type":"profile","cf":"acc_2","op":"isequalto","v":"ENF"}. This class only reads them, to
 * tell the teacher when they do not fit the selection type.
 *
 * @package    local_courseaccess
 * @copyright  2026 Rurak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restrictions {
    /**
     * How many restrictions of the course read the profile field, by operator.
     *
     * @param int $courseid
     * @param string $shortname The profile field shortname
     * @return int[] Operator (isequalto, contains…) => number of restrictions
     */
    public static function count_by_operator(int $courseid, string $shortname): array {
        global $DB;
        $counts = [];
        $needle = '%' . $DB->sql_like_escape('"cf":"' . $shortname . '"') . '%';
        foreach (['course_modules', 'course_sections'] as $table) {
            $trees = $DB->get_fieldset_select(
                $table,
                'availability',
                'course = :courseid AND ' . $DB->sql_like('availability', ':needle'),
                ['courseid' => $courseid, 'needle' => $needle]
            );
            foreach ($trees as $json) {
                $tree = json_decode((string)$json);
                if (is_object($tree)) {
                    self::count_node($tree, $shortname, $counts);
                }
            }
        }
        return $counts;
    }

    /**
     * Count the profile conditions of the field in a node of the tree and everything under it.
     *
     * @param \stdClass $node A tree ({op, c: [...]}) or a condition ({type, ...})
     * @param string $shortname
     * @param int[] $counts Operator => number, added to in place
     */
    private static function count_node(\stdClass $node, string $shortname, array &$counts): void {
        if (isset($node->c) && is_array($node->c)) {
            foreach ($node->c as $child) {
                if (is_object($child)) {
                    self::count_node($child, $shortname, $counts);
                }
            }
            return;
        }
        if (($node->type ?? '') !== 'profile' || ($node->cf ?? '') !== $shortname) {
            return;
        }
        $operator = (string)($node->op ?? '');
        $counts[$operator] = ($counts[$operator] ?? 0) + 1;
    }
}
