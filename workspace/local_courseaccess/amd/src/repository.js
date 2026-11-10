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
 * Web service calls of Course access.
 *
 * @module     local_courseaccess/repository
 * @copyright  2026 Rurak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {call as fetchMany} from 'core/ajax';

/**
 * Save the options the current user chooses for the pending condition of a course.
 *
 * @param {number} courseid The course.
 * @param {number} conditionid The condition.
 * @param {number[]} optionids The chosen options: one for a list, one or more for checkboxes.
 * @return {Promise<{saved: boolean}>} Resolves once saved; rejects with the server's error.
 */
export const saveSelection = (courseid, conditionid, optionids) => fetchMany([{
    methodname: 'local_courseaccess_save_selection',
    args: {courseid, conditionid, optionids},
}])[0];
