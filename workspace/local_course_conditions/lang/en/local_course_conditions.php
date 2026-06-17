<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Language strings for Course Conditionals plugin
 *
 * @package    local_course_conditions
 * @copyright  2025 Rurak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Plugin name
$string['pluginname'] = 'Condiciones de Curso';
$string['course_conditions'] = 'Condiciones de Curso';

// Capabilities
$string['course_conditions:configure'] = 'Configure course conditionals';
$string['course_conditions:viewreports'] = 'View course conditionals reports';
$string['course_conditions:manageconditionals'] = 'Manage global conditionals';

// Navigation
$string['manageconditionals'] = 'Manage Conditionals';
$string['configureconditions'] = 'Configure Course Conditions';
$string['manageoptions'] = 'Manage Options';
$string['assignconditional'] = 'Assign Conditional';
$string['backtocourse'] = 'Back to course';

// Conditionals management
$string['conditional'] = 'Conditional';
$string['conditionals'] = 'Conditionals';
$string['addconditional'] = 'Add Conditional';
$string['editconditional'] = 'Edit Conditional';
$string['deleteconditional'] = 'Delete Conditional';
$string['conditionalname'] = 'Conditional Name';
$string['conditionalname_help'] = 'Name of the conditional (e.g., "Lab Group", "Exam Mode", "Study Track")';
$string['conditionaldescription'] = 'Description';
$string['conditionaldescription_help'] = 'Optional description explaining what this conditional is for';
$string['noconditionals'] = 'No conditionals have been created yet. Create your first conditional to get started.';
$string['conditionalcreated'] = 'Conditional created successfully';
$string['conditionalupdated'] = 'Conditional updated successfully';
$string['conditionaldeleted'] = 'Conditional deleted successfully';
$string['conditionalarchived'] = 'Conditional archived successfully';
$string['deleteconditionalconfirm'] = 'Are you sure you want to delete the conditional "{$a}"? This will also delete all its options.';
$string['conditionalinuse'] = 'This conditional cannot be deleted because it is assigned to one or more courses.';
$string['conditionalnotfound'] = 'Conditional not found';

// Options management
$string['option'] = 'Option';
$string['options'] = 'Options';
$string['addoption'] = 'Add Option';
$string['editoption'] = 'Edit Option';
$string['deleteoption'] = 'Delete Option';
$string['optionname'] = 'Option Name';
$string['optionname_help'] = 'Display name for this option (e.g., "Group A", "Morning", "Track 1")';
$string['optionvalue'] = 'Option Value';
$string['optionvalue_help'] = 'Optional technical value stored in profile field. If empty, the name will be used.';
$string['nooptions'] = 'No options have been added yet. Add at least one option for students to choose from.';
$string['optioncreated'] = 'Option created successfully';
$string['optionupdated'] = 'Option updated successfully';
$string['optiondeleted'] = 'Option deleted successfully';
$string['deleteoptionconfirm'] = 'Are you sure you want to delete the option "{$a}"?';
$string['optioninuse'] = 'This option cannot be deleted because it has been selected by one or more users.';
$string['optionnotfound'] = 'Option not found';
$string['nooptionsavailable'] = 'No options are available for selection. Please contact your course administrator.';
$string['availableoptions'] = 'Available Options';

// Assignment to courses
$string['assignment'] = 'Assignment';
$string['assignments'] = 'Assignments';
$string['selectconditional'] = 'Select Conditional';
$string['selectconditional_help'] = 'Choose a conditional to assign to this course. Students will be required to select one of its options.';
$string['custommessage'] = 'Custom Message';
$string['custommessage_help'] = 'Optional message to display to students when they need to make their selection.';
$string['currentassignment'] = 'Current Assignment';
$string['noassignment'] = 'No conditional is currently assigned to this course.';
$string['conditionalassigned'] = 'Conditional assigned to course successfully';
$string['assignmentdeleted'] = 'Assignment removed successfully';
$string['assignmentupdated'] = 'Assignment updated successfully';
$string['errorassigning'] = 'Error assigning conditional to course';
$string['conditionalexists'] = 'This conditional is already assigned to this course';
$string['invalidconditional'] = 'Invalid conditional selected';

// Student selection
$string['selectconditional'] = 'Make Your Selection';
$string['selectcondition'] = 'Make Your Selection';
$string['selectoption'] = 'Select {$a}';
$string['selectoption_default'] = 'Please select an option';
$string['saveselection'] = 'Save Selection';
$string['selectionsaved'] = 'Your selection has been saved successfully';
$string['selectionssaved'] = 'Your selections have been saved successfully';
$string['alreadyselected'] = 'You have already made a selection for this course';
$string['currentselection'] = 'Current Selection';
$string['errorsaving'] = 'Error saving your selection. Please try again.';
$string['invalidoption'] = 'Invalid option selected';

// Change selection
$string['changeselection'] = 'Change Selection';
$string['changeselection_nav'] = 'Change {$a}';
$string['selectionchanged'] = 'Your selection has been changed successfully';
$string['errorchanging'] = 'Error changing your selection. Please try again.';
$string['changeselectionwarning'] = '⚠️ Changing your selection may affect your access to course activities and resources. Access restrictions will be applied immediately after the change.';

// Profile fields
$string['profilefield'] = 'Profile Field';
$string['profilefieldcategory'] = 'Course Conditionals';

// Common
$string['active'] = 'Active';
$string['archived'] = 'Archived';
$string['archive'] = 'Archive';
$string['enabled'] = 'Enabled';
$string['disabled'] = 'Disabled';
$string['sortorder'] = 'Sort Order';
$string['sortorder_help'] = 'Display order (lower numbers appear first)';
$string['actions'] = 'Actions';

// Advanced settings
$string['advancedsettings'] = 'Advanced Settings';
$string['customid'] = 'Custom ID';
$string['customid_help'] = 'Custom identifier for the profile field. Defaults to the course ID. If you change it, all student profile fields will be automatically migrated.';
$string['enablecondition'] = 'Enable Condition';
$string['enablecondition_help'] = 'If you disable the condition, students will not see the selection modal and restrictions will not apply. Selection data will be preserved and you can reactivate the condition later.';
$string['conditionenabled'] = 'Condition enabled';
$string['conditiondisabled'] = 'Condition disabled';
$string['migrationwarning'] = '⚠️ Warning: Changing the custom ID will migrate all student profile fields. This operation may take several minutes if there are many students.';
$string['migrationcomplete'] = 'Migration complete: {$a} profile fields updated';
$string['migrationfailed'] = 'Error during migration: {$a}';

// Errors
$string['invalidcourse'] = 'Invalid course';
$string['error'] = 'Error';
$string['requiredfieldmissing'] = 'Required field missing';

// Privacy
$string['privacy:metadata:local_course_conditions_selections'] = 'Stores user selections for course conditionals';
$string['privacy:metadata:local_course_conditions_selections:userid'] = 'The ID of the user who made the selection';
$string['privacy:metadata:local_course_conditions_selections:courseid'] = 'The ID of the course';
$string['privacy:metadata:local_course_conditions_selections:optionid'] = 'The ID of the selected option';
$string['privacy:metadata:local_course_conditions_selections:timecreated'] = 'Time when the selection was made';
$string['privacy:metadata:local_course_conditions_selections:timemodified'] = 'Time when the selection was last changed';

$string['privacy:metadata:local_course_conditions_history'] = 'Stores history of selection changes';
$string['privacy:metadata:local_course_conditions_history:userid'] = 'The ID of the user whose selection changed';
$string['privacy:metadata:local_course_conditions_history:courseid'] = 'The ID of the course';
$string['privacy:metadata:local_course_conditions_history:old_optionid'] = 'The previous option';
$string['privacy:metadata:local_course_conditions_history:new_optionid'] = 'The new option';
$string['privacy:metadata:local_course_conditions_history:changed_by'] = 'The ID of the user who made the change';
$string['privacy:metadata:local_course_conditions_history:timecreated'] = 'Time when the change was made';

// Settings page (admin)
$string['settings'] = 'Settings';
$string['manageconditionals_desc'] = 'Create and manage global conditionals that can be assigned to courses';
