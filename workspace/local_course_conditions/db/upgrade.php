<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Upgrade script for Course Conditionals plugin
 *
 * @package    local_course_conditions
 * @copyright  2025 Rurak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute upgrade steps
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_local_course_conditions_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    // ========================================
    // UPGRADE TO v1.5 (2025012501)
    // Course Conditionals - Complete Redesign
    // ========================================
    if ($oldversion < 2025012501) {

        // ----------------------------------------
        // 1. DROP OLD TABLES (if they exist from previous versions)
        // ----------------------------------------

        // Drop old v1.0 tables if they exist
        $oldtables = [
            'local_course_conditions_careers',
            'local_course_conditions_groups',
            'local_course_conditions_group_careers'
        ];

        foreach ($oldtables as $tablename) {
            $table = new xmldb_table($tablename);
            if ($dbman->table_exists($table)) {
                $dbman->drop_table($table);
            }
        }

        // Also drop the old courses and options tables completely
        // We'll recreate them with the new structure
        $maintables = [
            'local_course_conditions_user',
            'local_course_conditions_options',
            'local_course_conditions_courses'
        ];

        foreach ($maintables as $tablename) {
            $table = new xmldb_table($tablename);
            if ($dbman->table_exists($table)) {
                $dbman->drop_table($table);
            }
        }

        // ----------------------------------------
        // 2. CREATE NEW TABLE: conditionals
        // ----------------------------------------
        $table = new xmldb_table('local_course_conditions_conditionals');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('archived', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('sortorder', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        $table->add_index('archived_idx', XMLDB_INDEX_NOTUNIQUE, ['archived']);
        $table->add_index('sortorder_idx', XMLDB_INDEX_NOTUNIQUE, ['sortorder']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // ----------------------------------------
        // 3. CREATE NEW TABLE: options
        // ----------------------------------------
        $table = new xmldb_table('local_course_conditions_options');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('conditionalid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('value', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('sortorder', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('conditionalid', XMLDB_KEY_FOREIGN, ['conditionalid'],
                        'local_course_conditions_conditionals', ['id']);

        $table->add_index('conditionalid_idx', XMLDB_INDEX_NOTUNIQUE, ['conditionalid']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // ----------------------------------------
        // 4. CREATE NEW TABLE: assignments
        // ----------------------------------------
        $table = new xmldb_table('local_course_conditions_assignments');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('conditionalid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('enabled', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('message', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('profilefield_shortname', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('courseid', XMLDB_KEY_FOREIGN, ['courseid'], 'course', ['id']);
        $table->add_key('conditionalid', XMLDB_KEY_FOREIGN, ['conditionalid'],
                        'local_course_conditions_conditionals', ['id']);

        $table->add_index('courseid_idx', XMLDB_INDEX_NOTUNIQUE, ['courseid']);
        $table->add_index('conditionalid_idx', XMLDB_INDEX_NOTUNIQUE, ['conditionalid']);
        $table->add_index('course_conditional_idx', XMLDB_INDEX_UNIQUE, ['courseid', 'conditionalid']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // ----------------------------------------
        // 5. CREATE NEW TABLE: selections
        // ----------------------------------------
        $table = new xmldb_table('local_course_conditions_selections');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('assignmentid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('optionid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);
        $table->add_key('courseid', XMLDB_KEY_FOREIGN, ['courseid'], 'course', ['id']);
        $table->add_key('assignmentid', XMLDB_KEY_FOREIGN, ['assignmentid'],
                        'local_course_conditions_assignments', ['id']);
        $table->add_key('optionid', XMLDB_KEY_FOREIGN, ['optionid'],
                        'local_course_conditions_options', ['id']);

        $table->add_index('userid_courseid_idx', XMLDB_INDEX_NOTUNIQUE, ['userid', 'courseid']);
        $table->add_index('user_course_assignment_idx', XMLDB_INDEX_UNIQUE,
                         ['userid', 'courseid', 'assignmentid']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // ----------------------------------------
        // 6. CREATE NEW TABLE: history
        // ----------------------------------------
        $table = new xmldb_table('local_course_conditions_history');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('assignmentid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('old_optionid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('new_optionid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('reason', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('changed_by', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);
        $table->add_key('courseid', XMLDB_KEY_FOREIGN, ['courseid'], 'course', ['id']);
        $table->add_key('assignmentid', XMLDB_KEY_FOREIGN, ['assignmentid'],
                        'local_course_conditions_assignments', ['id']);
        $table->add_key('changed_by', XMLDB_KEY_FOREIGN, ['changed_by'], 'user', ['id']);

        $table->add_index('userid_courseid_idx', XMLDB_INDEX_NOTUNIQUE, ['userid', 'courseid']);
        $table->add_index('timecreated_idx', XMLDB_INDEX_NOTUNIQUE, ['timecreated']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2025012501, 'local', 'course_conditions');
    }

    // ========================================
    // UPGRADE TO v1.5.2 (2025012502)
    // Fixed capabilities registration
    // ========================================
    if ($oldversion < 2025012502) {
        // Force capabilities refresh by clearing caches
        // This ensures the new 'manageconditionals' capability is properly registered

        // Clear capability caches using the proper Moodle API
        accesslib_clear_all_caches(true);

        // Reset roles to update capabilities from access.php
        require_once($CFG->libdir . '/accesslib.php');
        core_role_set_view_allowed();

        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2025012502, 'local', 'course_conditions');
    }

    // ========================================
    // UPGRADE TO v1.5.3 (2025012503)
    // Force capabilities refresh
    // ========================================
    if ($oldversion < 2025012503) {
        // Force capabilities refresh
        accesslib_clear_all_caches(true);

        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2025012503, 'local', 'course_conditions');
    }

    // ========================================
    // UPGRADE TO v1.5.4 (2025012504)
    // Fixed cache clearing method
    // ========================================
    if ($oldversion < 2025012504) {
        // Clear caches properly
        accesslib_clear_all_caches(true);

        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2025012504, 'local', 'course_conditions');
    }

    // Automatically generated Moodle v5.1.0 release upgrade line.
    // Put any upgrade step following this.

    // ========================================
    // UPGRADE TO v1.7.19 (2025012522)
    // Add custom_id and enabled fields to conditions table
    // ========================================
    if ($oldversion < 2025012522) {
        $table = new xmldb_table('local_course_conditions_conditions');

        // Define field custom_id to be added to local_course_conditions_conditions.
        $field = new xmldb_field('custom_id', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'description');

        // Conditionally launch add field custom_id.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field enabled to be added to local_course_conditions_conditions.
        $field = new xmldb_field('enabled', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1', 'custom_id');

        // Conditionally launch add field enabled.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // For existing conditions, set custom_id to courseid (default behavior).
        $conditions = $DB->get_records('local_course_conditions_conditions');
        foreach ($conditions as $condition) {
            if (empty($condition->custom_id)) {
                $condition->custom_id = (string)$condition->courseid;
                $DB->update_record('local_course_conditions_conditions', $condition);
            }
        }

        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2025012522, 'local', 'course_conditions');
    }

    return true;
}
