<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Course conditions configuration page
 *
 * @package    local_course_conditions
 * @copyright  2025 Rurak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/local/course_conditions/lib.php');

$courseid = required_param('courseid', PARAM_INT);
$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);

require_login($course);
$context = context_course::instance($course->id);
require_capability('local/course_conditions:configure', $context);

$PAGE->set_url('/local/course_conditions/configure.php', ['courseid' => $course->id]);
$PAGE->set_context($context);
$PAGE->set_title(get_string('configureconditions', 'local_course_conditions'));
$PAGE->set_heading($course->fullname);

// Handle Form Submission
if ($data = data_submitted() && confirm_sesskey()) {
    try {
        // Get condition data from $_POST directly
        if (!isset($_POST['condition']) || !is_array($_POST['condition'])) {
            throw new moodle_exception('error', 'local_course_conditions', '', 'No se recibieron datos de la condición');
        }

        $raw_condition = $_POST['condition'];

        // Clean and validate condition data
        $condition_id = isset($raw_condition['id']) && is_numeric($raw_condition['id']) ? intval($raw_condition['id']) : null;
        $condition_name = isset($raw_condition['name']) ? clean_param($raw_condition['name'], PARAM_TEXT) : '';
        $condition_desc = isset($raw_condition['description']) ? clean_param($raw_condition['description'], PARAM_TEXT) : '';
        $custom_id = isset($raw_condition['custom_id']) ? clean_param($raw_condition['custom_id'], PARAM_TEXT) : (string)$course->id;
        $enabled = isset($raw_condition['enabled']) ? 1 : 0;

        if (empty($condition_name)) {
            throw new moodle_exception('error', 'local_course_conditions', '', 'El nombre de la condición es requerido');
        }

        // Validate custom_id (must not be empty)
        if (empty($custom_id)) {
            $custom_id = (string)$course->id;
        }

        // Structure for lib function (expects array of conditions)
        $condition_data = [
            'id' => $condition_id,
            'name' => $condition_name,
            'description' => $condition_desc,
            'custom_id' => $custom_id,
            'enabled' => $enabled,
            'sortorder' => 0,
            'options' => []
        ];

        // Process options
        if (isset($raw_condition['options']) && is_array($raw_condition['options'])) {
            foreach ($raw_condition['options'] as $oid => $odata) {
                if (!is_array($odata)) {
                    continue;
                }

                $option_id = isset($odata['id']) && is_numeric($odata['id']) ? intval($odata['id']) : null;
                $option_name = isset($odata['name']) ? clean_param($odata['name'], PARAM_TEXT) : '';
                $option_value = isset($odata['value']) ? clean_param($odata['value'], PARAM_TEXT) : '';

                if (empty($option_name) || empty($option_value)) {
                    continue; // Skip empty options
                }

                $condition_data['options'][] = [
                    'id' => $option_id,
                    'name' => $option_name,
                    'value' => $option_value,
                    'sortorder' => intval($oid)
                ];
            }
        }

        // Validate: at least 2 options required
        if (count($condition_data['options']) < 2) {
            throw new moodle_exception('error', 'local_course_conditions', '', 'Se requieren al menos 2 opciones');
        }

        // Pass as array of 1
        local_course_conditions_save_conditions($course->id, [$condition_data]);
        \core\notification::success(get_string('changessaved'));
        redirect($PAGE->url);

    } catch (Exception $e) {
        \core\notification::error($e->getMessage());
    }
}

// Get existing conditions (take the first one if exists)
$conditions = local_course_conditions_get_conditions_for_course($course->id);
$current_condition = !empty($conditions) ? reset($conditions) : null;

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('configureconditions', 'local_course_conditions'));

// Start Form
echo html_writer::start_tag('form', ['action' => $PAGE->url, 'method' => 'post', 'id' => 'condition-form']);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);

// Single Condition Container
echo html_writer::div('', 'condition-container', ['id' => 'condition-container']);

// Save Button
echo html_writer::div(
    html_writer::tag('button', get_string('savechanges'), ['type' => 'submit', 'class' => 'btn btn-primary mt-3']),
    'form-actions'
);

echo html_writer::end_tag('form');

// Prepare JS Data
$js_condition = $current_condition ? $current_condition : null;
$js_data = json_encode($js_condition);

?>
<style>
.condition-card {
    border: 1px solid #dee2e6;
    border-radius: 0.25rem;
    margin-bottom: 1rem;
    padding: 1.5rem;
    background-color: #fff;
}
.option-row {
    display: flex;
    gap: 10px;
    margin-bottom: 10px;
    align-items: flex-start;
}
.option-row input {
    flex: 1;
}
.option-row button {
    flex-shrink: 0;
}
.btn-add-option {
    margin-top: 10px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var container = document.getElementById('condition-container');
    var conditionData = <?php echo $js_data; ?>;
    var optionCounter = 0;

    // Render condition form
    function renderConditionForm() {
        var conditionId = conditionData ? conditionData.id : '';
        var conditionName = conditionData ? conditionData.name : '';
        var conditionDesc = conditionData ? conditionData.description : '';

        var html = `
            <div class="condition-card">
                <input type="hidden" name="condition[id]" value="${conditionId}">

                <div class="form-group">
                    <label for="condition-name">
                        <?php echo get_string('conditionalname', 'local_course_conditions'); ?>
                        <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           class="form-control"
                           id="condition-name"
                           name="condition[name]"
                           value="${conditionName}"
                           required
                           placeholder="Ej: Turno, Grupo de Laboratorio, Modalidad">
                    <small class="form-text text-muted">
                        <?php echo get_string('conditionalname_help', 'local_course_conditions'); ?>
                    </small>
                </div>

                <div class="form-group mt-3">
                    <label for="condition-desc">
                        <?php echo get_string('conditionaldescription', 'local_course_conditions'); ?>
                    </label>
                    <textarea class="form-control"
                              id="condition-desc"
                              name="condition[description]"
                              rows="2"
                              placeholder="Descripción opcional para los estudiantes">${conditionDesc}</textarea>
                    <small class="form-text text-muted">
                        <?php echo get_string('conditionaldescription_help', 'local_course_conditions'); ?>
                    </small>
                </div>

                <hr class="my-4">

                <h5><?php echo get_string('advancedsettings', 'local_course_conditions'); ?></h5>

                <div class="form-group mt-3">
                    <label for="condition-customid">
                        <?php echo get_string('customid', 'local_course_conditions'); ?>
                    </label>
                    <input type="text"
                           class="form-control"
                           id="condition-customid"
                           name="condition[custom_id]"
                           value="${conditionData ? (conditionData.custom_id || '<?php echo $course->id; ?>') : '<?php echo $course->id; ?>'}"
                           placeholder="<?php echo $course->id; ?>">
                    <small class="form-text text-muted">
                        <?php echo get_string('customid_help', 'local_course_conditions'); ?>
                    </small>
                    <small class="form-text text-warning d-block mt-1">
                        <?php echo get_string('migrationwarning', 'local_course_conditions'); ?>
                    </small>
                </div>

                <div class="form-group mt-3">
                    <div class="form-check">
                        <input type="checkbox"
                               class="form-check-input"
                               id="condition-enabled"
                               name="condition[enabled]"
                               value="1"
                               ${conditionData && conditionData.enabled != '0' ? 'checked' : ''}>
                        <label class="form-check-label" for="condition-enabled">
                            <?php echo get_string('enablecondition', 'local_course_conditions'); ?>
                        </label>
                    </div>
                    <small class="form-text text-muted">
                        <?php echo get_string('enablecondition_help', 'local_course_conditions'); ?>
                    </small>
                </div>

                <hr class="my-4">

                <h5><?php echo get_string('options', 'local_course_conditions'); ?> <span class="text-danger">*</span></h5>
                <small class="form-text text-muted mb-3 d-block">
                    Agrega al menos 2 opciones. Los estudiantes deberán seleccionar una.
                </small>

                <div id="options-container"></div>

                <button type="button" class="btn btn-secondary btn-sm btn-add-option" onclick="addOption()">
                    <i class="fa fa-plus"></i> <?php echo get_string('addoption', 'local_course_conditions'); ?>
                </button>
            </div>
        `;

        container.innerHTML = html;

        // Render existing options or add 2 empty ones
        if (conditionData && conditionData.options) {
            var options = Array.isArray(conditionData.options) ?
                          conditionData.options :
                          Object.values(conditionData.options);

            options.forEach(function(option) {
                addOption(option.id, option.name, option.value);
            });
        } else {
            // Add 2 empty options for new condition
            addOption();
            addOption();
        }
    }

    // Add option row
    window.addOption = function(optionId, optionName, optionValue) {
        optionId = optionId || '';
        optionName = optionName || '';
        optionValue = optionValue || '';

        var optionsContainer = document.getElementById('options-container');
        var optionDiv = document.createElement('div');
        optionDiv.className = 'option-row';
        optionDiv.dataset.optionIndex = optionCounter;

        optionDiv.innerHTML = `
            <input type="hidden" name="condition[options][${optionCounter}][id]" value="${optionId}">

            <input type="text"
                   class="form-control"
                   name="condition[options][${optionCounter}][name]"
                   value="${optionName}"
                   placeholder="Nombre visible (ej: Mañana, Grupo A)"
                   required>

            <input type="text"
                   class="form-control"
                   name="condition[options][${optionCounter}][value]"
                   value="${optionValue}"
                   placeholder="Valor técnico (ej: turno_manana, grupo_a)"
                   required>

            <button type="button" class="btn btn-danger btn-sm" onclick="removeOption(this)">
                <i class="fa fa-trash"></i> Eliminar
            </button>
        `;

        optionsContainer.appendChild(optionDiv);
        optionCounter++;
    };

    // Remove option row
    window.removeOption = function(button) {
        var optionRow = button.closest('.option-row');
        var optionsContainer = document.getElementById('options-container');

        // Don't allow removing if only 2 options left
        if (optionsContainer.children.length <= 2) {
            alert('Debes mantener al menos 2 opciones');
            return;
        }

        optionRow.remove();
    };

    // Initialize form
    renderConditionForm();
});
</script>

<?php
echo $OUTPUT->footer();
