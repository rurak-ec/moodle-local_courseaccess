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
 * Behat data generator for local_course_access.
 *
 * @package    local_course_access
 * @copyright  2025 Rurak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Behat data generator: exposes the plugin's conditions to "the following ... exist".
 */
class behat_local_course_access_generator extends behat_generator_base {
    /**
     * Entities this plugin can create from a Behat "Given the following ... exist" step.
     *
     * @return array
     */
    protected function get_creatable_entities(): array {
        return [
            'conditions' => [
                'singular' => 'condition',
                'datagenerator' => 'condition',
                'required' => ['course'],
                'switchids' => ['course' => 'courseid'],
            ],
        ];
    }
}
