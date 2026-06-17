# Mapa técnico del plugin

Ruta fuente:
- `workspace/local_course_conditions`

Componente Moodle: `local_course_conditions` (se despliega en `local/course_conditions`).

Puntos clave:

- `version.php`: versión/componente del plugin (`$plugin->component = 'local_course_conditions'`).
- `db/install.xml`: esquema (tablas `local_course_conditions_{conditions,options,selections,history}`).
- `db/access.php`: capacidades (`local/course_conditions:{configure,manageconditionals,viewreports}`).
- `db/events.php`: observadores de eventos (`course_viewed`, `user_enrolment_created`).
- `lib.php`: funciones principales (`local_course_conditions_*`), inyección del modal e integración con campos de perfil.
- `classes/observer.php`: lógica del observador que dispara el modal.
- `classes/privacy/provider.php`: cumplimiento de la Privacy API.
- `amd/src/condition_modal.js`: módulo AMD del modal (`local_course_conditions/condition_modal`). El build vive en `amd/build/`.
- `configure.php`: formulario de configuración del profesor.
- `ajax.php`: endpoint que guarda la selección del estudiante.
- `lang/en|es/local_course_conditions.php`: textos de la UI.

Regla de diseño:
- Editar únicamente bajo `workspace/local_course_conditions`.
- `build/` contiene artefactos generados (no es fuente).
