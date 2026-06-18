# Mapa técnico del plugin

Ruta fuente:
- `workspace/local_course_access`

Componente Moodle: `local_course_access` (se despliega en `local/course_access`).

Puntos clave:

- `version.php`: versión/componente del plugin (`$plugin->component = 'local_course_access'`).
- `db/install.xml`: esquema (tablas `local_course_access_{conditions,options,selections,history}`).
- `db/access.php`: capacidades (`local/course_access:{configure,manageconditionals,viewreports}`).
- `db/events.php`: observadores de eventos (`course_viewed`, `user_enrolment_created`).
- `lib.php`: funciones principales (`local_course_access_*`), inyección del modal e integración con campos de perfil.
- `classes/observer.php`: lógica del observador que dispara el modal.
- `classes/privacy/provider.php`: cumplimiento de la Privacy API.
- `amd/src/condition_modal.js`: módulo AMD del modal (`local_course_access/condition_modal`). El build vive en `amd/build/`.
- `configure.php`: formulario de configuración del profesor.
- `ajax.php`: endpoint que guarda la selección del estudiante.
- `lang/en|es/local_course_access.php`: textos de la UI.

Regla de diseño:
- Editar únicamente bajo `workspace/local_course_access`.
- `build/` contiene artefactos generados (no es fuente).
