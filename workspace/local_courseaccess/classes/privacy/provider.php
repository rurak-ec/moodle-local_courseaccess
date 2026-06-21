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
 * Privacy Subsystem implementation for local_courseaccess.
 *
 * @package    local_courseaccess
 * @copyright  2025 Rurak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_courseaccess\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy provider: the plugin stores each user's course condition selection and a change history.
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\request\plugin\provider {
    /**
     * Describe the personal data stored by this plugin.
     *
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table('local_courseaccess_sel', [
            'userid' => 'privacy:metadata:local_courseaccess_sel:userid',
            'courseid' => 'privacy:metadata:local_courseaccess_sel:courseid',
            'conditionid' => 'privacy:metadata:local_courseaccess_sel:conditionid',
            'optionid' => 'privacy:metadata:local_courseaccess_sel:optionid',
            'timecreated' => 'privacy:metadata:local_courseaccess_sel:timecreated',
            'timemodified' => 'privacy:metadata:local_courseaccess_sel:timemodified',
        ], 'privacy:metadata:local_courseaccess_sel');

        $collection->add_database_table('local_courseaccess_history', [
            'userid' => 'privacy:metadata:local_courseaccess_history:userid',
            'courseid' => 'privacy:metadata:local_courseaccess_history:courseid',
            'conditionid' => 'privacy:metadata:local_courseaccess_history:conditionid',
            'old_optionid' => 'privacy:metadata:local_courseaccess_history:old_optionid',
            'new_optionid' => 'privacy:metadata:local_courseaccess_history:new_optionid',
            'changed_by' => 'privacy:metadata:local_courseaccess_history:changed_by',
            'timecreated' => 'privacy:metadata:local_courseaccess_history:timecreated',
        ], 'privacy:metadata:local_courseaccess_history');

        return $collection;
    }

    /**
     * Return the course contexts that hold data for the given user.
     *
     * @param int $userid
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();

        $contextlist->add_from_sql(
            "SELECT ctx.id
               FROM {context} ctx
               JOIN {local_courseaccess_sel} s ON s.courseid = ctx.instanceid
              WHERE ctx.contextlevel = :contextlevel AND s.userid = :userid",
            ['contextlevel' => CONTEXT_COURSE, 'userid' => $userid]
        );

        $contextlist->add_from_sql(
            "SELECT ctx.id
               FROM {context} ctx
               JOIN {local_courseaccess_history} h ON h.courseid = ctx.instanceid
              WHERE ctx.contextlevel = :contextlevel AND (h.userid = :userid OR h.changed_by = :changedby)",
            ['contextlevel' => CONTEXT_COURSE, 'userid' => $userid, 'changedby' => $userid]
        );

        return $contextlist;
    }

    /**
     * Return the users who have data within the given (course) context.
     *
     * @param userlist $userlist
     */
    public static function get_users_in_context(userlist $userlist): void {
        $context = $userlist->get_context();
        if (!$context instanceof \context_course) {
            return;
        }
        $params = ['courseid' => $context->instanceid];

        $userlist->add_from_sql(
            'userid',
            "SELECT userid FROM {local_courseaccess_sel} WHERE courseid = :courseid",
            $params
        );
        $userlist->add_from_sql(
            'userid',
            "SELECT userid FROM {local_courseaccess_history} WHERE courseid = :courseid",
            $params
        );
        $userlist->add_from_sql(
            'changed_by',
            "SELECT changed_by FROM {local_courseaccess_history} WHERE courseid = :courseid",
            $params
        );
    }

    /**
     * Export all stored data for the approved contexts of the user.
     *
     * @param approved_contextlist $contextlist
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $user = $contextlist->get_user();

        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof \context_course) {
                continue;
            }
            $courseid = $context->instanceid;

            // Current selections.
            $records = $DB->get_records_sql(
                "SELECT s.id, c.name AS conditionname, o.name AS optionname, o.value AS optionvalue,
                        s.timecreated, s.timemodified
                   FROM {local_courseaccess_sel} s
                   JOIN {local_courseaccess_cond} c ON c.id = s.conditionid
                   JOIN {local_courseaccess_options} o ON o.id = s.optionid
                  WHERE s.userid = :userid AND s.courseid = :courseid",
                ['userid' => $user->id, 'courseid' => $courseid]
            );
            $selections = [];
            foreach ($records as $r) {
                $selections[] = [
                    'condition' => $r->conditionname,
                    'option' => $r->optionname,
                    'value' => $r->optionvalue,
                    'timecreated' => transform::datetime($r->timecreated),
                    'timemodified' => transform::datetime($r->timemodified),
                ];
            }
            if ($selections) {
                writer::with_context($context)->export_data(
                    [get_string('pluginname', 'local_courseaccess'),
                        get_string('privacy:selections', 'local_courseaccess')],
                    (object) ['selections' => $selections]
                );
            }

            // Change history (changes affecting the user, and changes the user made).
            $records = $DB->get_records_sql(
                "SELECT h.id, h.userid, h.changed_by, h.new_optionid, h.timecreated
                   FROM {local_courseaccess_history} h
                  WHERE h.courseid = :courseid AND (h.userid = :userid OR h.changed_by = :changedby)",
                ['courseid' => $courseid, 'userid' => $user->id, 'changedby' => $user->id]
            );
            $history = [];
            foreach ($records as $r) {
                $history[] = [
                    'newoption' => $DB->get_field('local_courseaccess_options', 'name', ['id' => $r->new_optionid]),
                    'changedbyyou' => transform::yesno((int) $r->changed_by === (int) $user->id),
                    'timecreated' => transform::datetime($r->timecreated),
                ];
            }
            if ($history) {
                writer::with_context($context)->export_data(
                    [get_string('pluginname', 'local_courseaccess'),
                        get_string('privacy:history', 'local_courseaccess')],
                    (object) ['history' => $history]
                );
            }
        }
    }

    /**
     * Delete all plugin data for all users in the given (course) context.
     *
     * @param \context $context
     */
    public static function delete_data_for_all_users_in_context(\context $context): void {
        global $DB;
        if (!$context instanceof \context_course) {
            return;
        }
        $DB->delete_records('local_courseaccess_sel', ['courseid' => $context->instanceid]);
        $DB->delete_records('local_courseaccess_history', ['courseid' => $context->instanceid]);
    }

    /**
     * Delete all plugin data for the user across the approved contexts.
     *
     * @param approved_contextlist $contextlist
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;
        $userid = $contextlist->get_user()->id;

        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof \context_course) {
                continue;
            }
            $courseid = $context->instanceid;
            $DB->delete_records('local_courseaccess_sel', ['userid' => $userid, 'courseid' => $courseid]);
            $DB->delete_records('local_courseaccess_history', ['userid' => $userid, 'courseid' => $courseid]);
            // Anonymise this user as the actor on other people's history rows.
            $DB->set_field_select(
                'local_courseaccess_history',
                'changed_by',
                0,
                'courseid = :courseid AND changed_by = :changedby AND userid <> :userid',
                ['courseid' => $courseid, 'changedby' => $userid, 'userid' => $userid]
            );
        }
    }

    /**
     * Delete data for the listed users within the (course) context.
     *
     * @param approved_userlist $userlist
     */
    public static function delete_data_for_users(approved_userlist $userlist): void {
        global $DB;
        $context = $userlist->get_context();
        if (!$context instanceof \context_course) {
            return;
        }
        $userids = $userlist->get_userids();
        if (empty($userids)) {
            return;
        }

        [$insql, $inparams] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
        $params = array_merge(['courseid' => $context->instanceid], $inparams);
        $DB->delete_records_select(
            'local_courseaccess_sel',
            "courseid = :courseid AND userid $insql",
            $params
        );
        $DB->delete_records_select(
            'local_courseaccess_history',
            "courseid = :courseid AND userid $insql",
            $params
        );

        [$actorsql, $actorparams] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
        $actorallparams = array_merge(['courseid' => $context->instanceid], $actorparams);
        $DB->set_field_select(
            'local_courseaccess_history',
            'changed_by',
            0,
            "courseid = :courseid AND changed_by $actorsql",
            $actorallparams
        );
    }
}
