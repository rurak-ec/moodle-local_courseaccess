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
 * AJAX endpoint for condition selection
 *
 * This endpoint is called by the JavaScript modal (condition_modal.js) when
 * a student selects an option. It validates the selection and saves it to:
 * 1. The selections table (for history tracking)
 * 2. The user's profile field (for Moodle's access restrictions)
 *
 * Expected POST parameters:
 * - sesskey: Moodle session key (security)
 * - courseid: Course ID
 * - conditionid: Condition ID
 * - optionid: Selected option ID
 *
 * Returns JSON with success/error status
 *
 * @package    local_courseaccess
 * @copyright  2025 Rurak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);

require_once('../../config.php');
require_once($CFG->dirroot . '/local/courseaccess/lib.php');

// Security: Require authenticated user and valid session key.
require_login();
require_sesskey();

// Get parameters from AJAX request.
$courseid = required_param('courseid', PARAM_INT);
$conditionid = required_param('conditionid', PARAM_INT);
$optionid = required_param('optionid', PARAM_INT);

// Verify user is enrolled in the course.
$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
require_login($course);

// Set JSON response header.
header('Content-Type: application/json');

try {
    // VALIDATION 0: This endpoint only serves the first-time blocking modal. Confirm.
    // The user is genuinely pending this exact (enabled) condition; this rejects.
    // Admins/configurers, already-completed users and disabled conditions.
    $pending = local_courseaccess_get_pending_condition($USER->id, $courseid);
    if (!$pending || (int)$pending->id !== $conditionid) {
        throw new moodle_exception('changenotallowed', 'local_courseaccess');
    }

    // VALIDATION 1: Verify condition belongs to this course.
    // This prevents tampering (e.g., selecting a condition from another course).
    $condition = $DB->get_record('local_courseaccess_cond', [
        'id' => $conditionid,
        'courseid' => $courseid,
    ], '*', MUST_EXIST);

    // VALIDATION 2: Verify option belongs to this condition.
    // This prevents selecting an option from a different condition.
    $option = $DB->get_record('local_courseaccess_options', [
        'id' => $optionid,
        'conditionid' => $conditionid,
    ], '*', MUST_EXIST);

    // Save the selection (both to selections table and profile field).
    local_courseaccess_save_selection($USER->id, $courseid, $conditionid, $optionid);

    // Return success response.
    echo json_encode([
        'success' => true,
        'message' => get_string('selectionsaved', 'local_courseaccess'),
    ]);
} catch (Exception $e) {
    // Return error response (HTTP 400 Bad Request).
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
    ]);
}
