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
 * Output hook callbacks for Course Conditions.
 *
 * Replaces the previous (fragile) approach of injecting JavaScript from a
 * \core\event\course_viewed observer. Event observers must not depend on output
 * or $PAGE, and course_viewed also fires from the core_course_view_course web
 * service (mobile app / REST), where js_call_amd() does nothing. This hook only
 * runs during a real HTML page render, with $PAGE fully set up and output not
 * yet started, so it is the reliable injection point.
 *
 * @package    local_courseaccess
 * @copyright  2025 Rurak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_courseaccess;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');

/**
 * Hook callback container.
 */
class hook_callbacks {
    /**
     * Inject the blocking condition modal on course-view pages.
     *
     * Fires for the before_standard_top_of_body_html_generation output hook.
     * Scope is limited to course main pages (pagetype course-view-*), matching
     * the reach of the old course_viewed observer.
     *
     * @param \core\hook\output\before_standard_top_of_body_html_generation $hook
     */
    public static function inject_condition_modal(
        \core\hook\output\before_standard_top_of_body_html_generation $hook
    ): void {
        global $PAGE, $USER;

        // Only on real course-view pages (not dashboard, profile, activities, etc.).
        if (strpos((string)$PAGE->pagetype, 'course-view') !== 0) {
            return;
        }

        // Need a logged-in real user and an actual course (not the site front page).
        if (!isloggedin() || isguestuser()) {
            return;
        }
        if (empty($PAGE->course) || $PAGE->course->id <= SITEID) {
            return;
        }

        $courseid = $PAGE->course->id;

        // Shared decision path: returns the pending condition or null.
        $condition = local_courseaccess_get_pending_condition($USER->id, $courseid);
        if ($condition === null) {
            return;
        }

        local_courseaccess_inject_modal($courseid, $condition);
    }
}
