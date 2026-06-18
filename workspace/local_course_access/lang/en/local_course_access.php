<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Language strings for Course Access plugin
 *
 * @package    local_course_access
 * @copyright  2025 Rurak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Plugin name
$string['pluginname'] = 'Course Access';
$string['course_access'] = 'Course Access';

// Capabilities
$string['course_access:configure'] = 'Configure course access';
$string['course_access:viewreports'] = 'View course access reports';
$string['course_access:manageconditionals'] = 'Manage global access';

// Navigation
$string['manageconditionals'] = 'Manage Access';
$string['configureconditions'] = 'Course access';
$string['manageoptions'] = 'Manage Options';
$string['assignconditional'] = 'Assign Access';
$string['backtocourse'] = 'Back to course';

// Access management
$string['conditional'] = 'Access';
$string['conditionals'] = 'Access';
$string['addconditional'] = 'Add Access';
$string['editconditional'] = 'Edit Access';
$string['deleteconditional'] = 'Delete Access';
$string['conditionalname'] = 'Access Name';
$string['conditionalname_help'] = 'Name of the access (e.g., "Lab Group", "Exam Mode", "Study Track")';
$string['conditionaldescription'] = 'Description';
$string['conditionaldescription_help'] = 'Optional description explaining what this access is for';
$string['noconditionals'] = 'No access have been created yet. Create your first access to get started.';
$string['conditionalcreated'] = 'Access created successfully';
$string['conditionalupdated'] = 'Access updated successfully';
$string['conditionaldeleted'] = 'Access deleted successfully';
$string['conditionalarchived'] = 'Access archived successfully';
$string['deleteconditionalconfirm'] = 'Are you sure you want to delete the access "{$a}"? This will also delete all its options.';
$string['conditionalinuse'] = 'This access cannot be deleted because it is assigned to one or more courses.';
$string['conditionalnotfound'] = 'Access not found';

// Options management
$string['option'] = 'Option';
$string['options'] = 'Options';
$string['addoption'] = 'Add Option';
$string['editoption'] = 'Edit Option';
$string['deleteoption'] = 'Delete Option';
$string['optionname'] = 'Option Name';
$string['optionname_help'] = 'Display name for this option (e.g., "Group A", "Morning", "Track 1")';
$string['optionvalue'] = 'Technical value';
$string['optionvalue_help'] = 'This is the code you will use in "Restrict access" to show content only to students who pick this option. It is suggested automatically from the name, but you can edit it. No spaces or accents.';
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
$string['selectconditional'] = 'Select Access';
$string['selectconditional_help'] = 'Choose a access to assign to this course. Students will be required to select one of its options.';
$string['custommessage'] = 'Custom Message';
$string['custommessage_help'] = 'Optional message to display to students when they need to make their selection.';
$string['currentassignment'] = 'Current Assignment';
$string['noassignment'] = 'No access is currently assigned to this course.';
$string['conditionalassigned'] = 'Access assigned to course successfully';
$string['assignmentdeleted'] = 'Assignment removed successfully';
$string['assignmentupdated'] = 'Assignment updated successfully';
$string['errorassigning'] = 'Error assigning access to course';
$string['conditionalexists'] = 'This access is already assigned to this course';
$string['invalidconditional'] = 'Invalid access selected';

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
$string['profilefieldcategory'] = 'Course Access';

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
$string['customid_help'] = 'Profile field identifier. Defaults to the course short name (sanitised, no spaces or symbols). It can only be changed before the field is in use by selections or restrictions.';
$string['enablecondition'] = 'Enable Access';
$string['enablecondition_help'] = 'If you disable the access, students will not see the selection modal and restrictions will not apply. Selection data will be preserved and you can reactivate the access later.';
$string['conditionenabled'] = 'Access enabled';
$string['conditiondisabled'] = 'Access disabled';
$string['migrationwarning'] = '⚠️ Warning: Changing the custom ID will migrate all student profile fields. This operation may take several minutes if there are many students.';
$string['migrationcomplete'] = 'Migration complete: {$a} profile fields updated';
$string['migrationfailed'] = 'Error during migration: {$a}';

// Errors
$string['invalidcourse'] = 'Invalid course';
$string['error'] = 'Error';
$string['requiredfieldmissing'] = 'Required field missing';

// Privacy
$string['privacy:metadata:local_course_access_selections'] = 'Stores user selections for course access';
$string['privacy:metadata:local_course_access_selections:userid'] = 'The ID of the user who made the selection';
$string['privacy:metadata:local_course_access_selections:courseid'] = 'The ID of the course';
$string['privacy:metadata:local_course_access_selections:optionid'] = 'The ID of the selected option';
$string['privacy:metadata:local_course_access_selections:timecreated'] = 'Time when the selection was made';
$string['privacy:metadata:local_course_access_selections:timemodified'] = 'Time when the selection was last changed';

$string['privacy:metadata:local_course_access_history'] = 'Stores history of selection changes';
$string['privacy:metadata:local_course_access_history:userid'] = 'The ID of the user whose selection changed';
$string['privacy:metadata:local_course_access_history:courseid'] = 'The ID of the course';
$string['privacy:metadata:local_course_access_history:old_optionid'] = 'The previous option';
$string['privacy:metadata:local_course_access_history:new_optionid'] = 'The new option';
$string['privacy:metadata:local_course_access_history:changed_by'] = 'The ID of the user who made the change';
$string['privacy:metadata:local_course_access_history:timecreated'] = 'Time when the change was made';

// Settings page (admin)
$string['settings'] = 'Settings';
$string['manageconditionals_desc'] = 'Create and manage global access that can be assigned to courses';

// ----------------------------------------------------------------------------
// Course configuration page (UX v1.8)
// ----------------------------------------------------------------------------
// Intro / context
$string['configintro_heading'] = 'How it works';
$string['configintro_text'] = 'Define a access (for example "Shift" or "Group"). When a student enters the course, they must pick one of the options. You can then show different activities to each group by restricting access based on their choice.';

// Form steps
$string['step1_heading'] = 'Step 1: Define the access';
$string['step2_heading'] = 'Step 2: Add the options';
$string['optionallabel'] = '(optional)';
$string['optionsintro'] = 'Add at least 2 options. Each student must choose one.';

// Placeholders (localised)
$string['conditionalname_placeholder'] = 'E.g. Shift, Lab Group, Modality';
$string['conditionaldescription_placeholder'] = 'Optional description for students';
$string['optionname_placeholder'] = 'Display name (e.g. Morning, Group A)';
$string['optionvalue_placeholder'] = 'Technical value (e.g. shift_morning, group_a)';
$string['removeoption'] = 'Remove';
$string['minoptionswarning'] = 'You must keep at least 2 options.';

// Access state
$string['conditionactive'] = 'Access active';
$string['conditionactive_help'] = 'Controls only whether students are asked to choose (the modal). New access start paused: activate it once you finish configuring. ACTIVATING re-asks the selection from EVERYONE (previous selections are reset). PAUSING deletes nothing; new students are left without a value. This does NOT turn off the restrictions you set on activities: those are managed per activity by Moodle.';
$string['statusactive'] = 'Active';
$string['statuspaused'] = 'Paused';
$string['activationgate'] = 'To activate you need a name and at least 2 options (each with a name and a value).';
$string['error_activate_requirements'] = 'To activate the access you need a name and at least 2 options, each with a display name and a technical value.';
$string['customid_locked'] = 'Cannot be changed: the field is already in use (there are selections or restrictions using it). Changing it would break the existing restrictions.';
$string['activation_reset_notice'] = 'Access activated. Every student will be asked to choose again; previous selections were reset.';
$string['paused_with_restrictions'] = 'Saved as paused. {$a} activity(ies) restrict content with this field: while paused, students without a selection (including new ones) will not be able to see them.';
$string['saved_paused_notice'] = 'Saved as paused. Students will not see anything yet. Activate it when you finish configuring the restrictions.';
$string['nextsteps_paused'] = 'This access is paused. Set up the restrictions with the field below, then activate it so students choose.';
$string['confirm_reactivate_title'] = 'Reactivate the access';
$string['confirm_reactivate_body'] = 'This will ask every student to choose again and reset the current selections. Continue?';
$string['confirm_reactivate_yes'] = 'Yes, reactivate';
$string['error_minoptions'] = 'The access "{$a}" needs at least 2 options.';
$string['error_optionfields'] = 'Each option must have a display name and a technical value.';

// Advanced options (disclosure)
$string['advancedoptions'] = 'Advanced options';

// Allow students to change their selection
$string['allowchange'] = 'Allow students to change their selection';
$string['allowchange_help'] = 'By default, once a student chooses they cannot change their option (nor edit it from their profile). If you enable this, a "Change selection" link appears in the course so they can choose again. Changing the option re-evaluates their access to restricted content immediately.';
$string['changenotallowed'] = 'The teacher does not allow changing your selection in this course.';
$string['confirm_change_title'] = 'Change your selection';
$string['confirm_change_body'] = 'Changing your selection will immediately change which course content you can access. Continue?';
$string['confirm_change_yes'] = 'Yes, change';

// "What's next?" panel (post-save)
$string['nextsteps_heading'] = 'What\'s next? Show content per option';
$string['nextsteps_intro'] = 'Your access is active. To show different activities to each group, edit an activity and add an access restriction using the profile field shown below.';
$string['nextsteps_field'] = 'Profile field to use';
$string['nextsteps_step1'] = 'Edit a course activity or resource and open the "Restrict access" section.';
$string['nextsteps_step2'] = 'Click "Add restriction" and choose "User profile".';
$string['nextsteps_step3'] = 'Select the field "{$a}", the condition "is equal to", and type the technical value of the option that activity should show for.';
$string['nextsteps_valuestable'] = 'Your option values (copy them into the restrictions):';
