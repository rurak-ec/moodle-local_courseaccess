// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Condition selection modal
 *
 * @module     local_course_access/condition_modal
 * @copyright  2025 Rurak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/ajax', 'core/notification'], function($, Ajax, Notification) {
    
    return {
        /**
         * Initialize the modal
         * @param {Object} conditionData - Contains condition info and options
         */
        init: function(conditionData) {
            var self = this;
            
            // Create modal HTML
            var modalHtml = this.createModal(conditionData);
            $('body').append(modalHtml);
            
            // Show modal
            $('#course_access-modal').show();
            
            // Handle form submission
            $('#course_access-form').on('submit', function(e) {
                e.preventDefault();
                self.saveSelection(conditionData);
            });
            
            // Prevent closing
            $('#course_access-modal').on('click', function(e) {
                e.stopPropagation();
            });
        },
        
        /**
         * Create modal HTML
         * @param {Object} data - Condition data
         * @returns {string} HTML string
         */
        createModal: function(data) {
            var optionsHtml = '';
            data.options.forEach(function(opt) {
                optionsHtml += '<option value="' + opt.id + '">' + opt.name + '</option>';
            });
            
            return `
                <div id="course_access-modal" style="
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0,0,0,0.8);
                    z-index: 9999;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                ">
                    <div style="
                        background: white;
                        padding: 30px;
                        border-radius: 8px;
                        max-width: 500px;
                        width: 90%;
                        box-shadow: 0 4px 20px rgba(0,0,0,0.3);
                    ">
                        <h3 style="margin-top: 0;">${data.condition.name}</h3>
                        ${data.condition.description ? '<p>' + data.condition.description + '</p>' : ''}
                        
                        <form id="course_access-form">
                            <div class="form-group">
                                <label for="condition-select"><strong>Selecciona una opción:</strong></label>
                                <select id="condition-select" class="form-control custom-select" required>
                                    <option value="">-- Elegir --</option>
                                    ${optionsHtml}
                                </select>
                            </div>
                            
                            <div style="text-align: right; margin-top: 20px;">
                                <button type="submit" class="btn btn-primary">Guardar</button>
                            </div>
                        </form>
                        
                        <div id="course_access-loading" style="display:none; text-align:center; margin-top: 15px;">
                            <div class="spinner-border" role="status">
                                <span class="sr-only">Guardando...</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        },
        
        /**
         * Save selection via AJAX
         * @param {Object} data - Condition data
         */
        saveSelection: function(data) {
            var optionId = $('#condition-select').val();
            
            if (!optionId) {
                Notification.alert('Error', 'Debes seleccionar una opción', 'OK');
                return;
            }
            
            // Show loading
            $('#course_access-form').hide();
            $('#course_access-loading').show();
            
            // AJAX call
            $.ajax({
                url: M.cfg.wwwroot + '/local/course_access/ajax.php',
                method: 'POST',
                data: {
                    sesskey: M.cfg.sesskey,
                    courseid: data.courseid,
                    conditionid: data.condition.id,
                    optionid: optionId
                },
                dataType: 'json'
            }).done(function(response) {
                if (response.success) {
                    // Reload page to show course content
                    window.location.reload();
                } else {
                    Notification.alert('Error', response.error || 'Error desconocido', 'OK');
                    $('#course_access-form').show();
                    $('#course_access-loading').hide();
                }
            }).fail(function() {
                Notification.alert('Error', 'Error de conexión. Intenta nuevamente.', 'OK');
                $('#course_access-form').show();
                $('#course_access-loading').hide();
            });
        }
    };
});
