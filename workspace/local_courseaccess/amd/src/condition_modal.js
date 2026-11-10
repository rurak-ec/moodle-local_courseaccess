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
 * its text from language strings, and the choice is saved through the
 * local_courseaccess_save_selection web service. It deliberately uses a
 * template-rendered overlay rather than a core/modal dialog because the gate
 * must not be dismissable.
 *
 * A condition is a list (the student picks one option in a dropdown) or
 * checkboxes (the student ticks one or more, up to the condition's maximum).
 *
 * @module     local_courseaccess/condition_modal
 * @copyright  2025 Rurak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Templates from 'core/templates';
import {getString} from 'core/str';
import {saveSelection} from './repository';

const SELECTORS = {
    OVERLAY: '[data-region="lcc-modal"]',
    FORM: '[data-region="lcc-modal-form"]',
    SELECT: '[data-region="lcc-modal-select"]',
    OPTION: '[data-region="lcc-modal-option"]',
    SAVE: '[data-region="lcc-modal-save"]',
    ERROR: '[data-region="lcc-modal-error"]',
};

/**
 * Initialise and show the blocking selection overlay.
 *
 * @param {object} data The condition data:
 *     {courseid, condition:{id,name,description}, multiple, maxselect, options:[{id,name}]}.
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
    const boxes = Array.from(overlay.querySelectorAll(SELECTORS.OPTION));
    const saveButton = overlay.querySelector(SELECTORS.SAVE);
    const errorBox = overlay.querySelector(SELECTORS.ERROR);
    const max = data.multiple ? (parseInt(data.maxselect, 10) || 0) : 0;

    const first = select || boxes[0];
    if (first) {
        first.focus();
    }

    const showError = (message) => {
        errorBox.textContent = message;
        errorBox.hidden = false;
    };

    // The chosen option ids: the ticked boxes, or the option of the dropdown.
    const chosen = () => {
        if (data.multiple) {
            return boxes.filter((box) => box.checked).map((box) => parseInt(box.value, 10));
        }
        return select && select.value ? [parseInt(select.value, 10)] : [];
    };

    // At the maximum, the boxes that are not ticked cannot be ticked.
    const applyMaximum = () => {
        if (!max) {
            return;
        }
        const full = boxes.filter((box) => box.checked).length >= max;
        boxes.forEach((box) => {
            box.disabled = full && !box.checked;
        });
    };

    // Choosing something clears the message of a previous attempt.
    const onChoice = () => {
        errorBox.hidden = true;
        applyMaximum();
    };
    boxes.forEach((box) => box.addEventListener('change', onChoice));
    if (select) {
        select.addEventListener('change', onChoice);
    }

    form.addEventListener('submit', async(e) => {
        e.preventDefault();
        errorBox.hidden = true;

        const optionids = chosen();
        if (!optionids.length) {
            showError(await getString('mustselect', 'local_courseaccess'));
            return;
        }

        const savelabel = saveButton.textContent;
        saveButton.disabled = true;
        saveButton.textContent = await getString('saving', 'local_courseaccess');

        try {
            await saveSelection(data.courseid, data.condition.id, optionids);
            window.location.reload();
            return;
        } catch (error) {
            const fallback = await getString('connectionerror', 'local_courseaccess');
            showError(error && error.message ? error.message : fallback);
        }

        saveButton.disabled = false;
        saveButton.textContent = savelabel;
    });
};
