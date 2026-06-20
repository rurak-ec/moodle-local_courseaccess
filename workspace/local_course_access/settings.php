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
 * Settings for Course Conditionals plugin
 *
 * @package    local_course_access
 * @copyright  2025 Rurak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    // The plugin is configured per course (via the course navigation), so there are.
    // No global settings. Expose a short description page so the plugin is.
    // Discoverable under Site administration > Plugins > Local plugins.
    $settings = new admin_settingpage(
        'local_course_access_settings',
        get_string('pluginname', 'local_course_access')
    );

    if ($ADMIN->fulltree) {
        $settings->add(new admin_setting_heading(
            'local_course_access_desc',
            '',
            get_string('manageconditionals_desc', 'local_course_access')
        ));
    }

    $ADMIN->add('localplugins', $settings);
}
