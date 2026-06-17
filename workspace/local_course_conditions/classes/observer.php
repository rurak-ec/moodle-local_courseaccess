<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Event observer for Course Conditions
 *
 * This observer listens to Moodle events and triggers the condition
 * selection modal when a student views a course but hasn't completed
 * their selection yet.
 *
 * @package    local_course_conditions
 * @copyright  2025 Rurak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_course_conditions;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');

/**
 * Event observer class
 *
 * Observers are registered in db/events.php and automatically called
 * by Moodle when the corresponding events are triggered.
 */
class observer
{

    /**
     * Observer for user_enrolment_created event
     *
     * Currently not used. Was previously used to trigger selection on enrollment,
     * but we now handle it on course_viewed instead for better UX.
     *
     * @param \core\event\user_enrolment_created $event
     */
    public static function user_enrolment_created(\core\event\user_enrolment_created $event)
    {
        // Intentionally left empty
        // Could be used in future to send email notifications or pre-populate data
    }

    /**
     * Observer for course_viewed event
     *
     * This is the main entry point for the modal injection logic.
     * When a student views a course, this function:
     * 1. Checks if they've completed the condition selection
     * 2. If not, injects JavaScript to display a blocking modal
     *
     * The modal forces the student to make a selection before accessing
     * course content.
     *
     * @param \core\event\course_viewed $event The course view event
     */
    public static function course_viewed(\core\event\course_viewed $event)
    {
        global $USER, $PAGE;

        // SKIP 1: Admins never see the modal
        if (is_siteadmin()) {
            return;
        }

        $courseid = $event->courseid;
        $userid = $USER->id;

        // SKIP 2: Teachers/managers with configure permission don't see the modal
        $context = \context_course::instance($courseid);
        if (has_capability('local/course_conditions:configure', $context)) {
            return; // They access configure.php instead
        }

        // SKIP 3: If user has already completed all selections, don't show modal
        // This checks the profile field directly to ensure the value is actually saved
        if (local_course_conditions_user_has_completed_all($userid, $courseid)) {
            return;
        }

        // Get the condition for this course
        $conditions = local_course_conditions_get_conditions_for_course($courseid);
        if (empty($conditions)) {
            return; // No conditions defined = no modal needed
        }

        // Get the first condition (only one per course)
        $condition = reset($conditions);

        // SKIP 4: If condition is disabled, don't show modal
        if (empty($condition->enabled)) {
            return; // Condition disabled = no modal, no restrictions
        }

        // Inject JavaScript that will display the blocking modal
        local_course_conditions_inject_modal($courseid, $condition);
    }
}
