# Course access (local_courseaccess)

A Moodle **local** plugin that gates a course behind a required choice: a teacher defines a course
*condition* (e.g. "Shift", "Lab group", "Modality") with options, and on first course view each
student must pick one before they can use the course. The choice is stored in an auto-managed custom
profile field, so teachers can then show different activities per group using Moodle's native
**Restrict access → User profile** conditions.

- **Component:** `local_courseaccess`
- **Supported Moodle:** 4.5 – 5.3 (LTS)
- **License:** GNU GPL v3 or later
- **Issues:** <https://github.com/rurak-ec/moodle-local_courseaccess/issues>

> Development repository. The installable plugin lives in
> [`workspace/local_courseaccess`](workspace/local_courseaccess). The plugin README has full
> details: [`workspace/local_courseaccess/README.md`](workspace/local_courseaccess/README.md).

---

## English

### What it does
- A teacher configures one condition per course (name + at least two options) and activates it.
- On first course view, a **blocking overlay** asks the student to choose an option. The choice is
  saved to a custom profile field and to the plugin's tables; the page reloads and the overlay is gone.
- Teachers restrict activities/resources by that profile field so each group sees the right content.
- Optional per-condition "allow students to change their selection" and an activate/pause lifecycle.

### Notes for administrators
- The modal is injected only on real course-view pages via an output hook (never from a web service).
- Privacy: the plugin stores each user's selection and a change history, fully covered by a Privacy
  API provider (export and delete are implemented).
- Ships **English only** (per the plugins-directory policy); the Spanish pack is kept under
  [`/translations`](translations/) for lang.moodle.org (AMOS) after approval.

### Installation
1. Build the ZIP: `./scripts/package_workspace.sh`
2. Install via **Site administration → Plugins → Install plugins**, or copy
   `workspace/local_courseaccess` to `local/courseaccess` and run the upgrade.

### Quality
CI runs `moodle-plugin-ci` (phpcs, phpdoc, validate, savepoints, mustache, grunt, PHPUnit, Behat)
across PHP 8.1–8.4 and Moodle 4.5/5.1/5.2 on PostgreSQL and MariaDB, plus a non-blocking `main` (5.3-dev) run.

---

## Español

### Qué hace
- El profesorado configura una condición por curso (nombre + al menos dos opciones) y la activa.
- En la primera visita al curso, un **overlay bloqueante** pide al estudiante elegir una opción. La
  elección se guarda en un campo de perfil personalizado y en las tablas del plugin; la página se
  recarga y el overlay desaparece.
- El profesorado restringe actividades/recursos por ese campo de perfil para mostrar el contenido
  adecuado a cada grupo.
- Opcionalmente, "permitir al estudiante cambiar su selección" por condición y un ciclo activar/pausar.

### Notas para administradores
- El modal se inyecta solo en páginas reales de curso mediante un *output hook* (nunca desde un
  servicio web).
- Privacidad: el plugin almacena la selección de cada usuario y un historial de cambios, cubiertos por
  un proveedor de la Privacy API (exportación y borrado implementados).
- Se publica **solo en inglés** (política del directorio); el español se conserva en
  [`/translations`](translations/) para lang.moodle.org (AMOS) tras la aprobación.

### Instalación
1. Generar el ZIP: `./scripts/package_workspace.sh`
2. Instalar desde **Administración del sitio → Plugins → Instalar plugins**, o copiar
   `workspace/local_courseaccess` a `local/courseaccess` y ejecutar la actualización.

## Licencia / License
GNU GPL v3 or later. See [LICENSE](LICENSE).
