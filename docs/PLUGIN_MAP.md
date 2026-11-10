# Mapa técnico del plugin

Ruta fuente:
- `workspace/local_courseaccess`

Componente Moodle: `local_courseaccess` (se despliega en `local/courseaccess`).

Puntos clave:

- `version.php`: versión/componente del plugin (`$plugin->component = 'local_courseaccess'`).
- `db/install.xml`: esquema (tablas `local_courseaccess_{cond,options,sel,history}`); `db/upgrade.php`: los pasos que
  llevan una instalación anterior a ese esquema.
- `db/services.php`: la función externa `local_courseaccess_save_selection` (AJAX).
- `db/access.php`: capacidades (`local/courseaccess:{configure,manageconditionals,viewreports}`).
- `db/hooks.php` y `classes/hook_callbacks.php`: el hook de salida que muestra la ventana en las páginas del curso.
- `lib.php`: funciones principales (`local_courseaccess_*`), inyección del modal e integración con campos de perfil.
- `classes/selection.php`: lo que eligió cada estudiante, una opción (lista) o varias (casillas). `selection::set()` es
  el único lugar que valida y guarda una elección (filas, historial y campo de perfil).
- `classes/restrictions.php`: cuenta las restricciones del curso que leen el campo de perfil, por operador.
- `classes/external/save_selection.php`: guarda la primera elección desde la ventana.
- `classes/form/selection_form.php`: el formulario de `select.php` y `change_selection.php` (lista o casillas).
- `classes/privacy/provider.php`: cumplimiento de la Privacy API.
- `amd/src/condition_modal.js`: módulo AMD del modal (`local_courseaccess/condition_modal`); `amd/src/repository.js`:
  las llamadas a la función externa (`core/ajax`). El build vive en `amd/build/`.
- `configure.php`: formulario de configuración del profesor (nombre, tipo de selección, máximo y opciones).
- `lang/en/local_courseaccess.php`: textos de la UI (el español, en `translations/es`).

Tipos de condición:
- **Lista:** una opción. El perfil guarda su valor (`ENF`) y se restringe con «es igual a».
- **Casillas:** una o más, hasta el máximo de la condición (0 = sin tope). El perfil guarda `|ENF|MED|` y se restringe
  con «contiene» `|ENF|`. El tipo queda fijo cuando ya hay estudiantes que eligieron.

Regla de diseño:
- Editar únicamente bajo `workspace/local_courseaccess`.
- `build/` contiene artefactos generados (no es fuente).
