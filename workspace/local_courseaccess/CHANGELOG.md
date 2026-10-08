# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.4.9] - 2026-10-08

### Fixed
- **Fixed selection change failure**: Resolved `dml_write_exception` in `change_selection.php` where `$history->courseid` was missing, violating the not-null database constraint on `mdl_local_courseaccess_history`. Encapsulated logic in `local_courseaccess_change_user_selection()`.
- Guaranteed form delivery: Form action in `change_selection.php` now has an explicit Moodle URL with `courseid` and includes a hidden `courseid` input.
- Self-healing profile fields: `local_courseaccess_save_to_profile` automatically re-creates any missing custom profile field dynamically.

### Added
- Declared full support for **Moodle 5.3 (LTS)** (`$plugin->supported = [401, 503]`).
- CI matrix covering `MOODLE_503_STABLE` on PHP 8.3 (pgsql) and PHP 8.4 (mariadb).
- High-performance CSS containment (`contain: layout;`, `contain: layout paint;`) and `touch-action: manipulation;` on modals and interactive buttons.

### Changed & Optimized
- **Zero-impact Course Page Performance**:
  - Added in-request runtime cache (`local_courseaccess_runtime_cache`) eliminating all duplicate queries during course view and navigation extension.
  - Replaced $N$ queries with a single batch `JOIN`/`get_in_or_equal` query in `local_courseaccess_get_conditions_for_course()`.
  - Fast-path completion check in `local_courseaccess_user_has_completed_all()` using `$USER->profile` already resident in memory (0 SQL queries on repeated course views).

## [2.4.0] - 2026-06-21 — Moodle 4.5 LTS support

### Changed
- **Declared support for Moodle 4.5 LTS** (`$plugin->supported = [405, 502]`, `requires = 2024100700`).
  Verified against `MOODLE_405_STABLE`: the output hook
  `\core\hook\output\before_standard_top_of_body_html_generation` (4.4+), the `db/hooks.php` registration,
  and every AMD core module used (`core/modal`, `core/notification`, `core/templates`, `core/str`,
  `core/config`) all exist in 4.5. CI now also runs `MOODLE_405_STABLE` on PHP 8.1 and 8.3.
- Made the "what's next" field badge margin work on Bootstrap 4 too (`ms-1 ml-1`) so it renders correctly
  on Moodle 4.5 (BS4) and 5.x (BS5).

## [2.3.0] - 2026-06-20 — Component renamed to `local_courseaccess`

### Changed
- **Frankenstyle component renamed** `local_course_access` → **`local_courseaccess`** (no underscore in
  the plugin-name part, per the moodle.org directory convention). This renames the install path
  (`local/courseaccess`), the namespace, capabilities (`local/courseaccess:*`), AMD module ids
  (`local_courseaccess/*`), the language file, and the database tables
  (`local_courseaccess_{cond,options,sel,history}`).
- The source repository was renamed accordingly (`moodle-course_access` → `moodle-local_courseaccess`).
- `db/upgrade.php` reset to a clean baseline (the schema is defined in `db/install.xml`; the legacy
  table-rename steps belonged to the previous component name and no longer apply).

> **Upgrade note:** because the frankenstyle component changed, Moodle treats this as a new plugin.
> Sites running the old `local_course_access` must uninstall it before installing `local_courseaccess`.

## [2.2.0] - 2026-06-20

Release prepared for the Moodle plugins directory (Moodle 5.1–5.2; CI also tracks 5.3-dev).

### Fixed
- **Privacy provider rewritten.** It previously queried a non-existent table
  (`local_courseaccess_user`); it now correctly describes, exports and deletes the data in the
  selections and history tables.
- **Removed all hardcoded Spanish from the code** (the student modal, the profile-field category and
  the configure error messages now use language strings). The student modal is rendered from a
  Mustache template, so option/condition text is escaped (fixes a potential XSS).
- Removed a broken admin link to a non-existent `manage_conditionals.php` page.

### Changed
- **Renamed two tables to fit Moodle's 28-char limit**: `local_courseaccess_conditions` →
  `local_courseaccess_cond` and `local_courseaccess_selections` → `local_courseaccess_sel`
  (with an upgrade step that renames existing tables, preserving data).
- **JavaScript rewritten as ES6 modules** built with grunt/rollup (no jQuery, no inline CSS, no
  deprecated `Notification.alert`); the student gate is a template-rendered blocking overlay.
- `version.php`: declared `$plugin->supported = [501, 502]`, completed the copyright.

### Added
- GitHub Actions CI (`moodle-plugin-ci`) across PHP 8.2–8.4 × Moodle 5.1/5.2 (PostgreSQL + MariaDB)
  plus a non-blocking `main` (5.3-dev) run.
- PHPUnit tests for the library and the privacy provider, and a Behat feature for the selection gate.
- English-only language pack (the Spanish translation moved to `/translations` for AMOS).

## [1.7.16] - 2025-01-25

### Added
- **Change Selection Feature**: Students can now change their selection after the initial choice
  - New page `change_selection.php` for voluntary selection changes
  - Navigation link "Cambiar [CondiciónNombre]" appears for students who have already selected
  - Warning message about impact on access restrictions
  - History tracking for all selection changes in `local_courseaccess_history` table
- **Helper Functions** in `lib.php`:
  - `local_courseaccess_get_options_for_condition()`: Get options for a specific condition
  - `local_courseaccess_get_user_selection()`: Get user's current selection with JOIN
- **Language Strings**:
  - `changeselection_nav`: Dynamic navigation text with condition name
  - Enhanced `changeselectionwarning` with emoji and detailed explanation

### Changed
- **Navigation System**: Refactored `local_courseaccess_extend_navigation_course()` 
  - Teachers see: "Configure Course Conditions" (settings icon)
  - Students see: "Cambiar [CondiciónNombre]" (edit icon) - only if selection exists
- **Plugin Name**: Changed from "Condiciones del curso" to "Condiciones de Curso"
- **Profile Field Category**: Updated category name to "Condiciones de Curso"
- **Profile Field Name**: Simplified to show only course shortname (e.g., "MATE101")
  - Removed condition name from field display name
  - Now: `{CourseShortname}` instead of `{CourseShortname}: {ConditionName}`

### Fixed
- **Form Processing Bug**: Fixed `change_selection.php` processing order
  - Moved form submission handling BEFORE any HTML output
  - Prevents "Invalid state passed to moodle_page::set_state" error
  - Prevents "You cannot redirect after the entire page has been generated" error

### Documentation
- **README.md**: Added comprehensive documentation
  - Front-end flow explanation for teachers and students
  - Back-end technical architecture with code examples
  - Change selection feature documentation
  - Security architecture section
- **CONTRIBUTING.md**: Created contribution guidelines
- **CHANGELOG.md**: This file
- **.gitignore**: Added for clean repository
- **LICENSE**: GPL-3.0 license file

## [1.7.0] - 2025-01-25

### Changed
- **Architecture Refactor**: Complete refactor to course-specific conditions
  - Removed global conditions system
  - Each course can have exactly one condition
  - Simplified data model and user experience

### Added
- **Dynamic Configuration Form**: JavaScript-based form in `configure.php`
  - Add/remove options dynamically
  - Minimum 2 options validation
  - Real-time field validation
- **Blocking Modal**: Automatic modal injection for first-time students
  - Event-driven via `course_viewed` observer
  - AJAX submission without page reload
  - Cannot be closed until selection is made
- **Custom Profile Fields**: Automatic creation for access restrictions
  - Format: `cond_{courseid}_{conditionid}`
  - Locked fields (students cannot edit manually)
  - Integrated with Moodle's native access restrictions
- **Database Schema**: Four main tables
  - `local_courseaccess_conditions`: Course conditions
  - `local_courseaccess_options`: Condition options
  - `local_courseaccess_selections`: User selections
  - `local_courseaccess_history`: Selection change history

### Technical
- **Auto-versioning**: `crear-zip.sh` script automatically increments versions
  - Increments both release version (v1.7.x) and build number
  - Forces Moodle to detect updates on every installation
- **AMD JavaScript**: Proper AMD module structure for modal
- **Transactional Saves**: All database operations use transactions with rollback
- **CSRF Protection**: All forms protected with sesskey validation

## [1.0.0] - Initial Release

### Added
- Initial plugin structure
- Basic condition management
- Global conditions system (deprecated in 1.7.0)

---

## Version Format

- **Major.Minor.Patch** (e.g., 1.7.16)
- **Build Number**: Internal Moodle version (e.g., 2025012519)

## Links

- [Moodle Plugin Directory](https://moodle.org/plugins/)
- [GitHub Repository](https://github.com/yourusername/moodle-local_courseaccess)
- [Issue Tracker](https://github.com/yourusername/moodle-local_courseaccess/issues)
