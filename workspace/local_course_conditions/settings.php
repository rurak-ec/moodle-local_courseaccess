<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Settings for Course Conditionals plugin
 *
 * @package    local_course_conditions
 * @copyright  2025 Rurak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    // Create the category for the plugin
    $ADMIN->add('localplugins', new admin_category('local_course_conditions',
        get_string('pluginname', 'local_course_conditions')));

    // Add link to manage conditionals
    $ADMIN->add('local_course_conditions', new admin_externalpage(
        'local_course_conditions_conditionals',
        get_string('manageconditionals', 'local_course_conditions'),
        new moodle_url('/local/course_conditions/manage_conditionals.php'),
        'local/course_conditions:manageconditionals'
    ));

    // Add empty settings page (required by Moodle)
    $settings = new admin_settingpage('local_course_conditions_settings',
        get_string('settings', 'local_course_conditions'));

    if ($ADMIN->fulltree) {
        // Add description
        $settings->add(new admin_setting_heading(
            'local_course_conditions_desc',
            '',
            get_string('manageconditionals_desc', 'local_course_conditions')
        ));
    }

    $ADMIN->add('local_course_conditions', $settings);
}
