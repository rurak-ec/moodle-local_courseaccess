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
 * Confirmation before a student changes their course-access selection.
 *
 * Changing the selection re-evaluates the student's access to restricted
 * content immediately, so we ask for an explicit confirmation first.
 *
 * @module     local_course_access/change_confirm
 * @copyright  2025 Rurak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['core/notification', 'core/str'], function(Notification, Str) {

    return {
        init: function() {
            var form = document.getElementById('change-selection-form');
            if (!form) {
                return;
            }

            var confirmed = false;

            form.addEventListener('submit', function(e) {
                if (confirmed) {
                    return;
                }
                e.preventDefault();

                Str.get_strings([
                    {key: 'confirm_change_title', component: 'local_course_access'},
                    {key: 'confirm_change_body', component: 'local_course_access'},
                    {key: 'confirm_change_yes', component: 'local_course_access'}
                ]).then(function(s) {
                    Notification.saveCancel(s[0], s[1], s[2], function() {
                        confirmed = true;
                        form.submit();
                    });
                    return s;
                }).catch(Notification.exception);
            });
        }
    };
});
