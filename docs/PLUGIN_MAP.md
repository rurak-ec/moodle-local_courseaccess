# Mapa técnico del plugin

Ruta fuente:
- `workspace/local_courseaccess`

Componente Moodle: `local_courseaccess` (se despliega en `local/courseaccess`).

Puntos clave:

- `version.php`: versión/componente del plugin (`$plugin->component = 'local_courseaccess'`).
- `db/install.xml`: esquema (tablas `local_courseaccess_{cond,options,sel,history}`).
- `db/access.php`: capacidades (`local/courseaccess:{configure,manageconditionals,viewreports}`).
- `db/events.php`: observadores de eventos (`course_viewed`, `user_enrolment_created`).
- `lib.php`: funciones principales (`local_courseaccess_*`), inyección del modal e integración con campos de perfil.
- `classes/observer.php`: lógica del observador que dispara el modal.
- `classes/privacy/provider.php`: cumplimiento de la Privacy API.
- `amd/src/condition_modal.js`: módulo AMD del modal (`local_courseaccess/condition_modal`). El build vive en `amd/build/`.
- `configure.php`: formulario de configuración del profesor.
- `ajax.php`: endpoint que guarda la selección del estudiante.
- `lang/en|es/local_courseaccess.php`: textos de la UI.

Regla de diseño:
- Editar únicamente bajo `workspace/local_courseaccess`.
- `build/` contiene artefactos generados (no es fuente).
