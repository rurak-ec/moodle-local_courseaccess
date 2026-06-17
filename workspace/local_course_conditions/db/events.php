<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Event observers for Career Selection plugin
 *
 * @package    local_course_conditions
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        'eventname' => '\core\event\user_enrolment_created',
        'callback' => '\local_course_conditions\observer::user_enrolment_created',
    ],
    [
        'eventname' => '\core\event\course_viewed',
        'callback' => '\local_course_conditions\observer::course_viewed',
    ],
];
