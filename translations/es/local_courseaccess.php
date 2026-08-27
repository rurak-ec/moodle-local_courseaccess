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
 * Cadenas de idioma en español para el plugin Course Conditionals
 *
 * @package    local_courseaccess
 * @copyright  2025 Rurak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['actions'] = 'Acciones';
$string['activation_reset_notice'] = 'Acceso activado. Se volverá a pedir la selección a todos los estudiantes; las selecciones previas se reiniciaron.';
$string['activationgate'] = 'Para activar necesitas un nombre y al menos 2 opciones (cada una con nombre y valor).';
$string['active'] = 'Activo';
$string['addconditional'] = 'Agregar Acceso';
$string['addoption'] = 'Agregar Opción';
$string['advancedoptions'] = 'Opciones avanzadas';
$string['advancedsettings'] = 'Configuración Avanzada';
$string['allowchange'] = 'Permitir que los estudiantes cambien su elección';
$string['allowchange_help'] = 'Por defecto, una vez que el estudiante elige no puede cambiar su opción (y tampoco puede editarla desde su perfil). Si activas esto, aparecerá un enlace "Cambiar selección" en el curso para que pueda volver a elegir. Cambiar la opción reevalúa de inmediato su acceso a las secciones, actividades y recursos restringidos.';
$string['alreadyselected'] = 'Ya ha realizado una selección para este curso';
$string['archive'] = 'Archivar';
$string['archived'] = 'Archivado';
$string['assignconditional'] = 'Asignar Acceso';
$string['assignment'] = 'Asignación';
$string['assignmentdeleted'] = 'Asignación eliminada exitosamente';
$string['assignments'] = 'Asignaciones';
$string['assignmentupdated'] = 'Asignación actualizada exitosamente';
$string['availableoptions'] = 'Opciones Disponibles';
$string['backtocourse'] = 'Volver al curso';
$string['changenotallowed'] = 'El profesor no permite cambiar tu elección en este curso.';
$string['changeselection'] = 'Cambiar Selección';
$string['changeselection_nav'] = 'Cambiar {$a}';
$string['changeselectionwarning'] = '⚠️ Cambiar su selección puede afectar su acceso a secciones, actividades y recursos del curso. Las restricciones de acceso se aplicarán inmediatamente después del cambio.';
$string['conditionactive'] = 'Acceso activo';
$string['conditionactive_help'] = 'Controla si se muestra el modal de selección a los estudiantes al ingresar al curso. Los accesos nuevos se crean en pausa para que puedas configurarlos primero. Al pausar el acceso, no se borran las opciones ya elegidas por los alumnos, pero los nuevos estudiantes no verán el modal. Al reactivarlo tras haber estado en pausa, se restablecen las selecciones de todos para que vuelvan a elegir. (Nota: Esto no apaga las restricciones que hayas configurado en las secciones, actividades o recursos del curso).';
$string['conditional'] = 'Acceso';
$string['conditionalarchived'] = 'Acceso archivado exitosamente';
$string['conditionalassigned'] = 'Acceso asignado al curso exitosamente';
$string['conditionalcreated'] = 'Acceso creado exitosamente';
$string['conditionaldeleted'] = 'Acceso eliminado exitosamente';
$string['conditionaldescription'] = 'Descripción';
$string['conditionaldescription_help'] = 'Descripción opcional explicando para qué sirve este acceso';
$string['conditionaldescription_placeholder'] = 'Descripción opcional para los estudiantes';
$string['conditionalexists'] = 'Este acceso ya está asignado a este curso';
$string['conditionalinuse'] = 'Este acceso no puede eliminarse porque está asignado a uno o más cursos.';
$string['conditionalname'] = 'Nombre';
$string['conditionalname_help'] = 'Nombre de la clasificación (ej: "Carrera", "Especialidad", "Paralelo", "Campus", "Modalidad")';
$string['conditionalname_placeholder'] = 'Ej: Carrera, Especialidad, Paralelo, Campus';
$string['conditionalnotfound'] = 'Acceso no encontrado';
$string['conditionals'] = 'Accesos';
$string['conditionalupdated'] = 'Acceso actualizado exitosamente';
$string['conditiondisabled'] = 'Acceso deshabilitado';
$string['conditionenabled'] = 'Acceso habilitado';
$string['configintro_heading'] = 'Cómo funciona';
$string['configintro_text'] = 'Define cómo se dará acceso a las secciones, actividades y recursos a los estudiantes (por ejemplo, "Carrera", "Especialidad" o "Paralelo"). Al entrar al curso, cada estudiante elegirá su opción y podrás mostrar u ocultar tanto secciones completas como actividades y recursos individuales según lo que haya elegido.';
$string['configureconditions'] = 'Acceso al curso';
$string['confirm_change_body'] = 'Cambiar tu elección modificará al instante a qué contenido del curso puedes acceder. ¿Deseas continuar?';
$string['confirm_change_title'] = 'Cambiar tu elección';
$string['confirm_change_yes'] = 'Sí, cambiar';
$string['confirm_reactivate_body'] = 'Esto volverá a pedir la selección a todos los estudiantes y reiniciará las selecciones actuales. ¿Continuar?';
$string['confirm_reactivate_title'] = 'Reactivar el acceso';
$string['confirm_reactivate_yes'] = 'Sí, reactivar';
$string['connectionerror'] = 'Error de conexión. Inténtalo de nuevo.';
$string['courseaccess'] = 'Accesos del curso';
$string['courseaccess:configure'] = 'Configurar accesos de curso';
$string['courseaccess:manageconditionals'] = 'Gestionar accesos globales';
$string['courseaccess:viewreports'] = 'Ver reportes de accesos de curso';
$string['currentassignment'] = 'Asignación Actual';
$string['currentselection'] = 'Selección Actual';
$string['customid'] = 'ID Personalizado';
$string['customid_help'] = 'Identificador del campo de perfil. Por defecto es el nombre corto del curso (saneado, sin espacios ni símbolos). Solo se puede cambiar antes de que el campo esté en uso por selecciones o restricciones.';
$string['customid_locked'] = 'No se puede cambiar: el campo ya está en uso (hay selecciones o restricciones que lo usan). Cambiarlo rompería las restricciones existentes.';
$string['custommessage'] = 'Mensaje Personalizado';
$string['custommessage_help'] = 'Mensaje opcional para mostrar a los estudiantes cuando necesiten hacer su selección.';
$string['deleteconditional'] = 'Eliminar Acceso';
$string['deleteconditionalconfirm'] = '¿Está seguro que desea eliminar el acceso "{$a}"? Esto también eliminará todas sus opciones.';
$string['deleteoption'] = 'Eliminar Opción';
$string['deleteoptionconfirm'] = '¿Está seguro que desea eliminar la opción "{$a}"?';
$string['disabled'] = 'Deshabilitado';
$string['editconditional'] = 'Editar Acceso';
$string['editoption'] = 'Editar Opción';
$string['enablecondition'] = 'Habilitar Acceso';
$string['enablecondition_help'] = 'si deshabilitas el acceso, los estudiantes no verán el modal de selección y no se aplicarán restricciones. Los datos de selección se conservarán y podrás reactivar el acceso más tarde.';
$string['enabled'] = 'Habilitado';
$string['error'] = 'Error';
$string['error_activate_requirements'] = 'Para activar el acceso necesitas un nombre y al menos 2 opciones, cada una con nombre y valor.';
$string['error_condition_name_required'] = 'El nombre de la condición es requerido.';
$string['error_min2options'] = 'Debes definir al menos 2 opciones.';
$string['error_minoptions'] = 'El acceso "{$a}" necesita al menos 2 opciones.';
$string['error_no_condition_data'] = 'No se recibieron datos de la condición.';
$string['error_optionfields'] = 'Cada opción debe tener un nombre visible y un valor.';
$string['errorassigning'] = 'Error al asignar el acceso al curso';
$string['errorchanging'] = 'Error al cambiar su selección. Por favor intente nuevamente.';
$string['errorsaving'] = 'Error al guardar su selección. Por favor intente nuevamente.';
$string['invalidconditional'] = 'Acceso seleccionado inválido';
$string['invalidcourse'] = 'Curso inválido';
$string['invalidoption'] = 'Opción seleccionada inválida';
$string['manageconditionals'] = 'Gestionar Accesos';
$string['manageconditionals_desc'] = 'Crear y gestionar accesos globales que pueden asignarse a cursos';
$string['manageoptions'] = 'Gestionar Opciones';
$string['migrationcomplete'] = 'Migración completada: {$a} campos de perfil actualizados';
$string['migrationfailed'] = 'Error durante la migración: {$a}';
$string['migrationwarning'] = '⚠️ Advertencia: Cambiar el ID personalizado migrará todos los campos de perfil de los estudiantes. Esta operación puede tardar varios minutos si hay muchos estudiantes.';
$string['minoptionswarning'] = 'Debes mantener al menos 2 opciones.';
$string['modalselectlabel'] = 'Selecciona una opción:';
$string['mustselect'] = 'Debes seleccionar una opción.';
$string['nextsteps_field'] = 'Campo de perfil a usar';
$string['nextsteps_heading'] = 'Instrucciones de configuración';
$string['nextsteps_intro'] = 'Tu acceso ya está activo. Para mostrar contenidos distintos a cada grupo, puedes agregar restricciones de acceso por perfil tanto a actividades y recursos individuales (tareas, cuestionarios, foros, etc.) como a secciones o temas completos del curso.';
$string['nextsteps_paused'] = 'Este acceso está pausado. Configura las restricciones con el campo de abajo y luego actívalo para que los estudiantes elijan.';
$string['nextsteps_step1'] = 'Edita la actividad, recurso o sección del curso y abre el apartado "Restringir acceso".';
$string['nextsteps_step2'] = 'Pulsa "Añadir restricción" y selecciona "Perfil de usuario".';
$string['nextsteps_step3'] = 'En el primer menú selecciona el campo "{$a}", mantén la condición "es igual a", y en el campo "Valor" escribe el valor exacto de la opción que debe ver ese contenido.';
$string['nextsteps_valuestable'] = 'Valores de tus opciones (cópialos en las restricciones):';
$string['noassignment'] = 'No hay ningún acceso asignado actualmente a este curso.';
$string['noconditionals'] = 'No se han creado accesos aún. Crea tu primer acceso para comenzar.';
$string['nooptions'] = 'No se han agregado opciones aún. Agregue al menos una opción para que los estudiantes puedan elegir.';
$string['nooptionsavailable'] = 'No hay opciones disponibles para selección. Por favor contacte al administrador del curso.';
$string['option'] = 'Opción';
$string['optionallabel'] = '(opcional)';
$string['optioncreated'] = 'Opción creada exitosamente';
$string['optiondeleted'] = 'Opción eliminada exitosamente';
$string['optioninuse'] = 'Esta opción no puede eliminarse porque ha sido seleccionada por uno o más usuarios.';
$string['optionname'] = 'Nombre de la Opción';
$string['optionname_help'] = 'Nombre visible para esta opción (ej: "Presencial", "En línea", "Campus Norte", "Paralelo A")';
$string['optionname_placeholder'] = 'Nombre visible (ej: En línea, Presencial, Paralelo A)';
$string['optionnotfound'] = 'Opción no encontrada';
$string['options'] = 'Opciones';
$string['optionsintro'] = 'Agrega al menos 2 opciones. Cada estudiante deberá elegir una.';
$string['optionupdated'] = 'Opción actualizada exitosamente';
$string['optionvalue'] = 'Valor';
$string['optionvalue_help'] = 'Este es el valor exacto que debes ingresar en la restricción de "Perfil de usuario" para mostrar contenido solo a quienes elijan esta opción. Se sugiere automáticamente desde el nombre, pero puedes editarlo (sin espacios ni acentos).';
$string['optionvalue_placeholder'] = 'Valor (ej: presencial, en_linea, paralelo_a)';
$string['paused_with_restrictions'] = 'Guardado en pausa. {$a} actividad(es) restringen contenido con este campo: mientras esté pausado, los estudiantes que no eligieron (incluidos los nuevos) no podrán verlas.';
$string['pluginname'] = 'Accesos del curso';
$string['privacy:history'] = 'Historial de selecciones de acceso al curso';
$string['privacy:metadata:local_courseaccess_history'] = 'Almacena el historial de cambios de selección';
$string['privacy:metadata:local_courseaccess_history:changed_by'] = 'El ID del usuario que realizó el cambio';
$string['privacy:metadata:local_courseaccess_history:conditionid'] = 'El ID de la condición.';
$string['privacy:metadata:local_courseaccess_history:courseid'] = 'El ID del curso';
$string['privacy:metadata:local_courseaccess_history:new_optionid'] = 'La nueva opción';
$string['privacy:metadata:local_courseaccess_history:old_optionid'] = 'La opción anterior';
$string['privacy:metadata:local_courseaccess_history:timecreated'] = 'Momento en que se realizó el cambio';
$string['privacy:metadata:local_courseaccess_history:userid'] = 'El ID del usuario cuya selección cambió';
$string['privacy:metadata:local_courseaccess_sel'] = 'Almacena las selecciones de usuarios para accesos de curso';
$string['privacy:metadata:local_courseaccess_sel:conditionid'] = 'El ID de la condición.';
$string['privacy:metadata:local_courseaccess_sel:courseid'] = 'El ID del curso';
$string['privacy:metadata:local_courseaccess_sel:optionid'] = 'El ID de la opción seleccionada';
$string['privacy:metadata:local_courseaccess_sel:timecreated'] = 'Momento en que se realizó la selección';
$string['privacy:metadata:local_courseaccess_sel:timemodified'] = 'Momento en que se cambió la selección por última vez';
$string['privacy:metadata:local_courseaccess_sel:userid'] = 'El ID del usuario que realizó la selección';
$string['privacy:selections'] = 'Selecciones de acceso al curso';
$string['profilefield'] = 'Campo de Perfil';
$string['profilefield_help'] = 'Identificador técnico generado automáticamente a partir del ID del curso. Úsalo al configurar restricciones de acceso en las secciones, actividades y recursos de Moodle.';
$string['profilefieldcategory'] = 'Accesos de curso';
$string['removeoption'] = 'Eliminar';
$string['requiredfieldmissing'] = 'Falta un campo requerido';
$string['saved_paused_notice'] = 'Guardado en pausa. Los estudiantes aún no verán nada. Actívalo cuando termines de configurar las restricciones.';
$string['saveselection'] = 'Guardar Selección';
$string['saving'] = 'Guardando...';
$string['selectcondition'] = 'Realice su Selección';
$string['selectconditional'] = 'Realice su Selección';
$string['selectconditional_help'] = 'Elija un acceso para asignar a este curso. Los estudiantes deberán seleccionar una de sus opciones.';
$string['selectionchanged'] = 'Su selección ha sido cambiada exitosamente';
$string['selectionsaved'] = 'Su selección ha sido guardada exitosamente';
$string['selectionssaved'] = 'Sus selecciones han sido guardadas exitosamente';
$string['selectoption'] = 'Seleccione {$a}';
$string['selectoption_default'] = 'Por favor seleccione una opción';
$string['settings'] = 'Configuración';
$string['sortorder'] = 'Orden';
$string['sortorder_help'] = 'Orden de visualización (números menores aparecen primero)';
$string['statusactive'] = 'Activo';
$string['statuspaused'] = 'Pausado';
$string['step1_heading'] = 'Paso 1: Define el acceso';
$string['step2_heading'] = 'Paso 2: Agrega las opciones';
$string['unknownerror'] = 'Ocurrió un error desconocido.';
