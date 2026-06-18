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
 * - Confirms before re-activating a paused condition that already has selections,
 *   because activation re-prompts every student.
 *
 * @module     local_course_access/configure_form
 * @copyright  2025 Rurak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['core/templates', 'core/notification', 'core/str'], function(Templates, Notification, Str) {

    var MIN_OPTIONS = 2;

    /**
     * Turn a display name into a safe technical value.
     * @param {String} text
     * @return {String}
     */
    function slugify(text) {
        return text
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '') // Strip accents.
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '_')                       // Non-alnum -> underscore.
            .replace(/^_+|_+$/g, '');                          // Trim underscores.
    }

    /**
     * @return {HTMLElement|null} The options container.
     */
    function getContainer() {
        return document.getElementById('lcc-options');
    }

    /**
     * Next unique index for a new option row.
     * @return {Number}
     */
    function nextIndex() {
        var max = -1;
        getContainer().querySelectorAll('.lcc-option-row').forEach(function(row) {
            var i = parseInt(row.getAttribute('data-option-index'), 10);
            if (!isNaN(i) && i > max) {
                max = i;
            }
        });
        return max + 1;
    }

    /**
     * Render and append a new empty option row.
     * @return {Promise}
     */
    function addOption() {
        var index = nextIndex();
        return Templates.render('local_course_access/option_row', {
            index: index,
            id: '',
            name: '',
            value: ''
        }).then(function(html) {
            getContainer().insertAdjacentHTML('beforeend', html);
            return index;
        }).catch(Notification.exception);
    }

    /**
     * Remove a row unless it would drop below the minimum.
     * @param {HTMLElement} row
     */
    function removeOption(row) {
        var container = getContainer();
        if (container.querySelectorAll('.lcc-option-row').length <= MIN_OPTIONS) {
            Str.get_string('minoptionswarning', 'local_course_access').then(function(msg) {
                Notification.addNotification({message: msg, type: 'warning'});
                return msg;
            }).catch(Notification.exception);
            return;
        }
        row.remove();
    }

    /**
     * Delegated input handler: name -> value autocomplete; track manual edits.
     * @param {Event} e
     */
    function handleInput(e) {
        var target = e.target;
        if (target.classList.contains('lcc-option-name')) {
            var valueInput = target.closest('.lcc-option-row').querySelector('.lcc-option-value');
            if (valueInput && valueInput.getAttribute('data-manual') !== '1') {
                valueInput.value = slugify(target.value);
            }
        } else if (target.classList.contains('lcc-option-value')) {
            // Teacher edited the value directly: stop auto-fill (unless they cleared it).
            target.setAttribute('data-manual', target.value === '' ? '0' : '1');
        }
    }

    /**
     * Delegated click handler for remove buttons.
     * @param {Event} e
     */
    function handleClick(e) {
        var btn = e.target.closest('.lcc-remove-option');
        if (btn) {
            e.preventDefault();
            removeOption(btn.closest('.lcc-option-row'));
        }
    }

    /**
     * Count option rows that have BOTH a display name and a technical value.
     * @return {Number}
     */
    function countValidOptions() {
        var n = 0;
        getContainer().querySelectorAll('.lcc-option-row').forEach(function(row) {
            var name = row.querySelector('.lcc-option-name');
            var val = row.querySelector('.lcc-option-value');
            if (name && val && name.value.trim() !== '' && val.value.trim() !== '') {
                n++;
            }
        });
        return n;
    }

    return {
        init: function() {
            var container = getContainer();
            if (!container) {
                return;
            }

            var form = document.getElementById('lcc-condition-form');
            var sw = document.getElementById('lcc-enabled');
            var nameEl = document.getElementById('lcc-name');
            var addBtn = document.getElementById('lcc-add-option');
            var badge = document.getElementById('lcc-state-badge');
            var initialEnabled = sw ? sw.checked : false;
            var hasSelections = !!form && form.getAttribute('data-hasselections') === '1';
            var confirmed = false;

            Str.get_strings([
                {key: 'statusactive', component: 'local_course_access'},
                {key: 'statuspaused', component: 'local_course_access'},
                {key: 'activationgate', component: 'local_course_access'},
                {key: 'confirm_reactivate_title', component: 'local_course_access'},
                {key: 'confirm_reactivate_body', component: 'local_course_access'},
                {key: 'confirm_reactivate_yes', component: 'local_course_access'}
            ]).then(function(s) {
                var str = {
                    active: s[0], paused: s[1], gate: s[2],
                    ctitle: s[3], cbody: s[4], cyes: s[5]
                };

                // Reflect the current state in the badge.
                var updateBadge = function() {
                    if (!badge || !sw) {
                        return;
                    }
                    badge.textContent = sw.checked ? str.active : str.paused;
                    badge.className = 'badge ' + (sw.checked ? 'bg-success' : 'bg-secondary');
                };

                // Disable activation until the minimum (name + >=2 valid options) exists.
                var updateGate = function() {
                    if (sw) {
                        var ok = nameEl && nameEl.value.trim() !== '' && countValidOptions() >= MIN_OPTIONS;
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
                container.addEventListener('input', function(e) {
                    handleInput(e);
                    updateGate();
                });
                container.addEventListener('click', function(e) {
                    handleClick(e);
                    updateGate();
                });
                if (addBtn) {
                    addBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        addOption().then(updateGate).catch(Notification.exception);
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
                    form.addEventListener('submit', function(e) {
                        if (confirmed || !sw) {
                            return;
                        }
                        if (sw.checked && !initialEnabled && hasSelections) {
                            e.preventDefault();
                            Notification.saveCancel(str.ctitle, str.cbody, str.cyes, function() {
                                confirmed = true;
                                form.submit();
                            });
                        }
                    });
                }

                updateGate();
                return s;
            }).catch(Notification.exception);
        }
    };
});
