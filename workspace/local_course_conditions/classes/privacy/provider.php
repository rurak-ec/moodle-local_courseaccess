<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Privacy provider for Career Selection plugin
 *
 * @package    local_course_conditions
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_course_conditions\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy provider implementation for local_course_conditions
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\core_userlist_provider {

    /**
     * Get metadata about data stored by this plugin
     *
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'local_course_conditions_user',
            [
                'userid' => 'privacy:metadata:local_course_conditions_user:userid',
                'courseid' => 'privacy:metadata:local_course_conditions_user:courseid',
                'optionid' => 'privacy:metadata:local_course_conditions_user:optionid',
                'uniquecode' => 'privacy:metadata:local_course_conditions_user:uniquecode',
                'timecreated' => 'privacy:metadata:local_course_conditions_user:timecreated',
            ],
            'privacy:metadata:local_course_conditions_user'
        );

        return $collection;
    }

    /**
     * Get contexts for user ID
     *
     * @param int $userid
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();

        $sql = "SELECT ctx.id
                  FROM {context} ctx
                  JOIN {course} c ON c.id = ctx.instanceid AND ctx.contextlevel = :contextlevel
                  JOIN {local_course_conditions_user} lcu ON lcu.courseid = c.id
                 WHERE lcu.userid = :userid";

        $params = [
            'userid' => $userid,
            'contextlevel' => CONTEXT_COURSE,
        ];

        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Export user data
     *
     * @param approved_contextlist $contextlist
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $user = $contextlist->get_user();

        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel != CONTEXT_COURSE) {
                continue;
            }

            $courseid = $context->instanceid;

            $sql = "SELECT lcu.*, lco.name as careername, lco.code as careercode
                      FROM {local_course_conditions_user} lcu
                      JOIN {local_course_conditions_options} lco ON lco.id = lcu.optionid
                     WHERE lcu.userid = :userid AND lcu.courseid = :courseid";

            $params = ['userid' => $user->id, 'courseid' => $courseid];
            $selection = $DB->get_record_sql($sql, $params);

            if ($selection) {
                $data = (object) [
                    'career' => $selection->careername,
                    'code' => $selection->careercode,
                    'uniquecode' => $selection->uniquecode,
                    'timecreated' => transform::datetime($selection->timecreated),
                ];

                writer::with_context($context)->export_data(
                    [get_string('pluginname', 'local_course_conditions')],
                    $data
                );
            }
        }
    }

    /**
     * Delete user data for context
     *
     * @param \context $context
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if ($context->contextlevel != CONTEXT_COURSE) {
            return;
        }

        $DB->delete_records('local_course_conditions_user', ['courseid' => $context->instanceid]);
    }

    /**
     * Delete user data
     *
     * @param approved_contextlist $contextlist
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $userid = $contextlist->get_user()->id;

        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel != CONTEXT_COURSE) {
                continue;
            }

            $DB->delete_records('local_course_conditions_user', [
                'userid' => $userid,
                'courseid' => $context->instanceid,
            ]);
        }
    }

    /**
     * Get users in context
     *
     * @param userlist $userlist
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if ($context->contextlevel != CONTEXT_COURSE) {
            return;
        }

        $sql = "SELECT userid
                  FROM {local_course_conditions_user}
                 WHERE courseid = :courseid";

        $params = ['courseid' => $context->instanceid];

        $userlist->add_from_sql('userid', $sql, $params);
    }

    /**
     * Delete data for users
     *
     * @param approved_userlist $userlist
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();

        if ($context->contextlevel != CONTEXT_COURSE) {
            return;
        }

        $userids = $userlist->get_userids();

        if (empty($userids)) {
            return;
        }

        list($usersql, $userparams) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);

        $select = "courseid = :courseid AND userid $usersql";
        $params = ['courseid' => $context->instanceid] + $userparams;

        $DB->delete_records_select('local_course_conditions_user', $select, $params);
    }
}
