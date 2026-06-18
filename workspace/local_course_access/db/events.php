<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Event observers for Course Conditions plugin.
 *
 * The blocking modal used to be injected from a \core\event\course_viewed
 * observer. That was moved to the before_standard_top_of_body_html_generation
 * output hook (see db/hooks.php), because event observers must not depend on
 * output/$PAGE and course_viewed also fires in non-HTML contexts (mobile/web
 * service) where modal injection is impossible.
 *
 * @package    local_course_access
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$observers = [];
