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
 * Upgrade steps for local_course_access.
 *
 * The current schema is defined in db/install.xml, so a fresh install needs no
 * upgrade steps. Add dbman steps here only for future schema changes.
 *
 * @package    local_course_access
 * @copyright  2025 Rurak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute upgrade steps.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_local_course_access_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2025012529) {
        // Add the per-condition "allow students to change their selection" flag.
        $table = new xmldb_table('local_course_access_conditions');
        $field = new xmldb_field('allowchange', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'enabled');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2025012529, 'local', 'course_access');
    }

    return true;
}
