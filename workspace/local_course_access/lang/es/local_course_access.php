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
 * @package    local_course_access
 * @copyright  2025 Rurak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Nombre del plugin
$string['pluginname'] = 'Accesos del Curso';
$string['course_access'] = 'Accesos del Curso';

// Capacidades
$string['course_access:configure'] = 'Configurar accesos de curso';
$string['course_access:viewreports'] = 'Ver reportes de accesos de curso';
$string['course_access:manageconditionals'] = 'Gestionar accesos globales';

// Navegación
$string['manageconditionals'] = 'Gestionar Accesos';
$string['configureconditions'] = 'Acceso al curso';
$string['manageoptions'] = 'Gestionar Opciones';
$string['assignconditional'] = 'Asignar Acceso';
$string['backtocourse'] = 'Volver al curso';

// Gestión de accesos
$string['conditional'] = 'Acceso';
$string['conditionals'] = 'Accesos';
$string['addconditional'] = 'Agregar Acceso';
$string['editconditional'] = 'Editar Acceso';
$string['deleteconditional'] = 'Eliminar Acceso';
$string['conditionalname'] = 'Nombre del Acceso';
$string['conditionalname_help'] = 'Nombre del acceso (ej: "Grupo de Laboratorio", "Modo de Examen", "Pista de Estudio")';
$string['conditionaldescription'] = 'Descripción';
$string['conditionaldescription_help'] = 'Descripción opcional explicando para qué sirve este acceso';
$string['noconditionals'] = 'No se han creado accesos aún. Crea tu primer acceso para comenzar.';
$string['conditionalcreated'] = 'Acceso creado exitosamente';
$string['conditionalupdated'] = 'Acceso actualizado exitosamente';
$string['conditionaldeleted'] = 'Acceso eliminado exitosamente';
$string['conditionalarchived'] = 'Acceso archivado exitosamente';
$string['deleteconditionalconfirm'] = '¿Está seguro que desea eliminar el acceso "{$a}"? Esto también eliminará todas sus opciones.';
$string['conditionalinuse'] = 'Este acceso no puede eliminarse porque está asignado a uno o más cursos.';
$string['conditionalnotfound'] = 'Acceso no encontrado';

// Gestión de opciones
$string['option'] = 'Opción';
$string['options'] = 'Opciones';
$string['addoption'] = 'Agregar Opción';
$string['editoption'] = 'Editar Opción';
$string['deleteoption'] = 'Eliminar Opción';
$string['optionname'] = 'Nombre de la Opción';
$string['optionname_help'] = 'Nombre visible para esta opción (ej: "Grupo A", "Mañana", "Pista 1")';
$string['optionvalue'] = 'Valor técnico';
$string['optionvalue_help'] = 'Este es el código que usarás en "Restringir acceso" para mostrar contenido solo a quienes elijan esta opción. Se sugiere automáticamente desde el nombre, pero puedes editarlo. Sin espacios ni acentos.';
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
$string['selectconditional'] = 'Seleccionar Acceso';
$string['selectconditional_help'] = 'Elija un acceso para asignar a este curso. Los estudiantes deberán seleccionar una de sus opciones.';
$string['custommessage'] = 'Mensaje Personalizado';
$string['custommessage_help'] = 'Mensaje opcional para mostrar a los estudiantes cuando necesiten hacer su selección.';
$string['currentassignment'] = 'Asignación Actual';
$string['noassignment'] = 'No hay ningún acceso asignado actualmente a este curso.';
$string['conditionalassigned'] = 'Acceso asignado al curso exitosamente';
$string['assignmentdeleted'] = 'Asignación eliminada exitosamente';
$string['assignmentupdated'] = 'Asignación actualizada exitosamente';
$string['errorassigning'] = 'Error al asignar el acceso al curso';
$string['conditionalexists'] = 'Este acceso ya está asignado a este curso';
$string['invalidconditional'] = 'Acceso seleccionado inválido';

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
$string['profilefieldcategory'] = 'Accesos de Curso';

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
$string['customid_help'] = 'Identificador del campo de perfil. Por defecto es el nombre corto del curso (saneado, sin espacios ni símbolos). Solo se puede cambiar antes de que el campo esté en uso por selecciones o restricciones.';
$string['enablecondition'] = 'Habilitar Acceso';
$string['enablecondition_help'] = 'si deshabilitas el acceso, los estudiantes no verán el modal de selección y no se aplicarán restricciones. Los datos de selección se conservarán y podrás reactivar el acceso más tarde.';
$string['conditionenabled'] = 'Acceso habilitado';
$string['conditiondisabled'] = 'Acceso deshabilitado';
$string['migrationwarning'] = '⚠️ Advertencia: Cambiar el ID personalizado migrará todos los campos de perfil de los estudiantes. Esta operación puede tardar varios minutos si hay muchos estudiantes.';
$string['migrationcomplete'] = 'Migración completada: {$a} campos de perfil actualizados';
$string['migrationfailed'] = 'Error durante la migración: {$a}';

// Errores
$string['invalidcourse'] = 'Curso inválido';
$string['error'] = 'Error';
$string['requiredfieldmissing'] = 'Falta un campo requerido';

// Privacidad
$string['privacy:metadata:local_course_access_selections'] = 'Almacena las selecciones de usuarios para accesos de curso';
$string['privacy:metadata:local_course_access_selections:userid'] = 'El ID del usuario que realizó la selección';
$string['privacy:metadata:local_course_access_selections:courseid'] = 'El ID del curso';
$string['privacy:metadata:local_course_access_selections:optionid'] = 'El ID de la opción seleccionada';
$string['privacy:metadata:local_course_access_selections:timecreated'] = 'Momento en que se realizó la selección';
$string['privacy:metadata:local_course_access_selections:timemodified'] = 'Momento en que se cambió la selección por última vez';

$string['privacy:metadata:local_course_access_history'] = 'Almacena el historial de cambios de selección';
$string['privacy:metadata:local_course_access_history:userid'] = 'El ID del usuario cuya selección cambió';
$string['privacy:metadata:local_course_access_history:courseid'] = 'El ID del curso';
$string['privacy:metadata:local_course_access_history:old_optionid'] = 'La opción anterior';
$string['privacy:metadata:local_course_access_history:new_optionid'] = 'La nueva opción';
$string['privacy:metadata:local_course_access_history:changed_by'] = 'El ID del usuario que realizó el cambio';
$string['privacy:metadata:local_course_access_history:timecreated'] = 'Momento en que se realizó el cambio';

// Página de configuración (admin)
$string['settings'] = 'Configuración';
$string['manageconditionals_desc'] = 'Crear y gestionar accesos globales que pueden asignarse a cursos';

// ----------------------------------------------------------------------------
// Página de configuración del curso (UX v1.8)
// ----------------------------------------------------------------------------
// Introducción / contexto
$string['configintro_heading'] = 'Cómo funciona';
$string['configintro_text'] = 'Define un acceso (por ejemplo, "Turno" o "Grupo"). Cuando un estudiante entre al curso, deberá elegir una de las opciones. Después podrás mostrar actividades distintas a cada grupo restringiendo el acceso según su elección.';

// Pasos del formulario
$string['step1_heading'] = 'Paso 1: Define el acceso';
$string['step2_heading'] = 'Paso 2: Agrega las opciones';
$string['optionallabel'] = '(opcional)';
$string['optionsintro'] = 'Agrega al menos 2 opciones. Cada estudiante deberá elegir una.';

// Placeholders (localizados)
$string['conditionalname_placeholder'] = 'Ej: Turno, Grupo de Laboratorio, Modalidad';
$string['conditionaldescription_placeholder'] = 'Descripción opcional para los estudiantes';
$string['optionname_placeholder'] = 'Nombre visible (ej: Mañana, Grupo A)';
$string['optionvalue_placeholder'] = 'Valor técnico (ej: turno_manana, grupo_a)';
$string['removeoption'] = 'Eliminar';
$string['minoptionswarning'] = 'Debes mantener al menos 2 opciones.';

// Estado del acceso
$string['conditionactive'] = 'Acceso activo';
$string['conditionactive_help'] = 'Controla solo si se pide la selección a los estudiantes (el modal). Los accesos nuevos nacen pausados: actívalo cuando termines de configurar. Al ACTIVARLO se vuelve a pedir la selección a TODOS (las selecciones previas se reinician). Al PAUSARLO no se borra nada; los estudiantes nuevos quedan sin valor. Esto NO apaga las restricciones que pongas en las actividades: esas las gestiona Moodle por actividad.';
$string['statusactive'] = 'Activa';
$string['statuspaused'] = 'Pausada';
$string['activationgate'] = 'Para activar necesitas un nombre y al menos 2 opciones (cada una con nombre y valor).';
$string['error_activate_requirements'] = 'para activar el acceso necesitas un nombre y al menos 2 opciones, cada una con nombre y valor técnico.';
$string['customid_locked'] = 'No se puede cambiar: el campo ya está en uso (hay selecciones o restricciones que lo usan). Cambiarlo rompería las restricciones existentes.';
$string['activation_reset_notice'] = 'Acceso activado. Se volverá a pedir la selección a TODOS los estudiantes; las selecciones previas se reiniciaron.';
$string['paused_with_restrictions'] = 'Guardado en pausa. {$a} actividad(es) restringen contenido con este campo: mientras esté pausado, los estudiantes que no eligieron (incluidos los nuevos) no podrán verlas.';
$string['saved_paused_notice'] = 'Guardado en pausa. Los estudiantes aún no verán nada. Actívalo cuando termines de configurar las restricciones.';
$string['nextsteps_paused'] = 'Este acceso está pausado. Configura las restricciones con el campo de abajo y luego actívalo para que los estudiantes elijan.';
$string['confirm_reactivate_title'] = 'Reactivar el acceso';
$string['confirm_reactivate_body'] = 'Esto volverá a pedir la selección a todos los estudiantes y reiniciará las selecciones actuales. ¿Continuar?';
$string['confirm_reactivate_yes'] = 'Sí, reactivar';
$string['error_minoptions'] = 'El acceso "{$a}" necesita al menos 2 opciones.';
$string['error_optionfields'] = 'Cada opción debe tener un nombre visible y un valor técnico.';

// Opciones avanzadas (disclosure)
$string['advancedoptions'] = 'Opciones avanzadas';

// Permitir que los estudiantes cambien su elección
$string['allowchange'] = 'Permitir que los estudiantes cambien su elección';
$string['allowchange_help'] = 'Por defecto, una vez que el estudiante elige no puede cambiar su opción (y tampoco puede editarla desde su perfil). Si activas esto, aparecerá un enlace "Cambiar selección" en el curso para que pueda volver a elegir. Cambiar la opción reevalúa su acceso al contenido restringido de inmediato.';
$string['changenotallowed'] = 'El profesor no permite cambiar tu elección en este curso.';
$string['confirm_change_title'] = 'Cambiar tu elección';
$string['confirm_change_body'] = 'Cambiar tu elección modificará al instante a qué contenido del curso puedes acceder. ¿Deseas continuar?';
$string['confirm_change_yes'] = 'Sí, cambiar';

// Panel "¿Qué sigue?" (post-guardado)
$string['nextsteps_heading'] = '¿Qué sigue? Mostrar contenido por opción';
$string['nextsteps_intro'] = 'Tu acceso ya está activa. Para mostrar actividades distintas a cada grupo, edita una actividad y agrega una restricción de acceso usando el campo de perfil indicado abajo.';
$string['nextsteps_field'] = 'Campo de perfil a usar';
$string['nextsteps_step1'] = 'Edita una actividad o recurso del curso y abre la sección "Restringir acceso".';
$string['nextsteps_step2'] = 'Pulsa "Añadir restricción" y elige "Perfil de usuario".';
$string['nextsteps_step3'] = 'Selecciona el campo "{$a}", la condición "es igual a", y escribe el valor técnico de la opción que debe ver esa actividad.';
$string['nextsteps_valuestable'] = 'Valores de tus opciones (cópialos en las restricciones):';
