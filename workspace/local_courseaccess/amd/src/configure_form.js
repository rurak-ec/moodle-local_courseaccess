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
 * Interactive behaviour for the course-conditions configure form.
 *
 * - Adds/removes option rows without a page reload (min 2 enforced).
 * - Auto-suggests the technical value from the display name (kept visible and
 *   editable; never overwrites a value the teacher edited or an existing value).
 * - Gates the "active" switch until the minimum config (name + 2 options) exists.
 * - Confirms before re-activating a paused condition that already has selections.
 *
 * @module     local_courseaccess/configure_form
 * @copyright  2025 Rurak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Templates from 'core/templates';
import Notification from 'core/notification';
import {getStrings, getString} from 'core/str';

const MIN_OPTIONS = 2;

/**
 * Turn a display name into a safe technical value.
 *
 * @param {string} text The display name.
 * @return {string} The slugified value.
 */
const slugify = (text) => text
    .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '_')
    .replace(/^_+|_+$/g, '');

/**
 * @return {HTMLElement|null} The options container.
 */
const getContainer = () => document.getElementById('lcc-options');

/**
 * Next unique index for a new option row.
 *
 * @return {number} The next index.
 */
const nextIndex = () => {
    let max = -1;
    getContainer().querySelectorAll('.lcc-option-row').forEach((row) => {
        const i = parseInt(row.getAttribute('data-option-index'), 10);
        if (!isNaN(i) && i > max) {
            max = i;
        }
    });
    return max + 1;
};

/**
 * Render and append a new empty option row.
 *
 * @return {Promise} Resolves once the row is appended.
 */
const addOption = () => {
    const index = nextIndex();
    return Templates.render('local_courseaccess/option_row', {index, id: '', name: '', value: ''})
        .then((html) => {
            getContainer().insertAdjacentHTML('beforeend', html);
            return index;
        }).catch(Notification.exception);
};

/**
 * Remove a row unless it would drop below the minimum.
 *
 * @param {HTMLElement} row The option row.
 */
const removeOption = (row) => {
    const container = getContainer();
    if (container.querySelectorAll('.lcc-option-row').length <= MIN_OPTIONS) {
        getString('minoptionswarning', 'local_courseaccess').then((msg) => {
            Notification.addNotification({message: msg, type: 'warning'});
            return msg;
        }).catch(Notification.exception);
        return;
    }
    row.remove();
};

/**
 * Delegated input handler: name -> value autocomplete; track manual edits.
 *
 * @param {Event} e The input event.
 */
const handleInput = (e) => {
    const target = e.target;
    if (target.classList.contains('lcc-option-name')) {
        const valueInput = target.closest('.lcc-option-row').querySelector('.lcc-option-value');
        if (valueInput && valueInput.getAttribute('data-manual') !== '1') {
            valueInput.value = slugify(target.value);
        }
    } else if (target.classList.contains('lcc-option-value')) {
        target.setAttribute('data-manual', target.value === '' ? '0' : '1');
    }
};

/**
 * Delegated click handler for remove buttons.
 *
 * @param {Event} e The click event.
 */
const handleClick = (e) => {
    const btn = e.target.closest('.lcc-remove-option');
    if (btn) {
        e.preventDefault();
        removeOption(btn.closest('.lcc-option-row'));
    }
};

/**
 * Count option rows that have BOTH a display name and a technical value.
 *
 * @return {number} The number of valid options.
 */
const countValidOptions = () => {
    let n = 0;
    getContainer().querySelectorAll('.lcc-option-row').forEach((row) => {
        const name = row.querySelector('.lcc-option-name');
        const val = row.querySelector('.lcc-option-value');
        if (name && val && name.value.trim() !== '' && val.value.trim() !== '') {
            n++;
        }
    });
    return n;
};

/**
 * Initialise the configure-form behaviour.
 */
export const init = async() => {
    const container = getContainer();
    if (!container) {
        return;
    }

    const form = document.getElementById('lcc-condition-form');
    const sw = document.getElementById('lcc-enabled');
    const nameEl = document.getElementById('lcc-name');
    const addBtn = document.getElementById('lcc-add-option');
    const badge = document.getElementById('lcc-state-badge');
    const initialEnabled = sw ? sw.checked : false;
    const hasSelections = !!form && form.getAttribute('data-hasselections') === '1';
    let confirmed = false;

    let s;
    try {
        s = await getStrings([
            {key: 'statusactive', component: 'local_courseaccess'},
            {key: 'statuspaused', component: 'local_courseaccess'},
            {key: 'activationgate', component: 'local_courseaccess'},
            {key: 'confirm_reactivate_title', component: 'local_courseaccess'},
            {key: 'confirm_reactivate_body', component: 'local_courseaccess'},
            {key: 'confirm_reactivate_yes', component: 'local_courseaccess'},
        ]);
    } catch (e) {
        Notification.exception(e);
        return;
    }
    const str = {active: s[0], paused: s[1], gate: s[2], ctitle: s[3], cbody: s[4], cyes: s[5]};

    // Reflect the current state in the badge.
    const updateBadge = () => {
        if (!badge || !sw) {
            return;
        }
        badge.textContent = sw.checked ? str.active : str.paused;
        badge.className = 'badge ' + (sw.checked ? 'bg-success' : 'bg-secondary');
    };

    // Disable activation until the minimum (name + >=2 valid options) exists.
    const updateGate = () => {
        if (sw) {
            const ok = nameEl && nameEl.value.trim() !== '' && countValidOptions() >= MIN_OPTIONS;
            if (!ok) {
                sw.checked = false;
                sw.disabled = true;
                sw.title = str.gate;
            } else {
                sw.disabled = false;
                sw.title = '';
            }
        }
        updateBadge();
    };

    // Delegated events so dynamically-added rows work without rebinding.
    container.addEventListener('input', (e) => {
        handleInput(e);
        updateGate();
    });
    container.addEventListener('click', (e) => {
        handleClick(e);
        updateGate();
    });
    if (addBtn) {
        addBtn.addEventListener('click', async(e) => {
            e.preventDefault();
            await addOption();
            updateGate();
        });
    }
    if (nameEl) {
        nameEl.addEventListener('input', updateGate);
    }
    if (sw) {
        sw.addEventListener('change', updateBadge);
    }

    // Re-activating a paused condition re-prompts every student: confirm first.
    if (form) {
        form.addEventListener('submit', (e) => {
            if (confirmed || !sw) {
                return;
            }
            if (sw.checked && !initialEnabled && hasSelections) {
                e.preventDefault();
                Notification.saveCancel(str.ctitle, str.cbody, str.cyes, () => {
                    confirmed = true;
                    form.submit();
                });
            }
        });
    }

    updateGate();
};
