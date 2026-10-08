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
 * Version information for Course Access plugin
 *
 * @package    local_courseaccess
 * @copyright  2025 Rurak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->component = 'local_courseaccess';
$plugin->version   = 2026100801;  // The current plugin version (Date: YYYYMMDDXX).
$plugin->requires  = 2022112800;  // Requires Moodle 4.1 LTS (2022112800) or later.
$plugin->supported = [401, 503];  // Supported from Moodle 4.1 to 5.3+.
$plugin->maturity  = MATURITY_STABLE;
$plugin->release   = 'v2.4.9';
