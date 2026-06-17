<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Cadenas de idioma en español para el plugin Course Conditionals
 *
 * @package    local_course_conditions
 * @copyright  2025 Rurak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Nombre del plugin
$string['pluginname'] = 'Condiciones de Curso';
$string['course_conditions'] = 'Condiciones de Curso';

// Capacidades
$string['course_conditions:configure'] = 'Configurar condicionales de curso';
$string['course_conditions:viewreports'] = 'Ver reportes de condicionales de curso';
$string['course_conditions:manageconditionals'] = 'Gestionar condicionales globales';

// Navegación
$string['manageconditionals'] = 'Gestionar Condicionales';
$string['configureconditions'] = 'Configurar Condiciones del Curso';
$string['manageoptions'] = 'Gestionar Opciones';
$string['assignconditional'] = 'Asignar Condicional';
$string['backtocourse'] = 'Volver al curso';

// Gestión de condicionales
$string['conditional'] = 'Condicional';
$string['conditionals'] = 'Condicionales';
$string['addconditional'] = 'Agregar Condicional';
$string['editconditional'] = 'Editar Condicional';
$string['deleteconditional'] = 'Eliminar Condicional';
$string['conditionalname'] = 'Nombre del Condicional';
$string['conditionalname_help'] = 'Nombre del condicional (ej: "Grupo de Laboratorio", "Modo de Examen", "Pista de Estudio")';
$string['conditionaldescription'] = 'Descripción';
$string['conditionaldescription_help'] = 'Descripción opcional explicando para qué sirve este condicional';
$string['noconditionals'] = 'No se han creado condicionales aún. Crea tu primer condicional para comenzar.';
$string['conditionalcreated'] = 'Condicional creado exitosamente';
$string['conditionalupdated'] = 'Condicional actualizado exitosamente';
$string['conditionaldeleted'] = 'Condicional eliminado exitosamente';
$string['conditionalarchived'] = 'Condicional archivado exitosamente';
$string['deleteconditionalconfirm'] = '¿Está seguro que desea eliminar el condicional "{$a}"? Esto también eliminará todas sus opciones.';
$string['conditionalinuse'] = 'Este condicional no puede eliminarse porque está asignado a uno o más cursos.';
$string['conditionalnotfound'] = 'Condicional no encontrado';

// Gestión de opciones
$string['option'] = 'Opción';
$string['options'] = 'Opciones';
$string['addoption'] = 'Agregar Opción';
$string['editoption'] = 'Editar Opción';
$string['deleteoption'] = 'Eliminar Opción';
$string['optionname'] = 'Nombre de la Opción';
$string['optionname_help'] = 'Nombre visible para esta opción (ej: "Grupo A", "Mañana", "Pista 1")';
$string['optionvalue'] = 'Valor de la Opción';
$string['optionvalue_help'] = 'Valor técnico opcional almacenado en el campo de perfil. Si está vacío, se usará el nombre.';
$string['nooptions'] = 'No se han agregado opciones aún. Agregue al menos una opción para que los estudiantes puedan elegir.';
$string['optioncreated'] = 'Opción creada exitosamente';
$string['optionupdated'] = 'Opción actualizada exitosamente';
$string['optiondeleted'] = 'Opción eliminada exitosamente';
$string['deleteoptionconfirm'] = '¿Está seguro que desea eliminar la opción "{$a}"?';
$string['optioninuse'] = 'Esta opción no puede eliminarse porque ha sido seleccionada por uno o más usuarios.';
$string['optionnotfound'] = 'Opción no encontrada';
$string['nooptionsavailable'] = 'No hay opciones disponibles para selección. Por favor contacte al administrador del curso.';
$string['availableoptions'] = 'Opciones Disponibles';

// Asignación a cursos
$string['assignment'] = 'Asignación';
$string['assignments'] = 'Asignaciones';
$string['selectconditional'] = 'Seleccionar Condicional';
$string['selectconditional_help'] = 'Elija un condicional para asignar a este curso. Los estudiantes deberán seleccionar una de sus opciones.';
$string['custommessage'] = 'Mensaje Personalizado';
$string['custommessage_help'] = 'Mensaje opcional para mostrar a los estudiantes cuando necesiten hacer su selección.';
$string['currentassignment'] = 'Asignación Actual';
$string['noassignment'] = 'No hay ningún condicional asignado actualmente a este curso.';
$string['conditionalassigned'] = 'Condicional asignado al curso exitosamente';
$string['assignmentdeleted'] = 'Asignación eliminada exitosamente';
$string['assignmentupdated'] = 'Asignación actualizada exitosamente';
$string['errorassigning'] = 'Error al asignar el condicional al curso';
$string['conditionalexists'] = 'Este condicional ya está asignado a este curso';
$string['invalidconditional'] = 'Condicional seleccionado inválido';

// Selección de estudiante
$string['selectconditional'] = 'Realice su Selección';
$string['selectcondition'] = 'Realice su Selección';
$string['selectoption'] = 'Seleccione {$a}';
$string['selectoption_default'] = 'Por favor seleccione una opción';
$string['saveselection'] = 'Guardar Selección';
$string['selectionsaved'] = 'Su selección ha sido guardada exitosamente';
$string['selectionssaved'] = 'Sus selecciones han sido guardadas exitosamente';
$string['alreadyselected'] = 'Ya ha realizado una selección para este curso';
$string['currentselection'] = 'Selección Actual';
$string['errorsaving'] = 'Error al guardar su selección. Por favor intente nuevamente.';
$string['invalidoption'] = 'Opción seleccionada inválida';

// Cambio de selección
$string['changeselection'] = 'Cambiar Selección';
$string['changeselection_nav'] = 'Cambiar {$a}';
$string['selectionchanged'] = 'Su selección ha sido cambiada exitosamente';
$string['errorchanging'] = 'Error al cambiar su selección. Por favor intente nuevamente.';
$string['changeselectionwarning'] = '⚠️ Cambiar su selección puede afectar su acceso a actividades y recursos del curso. Las restricciones de acceso se aplicarán inmediatamente después del cambio.';

// Campos de perfil
$string['profilefield'] = 'Campo de Perfil';
$string['profilefieldcategory'] = 'Condicionales de Curso';

// Común
$string['active'] = 'Activo';
$string['archived'] = 'Archivado';
$string['archive'] = 'Archivar';
$string['enabled'] = 'Habilitado';
$string['disabled'] = 'Deshabilitado';
$string['sortorder'] = 'Orden';
$string['sortorder_help'] = 'Orden de visualización (números menores aparecen primero)';
$string['actions'] = 'Acciones';

// Configuración avanzada
$string['advancedsettings'] = 'Configuración Avanzada';
$string['customid'] = 'ID Personalizado';
$string['customid_help'] = 'Identificador personalizado para el campo de perfil. Por defecto es el ID del curso. Si lo cambias, los campos de perfil de todos los estudiantes se migrarán automáticamente.';
$string['enablecondition'] = 'Habilitar Condición';
$string['enablecondition_help'] = 'Si deshabilitas la condición, los estudiantes no verán el modal de selección y no se aplicarán restricciones. Los datos de selección se conservarán y podrás reactivar la condición más tarde.';
$string['conditionenabled'] = 'Condición habilitada';
$string['conditiondisabled'] = 'Condición deshabilitada';
$string['migrationwarning'] = '⚠️ Advertencia: Cambiar el ID personalizado migrará todos los campos de perfil de los estudiantes. Esta operación puede tardar varios minutos si hay muchos estudiantes.';
$string['migrationcomplete'] = 'Migración completada: {$a} campos de perfil actualizados';
$string['migrationfailed'] = 'Error durante la migración: {$a}';

// Errores
$string['invalidcourse'] = 'Curso inválido';
$string['error'] = 'Error';
$string['requiredfieldmissing'] = 'Falta un campo requerido';

// Privacidad
$string['privacy:metadata:local_course_conditions_selections'] = 'Almacena las selecciones de usuarios para condicionales de curso';
$string['privacy:metadata:local_course_conditions_selections:userid'] = 'El ID del usuario que realizó la selección';
$string['privacy:metadata:local_course_conditions_selections:courseid'] = 'El ID del curso';
$string['privacy:metadata:local_course_conditions_selections:optionid'] = 'El ID de la opción seleccionada';
$string['privacy:metadata:local_course_conditions_selections:timecreated'] = 'Momento en que se realizó la selección';
$string['privacy:metadata:local_course_conditions_selections:timemodified'] = 'Momento en que se cambió la selección por última vez';

$string['privacy:metadata:local_course_conditions_history'] = 'Almacena el historial de cambios de selección';
$string['privacy:metadata:local_course_conditions_history:userid'] = 'El ID del usuario cuya selección cambió';
$string['privacy:metadata:local_course_conditions_history:courseid'] = 'El ID del curso';
$string['privacy:metadata:local_course_conditions_history:old_optionid'] = 'La opción anterior';
$string['privacy:metadata:local_course_conditions_history:new_optionid'] = 'La nueva opción';
$string['privacy:metadata:local_course_conditions_history:changed_by'] = 'El ID del usuario que realizó el cambio';
$string['privacy:metadata:local_course_conditions_history:timecreated'] = 'Momento en que se realizó el cambio';

// Página de configuración (admin)
$string['settings'] = 'Configuración';
$string['manageconditionals_desc'] = 'Crear y gestionar condicionales globales que pueden asignarse a cursos';
