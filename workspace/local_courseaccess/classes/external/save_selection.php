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
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use local_courseaccess\selection;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../lib.php');

/**
 * Save the first choice a student makes in the blocking overlay of a course.
 *
 * It only serves the condition that is still pending for the current user: once the student has chosen, a change goes
 * through change_selection.php (when the teacher allows it).
 *
 * @package    local_courseaccess
 * @copyright  2026 Rurak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class save_selection extends external_api {
    /**
     * Parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course id'),
            'conditionid' => new external_value(PARAM_INT, 'Condition id'),
            'optionids' => new external_multiple_structure(
                new external_value(PARAM_INT, 'Option id'),
                'The chosen options: one for a list, one or more for checkboxes'
            ),
        ]);
    }

    /**
     * Save the choice of the current user.
     *
     * @param int $courseid
     * @param int $conditionid
     * @param int[] $optionids
     * @return array
     */
    public static function execute(int $courseid, int $conditionid, array $optionids): array {
        global $USER;
        ['courseid' => $courseid, 'conditionid' => $conditionid, 'optionids' => $optionids] = self::validate_parameters(
            self::execute_parameters(),
            ['courseid' => $courseid, 'conditionid' => $conditionid, 'optionids' => $optionids]
        );
        self::validate_context(\context_course::instance($courseid));

        // Admins, teachers and students who already chose have nothing pending.
        $pending = local_courseaccess_get_pending_condition((int)$USER->id, $courseid);
        if (!$pending || (int)$pending->id !== $conditionid) {
            throw new \moodle_exception('changenotallowed', 'local_courseaccess');
        }

        selection::set((int)$USER->id, $courseid, $pending, $optionids);

        return ['saved' => true];
    }

    /**
     * Returns.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'saved' => new external_value(PARAM_BOOL, 'Whether the choice was saved'),
        ]);
    }
}
