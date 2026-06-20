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
 * Test data generator for local_course_access.
 *
 * @package    local_course_access
 * @copyright  2025 Rurak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Generator: creates course access conditions (with options) for tests/Behat.
 */
class local_course_access_generator extends component_generator_base {
    /**
     * Behat-creatable entities for this plugin.
     *
     * @return array
     */
    public function get_creatable_entities(): array {
        return [
            'conditions' => [
                'datagenerator' => 'condition',
                'required' => ['course'],
                'switchids' => ['course' => 'courseid'],
            ],
        ];
    }

    /**
     * Create a condition with its options on a course.
     *
     * The "options" field is a comma-separated list of "Display:value" pairs
     * (value defaults to the display name when omitted).
     *
     * @param array|stdClass $record Must include courseid; may include name, options, enabled, allowchange.
     * @return stdClass The created condition record.
     */
    public function create_condition($record): stdClass {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/local/course_access/lib.php');

        $record = (array) $record;
        if (empty($record['courseid'])) {
            throw new coding_exception('A course is required to create a condition.');
        }

        $options = [];
        $index = 0;
        foreach (explode(',', $record['options'] ?? 'Morning:morning,Afternoon:afternoon') as $pair) {
            $pair = trim($pair);
            if ($pair === '') {
                continue;
            }
            $parts = explode(':', $pair, 2);
            $name = trim($parts[0]);
            $value = isset($parts[1]) && trim($parts[1]) !== '' ? trim($parts[1]) : $name;
            $options[] = ['id' => null, 'name' => $name, 'value' => $value, 'sortorder' => $index++];
        }

        local_course_access_save_conditions((int) $record['courseid'], [[
            'id' => null,
            'name' => $record['name'] ?? 'Shift',
            'description' => $record['description'] ?? '',
            'custom_id' => $record['custom_id'] ?? '',
            'enabled' => isset($record['enabled']) ? (int) $record['enabled'] : 1,
            'allowchange' => isset($record['allowchange']) ? (int) $record['allowchange'] : 0,
            'sortorder' => 0,
            'options' => $options,
        ]]);

        return $DB->get_record('local_course_access_cond', ['courseid' => $record['courseid']], '*', MUST_EXIST);
    }
}
