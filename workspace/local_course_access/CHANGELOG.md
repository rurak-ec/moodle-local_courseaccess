# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.7.16] - 2025-01-25

### Added
- **Change Selection Feature**: Students can now change their selection after the initial choice
  - New page `change_selection.php` for voluntary selection changes
  - Navigation link "Cambiar [CondiciónNombre]" appears for students who have already selected
  - Warning message about impact on access restrictions
  - History tracking for all selection changes in `local_course_access_history` table
- **Helper Functions** in `lib.php`:
  - `local_course_access_get_options_for_condition()`: Get options for a specific condition
  - `local_course_access_get_user_selection()`: Get user's current selection with JOIN
- **Language Strings**:
  - `changeselection_nav`: Dynamic navigation text with condition name
  - Enhanced `changeselectionwarning` with emoji and detailed explanation

### Changed
- **Navigation System**: Refactored `local_course_access_extend_navigation_course()` 
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
  - `local_course_access_conditions`: Course conditions
  - `local_course_access_options`: Condition options
  - `local_course_access_selections`: User selections
  - `local_course_access_history`: Selection change history

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
- [GitHub Repository](https://github.com/yourusername/moodle-local_course_access)
- [Issue Tracker](https://github.com/yourusername/moodle-local_course_access/issues)
