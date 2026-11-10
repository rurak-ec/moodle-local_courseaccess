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
 * Upgrade steps for local_courseaccess.
 *
 * db/install.xml always describes the current schema; each step below brings an
 * older installation to it.
 *
 * @package    local_courseaccess
 * @copyright  2025 Rurak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Execute upgrade steps.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_local_courseaccess_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2026111000) {
        // Conditions: how students choose (one option from a list, or several with checkboxes) and the maximum.
        $table = new xmldb_table('local_courseaccess_cond');
        $field = new xmldb_field('multiple', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'allowchange');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('maxselect', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'multiple');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Selections: one row per chosen option, instead of one row per condition.
        $table = new xmldb_table('local_courseaccess_sel');
        $index = new xmldb_index('user_condition_idx', XMLDB_INDEX_UNIQUE, ['userid', 'conditionid']);
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }
        $index = new xmldb_index('user_condition_option_idx', XMLDB_INDEX_UNIQUE, ['userid', 'conditionid', 'optionid']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // History: removing an option from a checkbox selection has no new option.
        $table = new xmldb_table('local_courseaccess_history');
        $field = new xmldb_field('new_optionid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'old_optionid');
        $dbman->change_field_notnull($table, $field);

        upgrade_plugin_savepoint(true, 2026111000, 'local', 'courseaccess');
    }

    return true;
}
