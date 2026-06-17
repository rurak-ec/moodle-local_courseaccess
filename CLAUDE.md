# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Repository Layout

This repo follows the rurak-ec Moodle-plugin workspace convention (same as `moodle-video_embed`):

- `workspace/local_course_conditions/` — the actual Moodle plugin source (edit here).
- `scripts/` — `package_workspace.sh` (build installable ZIP) and `verify_isolation.sh` (structure check).
- `docs/` — `PLUGIN_MAP.md` and `WORKFLOW.md`.
- `build/` — generated ZIP artifacts (git-ignored, not plugin source).

All plugin paths below are relative to `workspace/local_course_conditions/`. When deployed into a Moodle site, this directory lives at `{moodleroot}/local/course_conditions/`.

## Project Overview

This is a Moodle local plugin (`local_course_conditions`) that allows teachers to define course-specific conditions (like "Turno", "Grupo de Laboratorio", "Modalidad") and requires students to select an option before accessing course content. The plugin injects a blocking modal on first course view and saves selections to custom user profile fields for use with Moodle's native access restrictions.

**Plugin Details:**
- Package: `local_course_conditions`
- Type: Moodle local plugin
- Target Moodle Version: 5.1+ (requires 2024042200)
- Current Version: v1.7.21 (2025012524)
- Maturity: MATURITY_STABLE
- Architecture: Course-specific conditions (v1.7.0+ refactor)

## Core Architecture

### Key Concept: Modal-Driven Selection

Unlike traditional form-based approaches, this plugin uses **JavaScript modal injection** triggered by Moodle events. When a student views a course without completing the condition selection, the observer injects an AMD JavaScript module that displays a blocking modal, preventing access until selection is complete.

### Data Flow

1. **Teacher Configuration** ([configure.php](workspace/local_course_conditions/configure.php)):
   - Teachers access configuration via course navigation node
   - Each course can have **one condition** with multiple options
   - Condition saved to `local_course_conditions_conditions` table
   - Options saved to `local_course_conditions_options` table
   - Profile field automatically created: `cond_{courseid}_{conditionid}`

2. **Student Selection** (Event-driven modal injection):
   - Observer detects `course_viewed` event ([classes/observer.php](workspace/local_course_conditions/classes/observer.php))
   - Checks if student has completed selection via `local_course_conditions_user_has_completed_all()`
   - If incomplete, injects AMD module `local_course_conditions/condition_modal`
   - Modal displays condition options, blocks page interaction
   - AJAX call to [ajax.php](workspace/local_course_conditions/ajax.php) saves selection
   - Selection saved to both:
     - `local_course_conditions_selections` table (history)
     - User profile field (for Moodle access restrictions)
   - Page auto-reloads, modal no longer appears

3. **Access Control** (Moodle native):
   - Teachers use "Restrict access" → "User profile" condition
   - Select the auto-created profile field `[CourseShortname]: [ConditionName]`
   - Set condition: "must be equal to" → option value (e.g., `turno_manana`)
   - Activities/resources now visible only to students who selected that option

### Database Schema

Four main tables defined in [db/install.xml](workspace/local_course_conditions/db/install.xml):

- **`local_course_conditions_conditions`**: Course conditions
  - Fields: `id`, `courseid`, `name`, `description`, `sortorder`, `timecreated`, `timemodified`
  - Key constraint: One condition per course (enforced via UI, not DB)

- **`local_course_conditions_options`**: Options for each condition
  - Fields: `id`, `conditionid`, `name`, `value`, `sortorder`, `timecreated`, `timemodified`
  - `name`: Display text (e.g., "Mañana")
  - `value`: Technical value for profile field (e.g., `turno_manana`)

- **`local_course_conditions_selections`**: User selections
  - Fields: `id`, `userid`, `courseid`, `conditionid`, `optionid`, `timecreated`, `timemodified`
  - Unique index on `userid, conditionid`
  - Used for history tracking and reporting

- **`local_course_conditions_history`**: Change audit trail
  - Fields: `id`, `userid`, `courseid`, `conditionid`, `old_optionid`, `new_optionid`, `reason`, `changed_by`, `timecreated`
  - Currently defined but not actively used

> Note: the table names exceed Moodle's 28-character guideline (e.g. `local_course_conditions_conditions` is 34 chars). They work on engines that already tolerate the previous 33-char names, but would warn on a moodle.org submission.

### Event Observers

Defined in [db/events.php](workspace/local_course_conditions/db/events.php), implemented in [classes/observer.php](workspace/local_course_conditions/classes/observer.php):

- **`\core\event\course_viewed`**: Primary trigger
  - Skips: Site admins, users with `local/course_conditions:configure` capability
  - Checks: `local_course_conditions_user_has_completed_all()` against profile field
  - Action: Injects modal via `local_course_conditions_inject_modal()`

- **`\core\event\user_enrolment_created`**: Placeholder
  - Currently empty (reserved for future features like email notifications)

### Key Library Functions

All defined in [lib.php](workspace/local_course_conditions/lib.php):

**Condition Management:**
- `local_course_conditions_get_conditions_for_course($courseid)`: Get all conditions with nested options
- `local_course_conditions_save_conditions($courseid, $conditions_data)`: Transactional save with validation

**Profile Field Management:**
- `local_course_conditions_create_profile_field($courseid, $conditionid, $conditionname)`: Creates/updates Moodle custom profile field
  - Shortname format: `cond_{courseid}_{conditionid}`
  - Name format: `{CourseShortname}: {ConditionName}`
  - Category: "Course Conditions"
- `local_course_conditions_save_to_profile($userid, $courseid, $conditionid, $value)`: Updates user's profile field value

**Selection Functions:**
- `local_course_conditions_save_selection($userid, $courseid, $conditionid, $optionid)`: Saves to both selections table and profile field
- `local_course_conditions_user_has_completed_all($userid, $courseid)`: Checks if all conditions completed (queries profile field directly)

**Modal Injection:**
- `local_course_conditions_inject_modal($courseid, $condition)`: Prepares data and calls AMD module via `$PAGE->requires->js_call_amd()`

**Navigation:**
- `local_course_conditions_extend_navigation_course($navigation, $course, $context)`: Adds configuration link to course navigation

### Capabilities

Defined in [db/access.php](workspace/local_course_conditions/db/access.php):

- **`local/course_conditions:manageconditionals`**: System-level management (managers only)
- **`local/course_conditions:configure`**: Course-level configuration (teachers, managers)
- **`local/course_conditions:viewreports`**: View selection reports (teachers, managers)

### JavaScript Architecture

The plugin uses Moodle's AMD (Asynchronous Module Definition) system:

- **Module**: `local_course_conditions/condition_modal` ([amd/src/condition_modal.js](workspace/local_course_conditions/amd/src/condition_modal.js))
- **Injection**: Via `$PAGE->requires->js_call_amd('local_course_conditions/condition_modal', 'init', [$data])`
- **Data structure**: JSON object with `courseid`, `condition` (id, name, description), and `options` array
- **Modal behavior**: Blocks all interaction, sends AJAX to [ajax.php](workspace/local_course_conditions/ajax.php), reloads on success

## Development Commands

### Verify repository structure

```bash
./scripts/verify_isolation.sh
```

Checks that `workspace/`, `scripts/`, `docs/` exist and that `workspace/local_course_conditions/version.php` is present.

### Package Plugin for Distribution

```bash
./scripts/package_workspace.sh
```

- Stages `workspace/local_course_conditions/` into a temp dir as `course_conditions/` (the bare plugin name Moodle expects inside `local/`).
- Produces a timestamped ZIP: `build/course_conditions_YYYYMMDD_HHMMSS.zip`.
- Excludes `.DS_Store`, `__MACOSX`, and `.git` internals.
- Does **not** bump the version (unlike the old `crear-zip.sh`). Bump `$plugin->version`/`$plugin->release` in `version.php` manually when you publish a release that Moodle must detect as an update.

### Moodle Development Environment

- Edit the plugin under `workspace/local_course_conditions/`.
- Deploy by copying that directory to `{moodleroot}/local/course_conditions/` (or by installing the ZIP from `build/`).
- The plugin requires Moodle core at `../../config.php` relative to the deployed location.
- Testing requires an active Moodle site with enrolled students and created courses.

To test modal injection:
1. Configure a condition as teacher
2. Enroll a test student
3. Log in as student and view course
4. Modal should appear blocking access

## Moodle Coding Standards

This plugin follows Moodle coding standards:
- GPL v3 license headers on all PHP files
- `defined('MOODLE_INTERNAL') || die();` on all includeable files
- Namespaced classes under `local_course_conditions`
- String identifiers in language files (en/es)
- Database operations use `$DB` global
- Page setup using `$PAGE` global
- Capability checks using `require_capability()` and `has_capability()`
- Event observers use namespaced event classes
- JavaScript uses AMD modules in `amd/src/` directory

## Custom Profile Fields

The plugin automatically manages Moodle custom user profile fields:

**Automatic Creation:**
- Triggered when condition is saved via `local_course_conditions_create_profile_field()`
- Category: "Course Conditions" (auto-created if needed)
- Field shortname: `cond_{courseid}_{conditionid}` (stable identifier)
- Field name: `{CourseShortname}: {ConditionName}` (human-readable)

**Field Properties:**
- Type: Text field
- Locked: Yes (only plugin can modify)
- Visible: To user and teachers
- Required: No (controlled by plugin logic instead)

**Integration with Moodle:**
- These fields appear in "Restrict access" UI for activities/resources
- Teachers select the field and set conditions like "must be equal to turno_manana"
- Moodle's core access system handles visibility automatically
- Values in profile fields are what Moodle actually checks, not the selections table

## Important Architectural Notes

1. **Profile Fields are Source of Truth**: The `local_course_conditions_user_has_completed_all()` function checks profile fields directly, not the selections table. This ensures the modal logic matches Moodle's access restriction logic.

2. **Single Condition Enforcement**: While the database schema supports multiple conditions per course, the current UI (v1.7.0+) enforces one condition per course. The `configure.php` page only manages a single condition.

3. **Transactional Saves**: `local_course_conditions_save_conditions()` uses `$DB->start_delegated_transaction()` to ensure atomicity when saving conditions and options.

4. **AJAX Security**: [ajax.php](workspace/local_course_conditions/ajax.php) validates:
   - User is logged in and enrolled in course
   - Session key is valid
   - Condition belongs to specified course
   - Option belongs to specified condition

5. **Observer Skips**: The modal is never shown to:
   - Site administrators
   - Users with `local/course_conditions:configure` capability
   - Users who have already completed selection (profile field has value)
