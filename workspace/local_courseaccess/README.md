# Condiciones del Curso — Plugin local de Moodle

[![Moodle](https://img.shields.io/badge/Moodle-4.5%20to%205.3%20LTS-orange.svg)](https://moodle.org/)
[![PHP](https://img.shields.io/badge/PHP-8.1+-blue.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/License-GPLv3-green.svg)](https://www.gnu.org/licenses/gpl-3.0.html)
[![Status](https://img.shields.io/badge/Status-Stable-green.svg)]()

Plugin local de Moodle que obliga a los estudiantes a elegir una opción (turno, grupo, modalidad, etc.) antes de acceder al contenido de un curso, y usa esa elección para alimentar las restricciones de acceso nativas de Moodle.

**Componente:** `local_courseaccess`
**Versión:** v2.4.9 (build 2026100801)
**Licencia:** GNU GPL v3 or later

---

## Tabla de contenidos

- [Qué hace](#qué-hace)
- [Instalación](#instalación)
- [Configuración (profesor)](#configuración-profesor)
- [Experiencia del estudiante](#experiencia-del-estudiante)
- [Configuración avanzada](#configuración-avanzada)
- [Arquitectura técnica](#arquitectura-técnica)
- [Privacidad y GDPR](#privacidad-y-gdpr)

---

## Qué hace

### El problema que resuelve

En cursos con varios grupos o turnos es habitual querer mostrar actividades distintas a cada subgrupo. Moodle tiene restricciones de acceso nativas basadas en campos de perfil de usuario, pero alguien tiene que poblar esos campos. Este plugin se encarga de eso: le pregunta al estudiante cuál es su grupo la primera vez que entra al curso y guarda la respuesta en un campo de perfil personalizado que el profesor ya puede usar en las restricciones.

### Cómo funciona de extremo a extremo

1. **El profesor configura una condición** en el curso (ej. "Turno") con sus opciones (ej. "Mañana" / "Tarde"). El plugin crea automáticamente un campo de perfil de usuario personalizado vinculado a esa condición.

2. **El estudiante entra al curso por primera vez.** Un hook de salida de Moodle (`before_standard_top_of_body_html_generation`) detecta, en las páginas de curso, que el campo de perfil está vacío y lanza un modal JavaScript bloqueante. El estudiante no puede cerrar el modal ni interactuar con el curso hasta que elige una opción.

3. **El estudiante elige.** Una llamada AJAX guarda la selección en dos lugares: la tabla interna del plugin (`local_courseaccess_sel`) y el campo de perfil de usuario de Moodle. La página se recarga y el modal no vuelve a aparecer.

4. **Las restricciones de Moodle funcionan solas.** Como el campo de perfil ahora tiene valor, cualquier actividad configurada con "Restringir acceso → Perfil de usuario → debe ser igual a turno_manana" se muestra u oculta automáticamente sin intervención adicional del plugin.

5. **El estudiante puede cambiar su selección** desde un enlace en la navegación del curso. El cambio actualiza la tabla de selecciones, el campo de perfil y deja un registro en la tabla de historial. Las restricciones de acceso se aplican inmediatamente con el nuevo valor.

---

## Instalación

**Requisitos:** Moodle 4.5+, PHP 8.1+

### Despliegue en moodle-dev (entorno de desarrollo)

El plugin está montado como bind-mount en el entorno de desarrollo; cualquier cambio en `workspace/local_courseaccess/` aplica al siguiente request sin necesidad de reconstruir la imagen.

Si se modifican tablas (`db/install.xml` o `db/upgrade.php`), ejecutar:

```bash
docker exec -u www-data moodle-moodle-dev php admin/cli/upgrade.php --non-interactive
```

### Empaquetado para instalación en otro sitio

```bash
cd /ruta/al/repo
./scripts/package_workspace.sh
```

Genera un ZIP timestamped en `build/courseaccess_YYYYMMDD_HHMMSS.zip`. Ese ZIP se instala desde Administración del sitio → Plugins → Instalar plugins.

Antes de generar una release que Moodle deba detectar como actualización, incrementar manualmente `$plugin->version` y `$plugin->release` en `version.php`.

### Verificar estructura del repositorio

```bash
./scripts/verify_isolation.sh
```

---

## Configuración (profesor)

### 1. Acceder a la configuración del curso

Dentro del curso, en la barra de navegación aparece el enlace **"Configure Course Conditions"**. Solo lo ven usuarios con el permiso `local/courseaccess:configure` (profesores editores y administradores).

### 2. Definir la condición y sus opciones

El formulario permite configurar una condición por curso:

| Campo | Descripción |
|---|---|
| Nombre | Lo que verá el estudiante en el modal (ej. "Turno") |
| Descripción | Texto opcional explicativo en el modal |
| Opciones | Mínimo 2. Cada opción tiene un nombre visible y un valor técnico |

El **valor técnico** de cada opción es el string que Moodle comparará en las restricciones de acceso. Debe ser único dentro de la condición y sin espacios (ej. `turno_manana`, `grupo_a`).

Al guardar, el plugin crea automáticamente un campo de perfil de usuario:

- **Shortname:** `cond_{custom_id}_{conditionid}` (por defecto `custom_id` = courseid, ej. `cond_5_1`)
- **Nombre visible:** el nombre corto del curso (ej. `MATE101`)
- **Categoría:** "Accesos del curso"
- **Tipo:** texto, bloqueado (los estudiantes no pueden editarlo manualmente)

### 3. Aplicar restricciones a actividades

Una vez que los estudiantes han elegido, cualquier actividad o recurso puede restringirse por grupo:

1. Editar la actividad → sección "Restringir acceso"
2. Añadir restricción → "Perfil de usuario"
3. Seleccionar el campo creado (aparece como `MATE101: Turno`)
4. Condición: "debe ser igual a" → valor técnico (ej. `turno_manana`)

Solo los estudiantes cuyo campo de perfil tenga ese valor verán la actividad. Moodle gestiona la visibilidad de forma completamente nativa; el plugin no interviene en este paso.

---

## Experiencia del estudiante

### Primera vez en el curso

1. El estudiante abre el curso.
2. Aparece un modal de pantalla completa con fondo oscuro bloqueante (z-index 9999). No se puede cerrar con ESC ni clic fuera.
3. El modal muestra el nombre y la descripción de la condición, y un dropdown con las opciones.
4. El estudiante elige y hace clic en "Guardar".
5. Una llamada AJAX guarda la selección. Al terminar, la página se recarga.
6. El modal no vuelve a aparecer. Las restricciones de acceso se aplican con el valor elegido.

### Visitas posteriores

El hook verifica el campo de perfil en cada carga de página de curso. Si el campo tiene valor, no pasa nada — el estudiante accede directamente.

### Cambiar la selección

Si el estudiante necesita cambiar su grupo o turno:

1. En la navegación del curso aparece "Cambiar [NombreCondición]" (solo si ya hizo una selección previa).
2. La página de cambio (no es un modal bloqueante) muestra la selección actual y un aviso de que el cambio afecta las actividades visibles.
3. El estudiante elige la nueva opción y guarda.
4. El plugin actualiza la tabla de selecciones, el campo de perfil y registra el cambio en la tabla de historial con la opción anterior y la nueva.
5. Las restricciones de acceso aplican inmediatamente.

---

## Configuración avanzada

Estas opciones están en la sección "Configuración Avanzada" del formulario del profesor.

### ID Personalizado

Por defecto el shortname del campo de perfil usa el ID del curso: `cond_{custom_id}_{conditionid}` con `custom_id` = courseid. El ID personalizado permite reemplazar el `courseid` por un identificador semántico propio.

**Caso de uso:** compartir un campo de perfil entre varios cursos del mismo programa (ej. `cond_quimica_1` en lugar de `cond_47_1` y `cond_52_1`). Si un estudiante ya eligió en un curso, no verá el modal en los otros que usen el mismo ID.

**Advertencia:** cambiar el ID personalizado después de que los estudiantes ya hicieron selecciones dispara una migración automática que actualiza todos los campos de perfil existentes. Puede tardar varios minutos con muchos estudiantes.

### Habilitar / Deshabilitar

El checkbox "Habilitar Condición" permite desactivar temporalmente la condición sin borrar datos:

- **Deshabilitada:** el hook no inyecta el modal y las restricciones de acceso del plugin no aplican. Los datos de selección se conservan.
- **Reactivada:** los estudiantes recuperan sus selecciones automáticamente; no necesitan volver a elegir.

---

## Arquitectura técnica

### Tablas de base de datos

| Tabla | Contenido |
|---|---|
| `mdl_local_courseaccess_cond` | Una condición por curso. Campos: `id`, `courseid`, `name`, `description`, `custom_id`, `enabled`, `sortorder`, `timecreated`, `timemodified` |
| `mdl_local_courseaccess_options` | Opciones de cada condición. Campos: `id`, `conditionid`, `name`, `value`, `sortorder` |
| `mdl_local_courseaccess_sel` | Selección actual de cada estudiante. Índice único en `(userid, conditionid)` |
| `mdl_local_courseaccess_history` | Historial de cambios. Registra `old_optionid`, `new_optionid`, `changed_by` y timestamp por cada modificación |

> Las tablas respetan el límite de 28 caracteres recomendado por Moodle (ej. `local_courseaccess_cond` tiene 23; la más larga, `local_courseaccess_options`, tiene 26), por lo que pasan la verificación de una publicación en moodle.org.

### Flujo de inyección del modal

```
Render de página de curso (course-view-*)
    └── hook before_standard_top_of_body_html_generation
            └── hook_callbacks::inject_condition_modal()
                    ├── ¿pagetype course-view-* y curso real? → si no, salir
                    └── local_courseaccess_get_pending_condition()
                            ├── is_siteadmin()? → salir
                            ├── has_capability('configure')? → salir
                            ├── campo de perfil con valor? → salir
                            ├── sin condición / condición deshabilitada? → salir
                            └── devuelve la condición pendiente
                    └── local_courseaccess_inject_modal()
                            └── $PAGE->requires->js_call_amd('local_courseaccess/condition_modal', 'init', [$data])
                                    └── Modal JS → AJAX POST /local/courseaccess/ajax.php
                                            └── local_courseaccess_save_selection()
                                                    ├── INSERT/UPDATE mdl_local_courseaccess_sel
                                                    └── INSERT/UPDATE mdl_user_info_data  ← campo de perfil
```

> El modal se inyecta desde un **hook de salida**, no desde un event observer.
> Los observers de Moodle no deben depender de `$PAGE`/salida y `course_viewed`
> también se dispara en el servicio web del móvil, donde el modal no podría
> mostrarse. El hook solo corre en el render real de la página.

### Campos de perfil como fuente de verdad

La función `local_courseaccess_user_has_completed_all()` consulta directamente `mdl_user_info_data` (no la tabla de selecciones). Esto garantiza que la lógica del modal y las restricciones de acceso de Moodle estén sincronizadas: ambas leen el mismo dato.

### JavaScript (AMD)

El módulo `local_courseaccess/condition_modal` (`amd/src/condition_modal.js`) recibe un objeto JSON con la condición y sus opciones, renderiza el modal con estilos inline, envía la selección por AJAX y recarga la página al confirmar.

El archivo minificado (`amd/build/condition_modal.min.js`) debe mantenerse sincronizado con el fuente. En el entorno de desarrollo con bind-mount, el servidor PHP sirve el `.min.js` directamente desde el repositorio via `javascript.php`.

### Seguridad

- **CSRF:** todos los formularios y el endpoint AJAX validan `sesskey` con `confirm_sesskey()`.
- **Permisos:** `require_capability()` en todas las páginas PHP del plugin.
- **Limpieza de datos:** `clean_param()` con tipo específico en cada parámetro recibido.
- **Integridad transaccional:** `$DB->start_delegated_transaction()` con rollback en configuración, selección y cambio de selección.
- **Campos bloqueados:** `locked = 1` en los campos de perfil; los estudiantes no pueden editarlos desde su perfil.
- **Validación de relaciones en AJAX:** `ajax.php` verifica que `conditionid` pertenezca al `courseid` dado y que `optionid` pertenezca a ese `conditionid` antes de guardar.

### Capacidades

| Capability | Contexto | Asignada a |
|---|---|---|
| `local/courseaccess:manageconditionals` | Sistema | Administradores |
| `local/courseaccess:configure` | Curso | Profesores editores, administradores |
| `local/courseaccess:viewreports` | Curso | Profesores, administradores |

---

## Privacidad y GDPR

El plugin implementa la Privacy API de Moodle (`classes/privacy/provider.php`):

- **Datos almacenados:** selección actual del estudiante (tabla `selections`) e historial de cambios (tabla `history`), más el valor en el campo de perfil de usuario de Moodle core.
- **Exportación:** un usuario puede solicitar la exportación de sus datos; el plugin devuelve sus selecciones y el historial de cambios.
- **Eliminación:** al solicitar el borrado de un usuario se eliminan sus registros en `selections` e `history` y se limpia el valor en el campo de perfil. Al eliminar el contexto del curso se eliminan todos los registros de ese curso.
