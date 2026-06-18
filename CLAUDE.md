# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Repository Layout

This repo follows the rurak-ec Moodle-plugin workspace convention (same as `moodle-video_embed`):

- `workspace/local_course_access/` — the actual Moodle plugin source (edit here).
- `scripts/` — `package_workspace.sh` (build installable ZIP) and `verify_isolation.sh` (structure check).
- `docs/` — `PLUGIN_MAP.md` and `WORKFLOW.md`.
- `build/` — generated ZIP artifacts (git-ignored, not plugin source).

All plugin paths below are relative to `workspace/local_course_access/`. When deployed into a Moodle site, this directory lives at `{moodleroot}/local/course_access/`.

## Project Overview

This is a Moodle local plugin (`local_course_access`) that allows teachers to define course-specific conditions (like "Turno", "Grupo de Laboratorio", "Modalidad") and requires students to select an option before accessing course content. The plugin injects a blocking modal on first course view and saves selections to custom user profile fields for use with Moodle's native access restrictions.

**Plugin Details:**
- Package: `local_course_access`
- Type: Moodle local plugin
- Target Moodle Version: 5.1+ (requires 2024042200)
- Current Version: v1.7.21 (2025012524)
- Maturity: MATURITY_STABLE
- Architecture: Course-specific conditions (v1.7.0+ refactor)

## Core Architecture

### Key Concept: Modal-Driven Selection

Unlike traditional form-based approaches, this plugin uses **JavaScript modal injection** on course-view pages. When a student views a course without completing the condition selection, an output hook injects an AMD JavaScript module that displays a blocking modal, preventing access until selection is complete.

### Data Flow

1. **Teacher Configuration** ([configure.php](workspace/local_course_access/configure.php)):
   - Teachers access configuration via course navigation node
   - Each course can have **one condition** with multiple options
   - Condition saved to `local_course_access_conditions` table
   - Options saved to `local_course_access_options` table
   - Profile field automatically created: `acc_{custom_id}_{conditionid}` (custom_id defaults to courseid)

2. **Student Selection** (hook-driven modal injection):
   - Output hook fires on course-view pages ([classes/hook_callbacks.php](workspace/local_course_access/classes/hook_callbacks.php))
   - Checks if student has completed selection via `local_course_access_user_has_completed_all()`
   - If incomplete, injects AMD module `local_course_access/condition_modal`
   - Modal displays condition options, blocks page interaction
   - AJAX call to [ajax.php](workspace/local_course_access/ajax.php) saves selection
   - Selection saved to both:
     - `local_course_access_selections` table (history)
     - User profile field (for Moodle access restrictions)
   - Page auto-reloads, modal no longer appears

3. **Access Control** (Moodle native):
   - Teachers use "Restrict access" → "User profile" condition
   - Select the auto-created profile field `[CourseShortname]: [ConditionName]`
   - Set condition: "must be equal to" → option value (e.g., `turno_manana`)
   - Activities/resources now visible only to students who selected that option

### Database Schema

Four main tables defined in [db/install.xml](workspace/local_course_access/db/install.xml):

- **`local_course_access_conditions`**: Course conditions
  - Fields: `id`, `courseid`, `name`, `description`, `sortorder`, `timecreated`, `timemodified`
  - Key constraint: One condition per course (enforced via UI, not DB)

- **`local_course_access_options`**: Options for each condition
  - Fields: `id`, `conditionid`, `name`, `value`, `sortorder`, `timecreated`, `timemodified`
  - `name`: Display text (e.g., "Mañana")
  - `value`: Technical value for profile field (e.g., `turno_manana`)

- **`local_course_access_selections`**: User selections
  - Fields: `id`, `userid`, `courseid`, `conditionid`, `optionid`, `timecreated`, `timemodified`
  - Unique index on `userid, conditionid`
  - Used for history tracking and reporting

- **`local_course_access_history`**: Change audit trail
  - Fields: `id`, `userid`, `courseid`, `conditionid`, `old_optionid`, `new_optionid`, `reason`, `changed_by`, `timecreated`
  - Currently defined but not actively used

> Note: the table names exceed Moodle's 28-character guideline (e.g. `local_course_access_conditions` is 34 chars). They work on engines that already tolerate the previous 33-char names, but would warn on a moodle.org submission.

### Modal injection via output hook (not an event observer)

The blocking modal is injected from an **output hook**, registered in
[db/hooks.php](workspace/local_course_access/db/hooks.php) and implemented in
[classes/hook_callbacks.php](workspace/local_course_access/classes/hook_callbacks.php):

- **Hook**: `\core\hook\output\before_standard_top_of_body_html_generation` (Moodle 4.4+)
- **Callback**: `hook_callbacks::inject_condition_modal()`
  - Scope: only `course-view-*` pagetypes on a real course (`$PAGE->course->id > SITEID`)
  - Decision: delegates to `local_course_access_get_pending_condition()` (shared skip rules)
  - Action: injects modal via `local_course_access_inject_modal()`

> **Why a hook, not the old `course_viewed` observer.** Event observers must not
> depend on output/`$PAGE` (per Moodle policy), and `\core\event\course_viewed`
> also fires from the `core_course_view_course` web service (mobile app / REST),
> where `js_call_amd()` does nothing and the gate would be silently bypassed. The
> output hook only runs during a real HTML page render with `$PAGE` ready and
> output not yet started. `db/events.php` is now empty; `classes/observer.php`
> was removed.

### Key Library Functions

All defined in [lib.php](workspace/local_course_access/lib.php):

**Condition Management:**
- `local_course_access_get_conditions_for_course($courseid)`: Get all conditions with nested options
- `local_course_access_save_conditions($courseid, $conditions_data)`: Transactional save with validation

**Profile Field Management:**
- `local_course_access_get_field_shortname($courseid, $conditionid, $custom_id = null)`: **Single source of truth** for the profile-field shortname. Every other function must build the shortname through this — do not hand-format `acc_..._...` anywhere else.
  - Format: `acc_{custom_id}_{conditionid}` (custom_id defaults to courseid)
- `local_course_access_create_profile_field($courseid, $conditionid, $conditionname, $custom_id = null)`: Creates/updates Moodle custom profile field
  - Name format: `{CourseShortname}`
  - Category: "Accesos del Curso"
- `local_course_access_save_to_profile($userid, $courseid, $conditionid, $value)`: Updates user's profile field value
- `local_course_access_get_pending_condition($userid, $courseid)`: Shared "should the modal show?" decision (skips admins, configurers, completed users, missing/disabled conditions). Returns the pending condition or null.

**Selection Functions:**
- `local_course_access_save_selection($userid, $courseid, $conditionid, $optionid)`: Saves to both selections table and profile field
- `local_course_access_user_has_completed_all($userid, $courseid)`: Checks if all conditions completed (queries profile field directly)

**Modal Injection:**
- `local_course_access_inject_modal($courseid, $condition)`: Prepares data and calls AMD module via `$PAGE->requires->js_call_amd()`

**Navigation:**
- `local_course_access_extend_navigation_course($navigation, $course, $context)`: Adds configuration link to course navigation

### Capabilities

Defined in [db/access.php](workspace/local_course_access/db/access.php):

- **`local/course_access:manageconditionals`**: System-level management (managers only)
- **`local/course_access:configure`**: Course-level configuration (teachers, managers)
- **`local/course_access:viewreports`**: View selection reports (teachers, managers)

### JavaScript Architecture

The plugin uses Moodle's AMD (Asynchronous Module Definition) system:

- **Student modal module**: `local_course_access/condition_modal` ([amd/src/condition_modal.js](workspace/local_course_access/amd/src/condition_modal.js))
  - **Injection**: Via `$PAGE->requires->js_call_amd('local_course_access/condition_modal', 'init', [$data])`, called from the output hook
  - **Data structure**: JSON object with `courseid`, `condition` (id, name, description), and `options` array
  - **Modal behavior**: Blocks all interaction, sends AJAX to [ajax.php](workspace/local_course_access/ajax.php), reloads on success
- **Configure-form module**: `local_course_access/configure_form` ([amd/src/configure_form.js](workspace/local_course_access/amd/src/configure_form.js))
  - Powers the teacher form: add/remove option rows (min 2, `core/notification`), and auto-suggests the technical value from the display name (kept visible/editable; never overwrites a manual or existing value)
  - Init from [configure.php](workspace/local_course_access/configure.php) via `js_call_amd('local_course_access/configure_form', 'init')`

### Configure page (v1.8.0)
The teacher page is Moodle-standard: a controller ([configure.php](workspace/local_course_access/configure.php)) that renders Mustache templates ([templates/configure_form.mustache](workspace/local_course_access/templates/configure_form.mustache) + [templates/option_row.mustache](workspace/local_course_access/templates/option_row.mustache)), with CSS in [styles.css](workspace/local_course_access/styles.css) and all text in the language packs (no inline `<script>`/`<style>`, no `alert()`, no hardcoded strings). A post-save "what's next" panel shows the exact profile field name and each option's value to guide the teacher to Restrict access. The `option_row` partial is rendered server-side for existing rows and client-side (via `core/templates`) when adding.

### Activate/Pause lifecycle and custom_id safety (v1.8.1)
The `enabled` flag controls ONLY the selection modal (the "gate"). It does **not** touch Moodle's per-activity access restrictions — those are core `availability` conditions keyed to the profile field and are independent of this flag.
- **New conditions start PAUSED.** The teacher configures, sets up restrictions using the field, then activates. A badge shows Active/Paused.
- **Activation requires** a name + ≥2 valid options. Enforced both server-side (`save_conditions` throws `error_activate_requirements`) and client-side (the switch is disabled until valid).
- **Activating (paused→active)** clears every student's stored value (`local_course_access_reset_profile_values`) so the modal re-prompts everyone; selections rows are kept and overwritten on re-pick. The JS confirms first when selections already exist.
- **Pausing** deletes nothing; new students simply have no value.
- **custom_id is immutable once the field is in use** (`local_course_access_customid_is_locked`: any restriction referencing the field, any selection, or any stored value). Changing the shortname would orphan existing restrictions (core `availability_profile` returns `false` for a missing `cf`, hiding the activity for everyone) — confirmed in core `availability/condition/profile/classes/condition.php`. The field is rendered read-only and server-side rejects the change. `local_course_access_count_field_restrictions` scans `course_modules`/`course_sections` `availability` for `"cf":"<shortname>"`.

### Student re-selection (v2.1.0)
Whether a student can change their choice **after** the first selection is a per-condition advanced flag `allowchange` (default **0 = locked**).
- The profile field is created with `locked=1`, so a student can **never** edit it from their own profile — core hardFreezes locked fields for users without `moodle/user:update` (`user/profile/lib.php::edit_field_set_locked`). The only change vector is the plugin's `change_selection.php`.
- When `allowchange=0`: the "Change selection" nav node is not added (`extend_navigation_course`) and `change_selection.php` redirects with `changenotallowed` (defence against direct URL). `select.php` only serves the first-time selection (redirects once completed).
- When `allowchange=1`: the link appears; `change_selection.php` shows the existing warning and asks for an explicit confirmation via `local_course_access/change_confirm` (`core/notification` saveCancel) before saving. The change updates the profile field → access re-evaluates immediately; the change is recorded in the `history` table.
- The toggle lives in the configure form's "Advanced options" `<details>`.

**AMD build convention.** The module is authored as **legacy AMD** (`define([...], function(){})`), so no transpilation is required and `amd/build/condition_modal.min.js` is a verbatim copy of `amd/src/condition_modal.js`. Moodle serves `amd/build/`, so the build must never lag the source — `scripts/package_workspace.sh` re-mirrors src→build automatically at package time. If a module is ever rewritten as a native ES6 module (`import`/`export`), this copy is no longer sufficient and it must be compiled with `grunt amd` against a Moodle checkout.

## Development Commands

### Verify repository structure

```bash
./scripts/verify_isolation.sh
```

Checks that `workspace/`, `scripts/`, `docs/` exist and that `workspace/local_course_access/version.php` is present.

### Package Plugin for Distribution

```bash
./scripts/package_workspace.sh
```

- Stages `workspace/local_course_access/` into a temp dir as `course_access/` (the bare plugin name Moodle expects inside `local/`).
- Produces a timestamped ZIP: `build/course_access_YYYYMMDD_HHMMSS.zip`.
- Excludes `.DS_Store`, `__MACOSX`, and `.git` internals.
- Does **not** bump the version (unlike the old `crear-zip.sh`). Bump `$plugin->version`/`$plugin->release` in `version.php` manually when you publish a release that Moodle must detect as an update.

### Moodle Development Environment

- Edit the plugin under `workspace/local_course_access/`.
- Deploy by copying that directory to `{moodleroot}/local/course_access/` (or by installing the ZIP from `build/`).
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
- Namespaced classes under `local_course_access`
- String identifiers in language files (en/es)
- Database operations use `$DB` global
- Page setup using `$PAGE` global
- Capability checks using `require_capability()` and `has_capability()`
- Hook callbacks use namespaced classes registered in `db/hooks.php`
- JavaScript uses AMD modules in `amd/src/` directory

## Custom Profile Fields

The plugin automatically manages Moodle custom user profile fields:

**Automatic Creation:**
- Triggered when condition is saved via `local_course_access_create_profile_field()`
- Category: "Accesos del Curso" (auto-created if needed)
- Field shortname: built by `local_course_access_get_field_shortname()` → `acc_{custom_id}_{conditionid}` (custom_id defaults to courseid)
- Field name: `{CourseShortname}` (human-readable)

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

1. **Profile Fields are Source of Truth**: The `local_course_access_user_has_completed_all()` function checks profile fields directly, not the selections table. This ensures the modal logic matches Moodle's access restriction logic.

2. **Single Condition Enforcement**: While the database schema supports multiple conditions per course, the current UI (v1.7.0+) enforces one condition per course. The `configure.php` page only manages a single condition.

3. **Transactional Saves**: `local_course_access_save_conditions()` uses `$DB->start_delegated_transaction()` to ensure atomicity when saving conditions and options.

4. **AJAX Security**: [ajax.php](workspace/local_course_access/ajax.php) validates:
   - User is logged in and enrolled in course
   - Session key is valid
   - Condition belongs to specified course
   - Option belongs to specified condition

5. **Modal Skips**: `local_course_access_get_pending_condition()` returns null (no modal) for:
   - Site administrators
   - Users with `local/course_access:configure` capability
   - Users who have already completed selection (profile field has value)
   - Courses with no condition, or with the condition disabled

6. **Shortname Single Source of Truth**: All profile-field shortnames are built by `local_course_access_get_field_shortname()`. Never hand-format `acc_..._...` elsewhere — divergence previously broke the custom_id feature (student locked in an infinite modal).
