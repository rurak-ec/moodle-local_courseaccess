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
 * Blocking condition-selection overlay for course-view pages.
 *
 * The overlay markup comes from a Mustache template (so every value is escaped),
 * its text from language strings, and the save request goes to ajax.php with the
 * session key. It deliberately uses a template-rendered overlay rather than a
 * core/modal dialog because the gate must not be dismissable.
 *
 * @module     local_courseaccess/condition_modal
 * @copyright  2025 Rurak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Templates from 'core/templates';
import {getString} from 'core/str';
import Config from 'core/config';

const SELECTORS = {
    OVERLAY: '[data-region="lcc-modal"]',
    FORM: '[data-region="lcc-modal-form"]',
    SELECT: '[data-region="lcc-modal-select"]',
    SAVE: '[data-region="lcc-modal-save"]',
    ERROR: '[data-region="lcc-modal-error"]',
};

/**
 * Initialise and show the blocking selection overlay.
 *
 * @param {object} data The condition data: {courseid, condition:{id,name,description}, options:[{id,name}]}.
 */
export const init = async(data) => {
    if (document.querySelector(SELECTORS.OVERLAY)) {
        return;
    }

    const html = await Templates.render('local_courseaccess/condition_modal', data);
    const wrapper = document.createElement('div');
    wrapper.innerHTML = html.trim();
    const overlay = wrapper.firstElementChild;
    if (!overlay) {
        return;
    }
    document.body.appendChild(overlay);
    document.body.classList.add('lcc-modal-open');

    const form = overlay.querySelector(SELECTORS.FORM);
    const select = overlay.querySelector(SELECTORS.SELECT);
    const saveButton = overlay.querySelector(SELECTORS.SAVE);
    const errorBox = overlay.querySelector(SELECTORS.ERROR);

    if (select) {
        select.focus();
    }

    const showError = (message) => {
        errorBox.textContent = message;
        errorBox.hidden = false;
    };

    form.addEventListener('submit', async(e) => {
        e.preventDefault();
        errorBox.hidden = true;

        const optionid = select.value;
        if (!optionid) {
            showError(await getString('mustselect', 'local_courseaccess'));
            return;
        }

        const savelabel = saveButton.textContent;
        saveButton.disabled = true;
        saveButton.textContent = await getString('saving', 'local_courseaccess');

        try {
            const body = new URLSearchParams({
                sesskey: Config.sesskey,
                courseid: data.courseid,
                conditionid: data.condition.id,
                optionid: optionid,
            });
            const response = await fetch(Config.wwwroot + '/local/courseaccess/ajax.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: body.toString(),
            });
            const result = await response.json();
            if (result.success) {
                window.location.reload();
                return;
            }
            showError(result.error || await getString('unknownerror', 'local_courseaccess'));
        } catch {
            showError(await getString('connectionerror', 'local_courseaccess'));
        }

        saveButton.disabled = false;
        saveButton.textContent = savelabel;
    });
};
