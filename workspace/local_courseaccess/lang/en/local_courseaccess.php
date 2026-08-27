<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Language strings for Course Access plugin
 *
 * @package    local_courseaccess
 * @copyright  2025 Rurak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['actions'] = 'Actions';
$string['activation_reset_notice'] = 'Access activated. Every student will be asked to choose again; previous selections were reset.';
$string['activationgate'] = 'To activate you need a name and at least 2 options (each with a name and a value).';
$string['active'] = 'Active';
$string['addconditional'] = 'Add Access';
$string['addoption'] = 'Add Option';
$string['advancedoptions'] = 'Advanced options';
$string['advancedsettings'] = 'Advanced Settings';
$string['allowchange'] = 'Allow students to change their selection';
$string['allowchange_help'] = 'By default, once a student chooses they cannot change their option (nor edit it from their profile). If you enable this, a "Change selection" link appears in the course so they can choose again. Changing the option immediately re-evaluates their access to restricted sections, activities, and resources.';
$string['alreadyselected'] = 'You have already made a selection for this course';
$string['archive'] = 'Archive';
$string['archived'] = 'Archived';
$string['assignconditional'] = 'Assign Access';
$string['assignment'] = 'Assignment';
$string['assignmentdeleted'] = 'Assignment removed successfully';
$string['assignments'] = 'Assignments';
$string['assignmentupdated'] = 'Assignment updated successfully';
$string['availableoptions'] = 'Available Options';
$string['backtocourse'] = 'Back to course';
$string['changenotallowed'] = 'The teacher does not allow changing your selection in this course.';
$string['changeselection'] = 'Change Selection';
$string['changeselection_nav'] = 'Change {$a}';
$string['changeselectionwarning'] = '⚠️ Changing your selection may affect your access to course sections, activities, and resources. Access restrictions will be applied immediately after the change.';
$string['conditionactive'] = 'Access active';
$string['conditionactive_help'] = 'Controls whether the selection modal is displayed to students when entering the course. New conditions are created paused so you can finish configuring options first. When paused, existing student selections are preserved, but new students will not see the modal. When reactivated after being paused, all selections are reset so everyone chooses again. (Note: This does not disable access restrictions configured on course sections, activities, or resources).';
$string['conditional'] = 'Access';
$string['conditionalarchived'] = 'Access archived successfully';
$string['conditionalassigned'] = 'Access assigned to course successfully';
$string['conditionalcreated'] = 'Access created successfully';
$string['conditionaldeleted'] = 'Access deleted successfully';
$string['conditionaldescription'] = 'Description';
$string['conditionaldescription_help'] = 'Optional description explaining what this access is for';
$string['conditionaldescription_placeholder'] = 'Optional description for students';
$string['conditionalexists'] = 'This access is already assigned to this course';
$string['conditionalinuse'] = 'This access cannot be deleted because it is assigned to one or more courses.';
$string['conditionalname'] = 'Name';
$string['conditionalname_help'] = 'Name of the classification (e.g., "Major", "Campus", "Modality", "Track", "Section")';
$string['conditionalname_placeholder'] = 'E.g. Major, Campus, Modality, Section';
$string['conditionalnotfound'] = 'Access not found';
$string['conditionals'] = 'Access';
$string['conditionalupdated'] = 'Access updated successfully';
$string['conditiondisabled'] = 'Access disabled';
$string['conditionenabled'] = 'Access enabled';
$string['configintro_heading'] = 'How it works';
$string['configintro_text'] = 'Define how students will be granted access to sections, activities, and resources (for example "Major", "Campus", or "Section"). When entering the course, each student will choose their option, and you can show or hide entire sections as well as individual activities and resources based on their choice.';
$string['configureconditions'] = 'Course access';
$string['confirm_change_body'] = 'Changing your selection will immediately change which course content you can access. Continue?';
$string['confirm_change_title'] = 'Change your selection';
$string['confirm_change_yes'] = 'Yes, change';
$string['confirm_reactivate_body'] = 'This will ask every student to choose again and reset the current selections. Continue?';
$string['confirm_reactivate_title'] = 'Reactivate the access';
$string['confirm_reactivate_yes'] = 'Yes, reactivate';
$string['connectionerror'] = 'Connection error. Please try again.';
$string['courseaccess'] = 'Course Access';
$string['courseaccess:configure'] = 'Configure course access';
$string['courseaccess:manageconditionals'] = 'Manage global access';
$string['courseaccess:viewreports'] = 'View course access reports';
$string['currentassignment'] = 'Current Assignment';
$string['currentselection'] = 'Current Selection';
$string['customid'] = 'Custom ID';
$string['customid_help'] = 'Profile field identifier. Defaults to the course short name (sanitised, no spaces or symbols). It can only be changed before the field is in use by selections or restrictions.';
$string['customid_locked'] = 'Cannot be changed: the field is already in use (there are selections or restrictions using it). Changing it would break the existing restrictions.';
$string['custommessage'] = 'Custom Message';
$string['custommessage_help'] = 'Optional message to display to students when they need to make their selection.';
$string['deleteconditional'] = 'Delete Access';
$string['deleteconditionalconfirm'] = 'Are you sure you want to delete the access "{$a}"? This will also delete all its options.';
$string['deleteoption'] = 'Delete Option';
$string['deleteoptionconfirm'] = 'Are you sure you want to delete the option "{$a}"?';
$string['disabled'] = 'Disabled';
$string['editconditional'] = 'Edit Access';
$string['editoption'] = 'Edit Option';
$string['enablecondition'] = 'Enable Access';
$string['enablecondition_help'] = 'If you disable the access, students will not see the selection modal and restrictions will not apply. Selection data will be preserved and you can reactivate the access later.';
$string['enabled'] = 'Enabled';
$string['error'] = 'Error';
$string['error_activate_requirements'] = 'To activate the access you need a name and at least 2 options, each with a display name and a value.';
$string['error_condition_name_required'] = 'The condition name is required.';
$string['error_min2options'] = 'You must define at least 2 options.';
$string['error_minoptions'] = 'The access "{$a}" needs at least 2 options.';
$string['error_no_condition_data'] = 'No condition data was received.';
$string['error_optionfields'] = 'Each option must have a display name and a value.';
$string['errorassigning'] = 'Error assigning access to course';
$string['errorchanging'] = 'Error changing your selection. Please try again.';
$string['errorsaving'] = 'Error saving your selection. Please try again.';
$string['invalidconditional'] = 'Invalid access selected';
$string['invalidcourse'] = 'Invalid course';
$string['invalidoption'] = 'Invalid option selected';
$string['manageconditionals'] = 'Manage Access';
$string['manageconditionals_desc'] = 'Create and manage global access that can be assigned to courses';
$string['manageoptions'] = 'Manage Options';
$string['migrationcomplete'] = 'Migration complete: {$a} profile fields updated';
$string['migrationfailed'] = 'Error during migration: {$a}';
$string['migrationwarning'] = '⚠️ Warning: Changing the custom ID will migrate all student profile fields. This operation may take several minutes if there are many students.';
$string['minoptionswarning'] = 'You must keep at least 2 options.';
$string['modalselectlabel'] = 'Select an option:';
$string['mustselect'] = 'You must select an option.';
$string['nextsteps_field'] = 'Profile field to use';
$string['nextsteps_heading'] = 'Configuration instructions';
$string['nextsteps_intro'] = 'Your access is active. To show different content to each group, you can add profile access restrictions to individual activities and resources (assignments, quizzes, forums, etc.) as well as entire course sections or topics.';
$string['nextsteps_paused'] = 'This access is paused. Set up the restrictions with the field below, then activate it so students choose.';
$string['nextsteps_step1'] = 'Edit the activity, resource, or course section and open the "Restrict access" section.';
$string['nextsteps_step2'] = 'Click "Add restriction" and choose "User profile".';
$string['nextsteps_step3'] = 'Select the field "{$a}", keep the condition "is equal to", and in the "Value" field type the exact value of the option that should see that content.';
$string['nextsteps_valuestable'] = 'Values of your options (copy them into the restrictions):';
$string['noassignment'] = 'No access is currently assigned to this course.';
$string['noconditionals'] = 'No access have been created yet. Create your first access to get started.';
$string['nooptions'] = 'No options have been added yet. Add at least one option for students to choose from.';
$string['nooptionsavailable'] = 'No options are available for selection. Please contact your course administrator.';
$string['option'] = 'Option';
$string['optionallabel'] = '(optional)';
$string['optioncreated'] = 'Option created successfully';
$string['optiondeleted'] = 'Option deleted successfully';
$string['optioninuse'] = 'This option cannot be deleted because it has been selected by one or more users.';
$string['optionname'] = 'Option Name';
$string['optionname_help'] = 'Display name for this option (e.g., "On-campus", "Online", "North Campus", "Section A")';
$string['optionname_placeholder'] = 'Display name (e.g. Online, On-campus, Section A)';
$string['optionnotfound'] = 'Option not found';
$string['options'] = 'Options';
$string['optionsintro'] = 'Add at least 2 options. Each student must choose one.';
$string['optionupdated'] = 'Option updated successfully';
$string['optionvalue'] = 'Value';
$string['optionvalue_help'] = 'This is the exact value you must type in "Restrict access" under "User profile" to show content only to students who choose this option. It is suggested automatically from the name, but you can edit it. No spaces or accents.';
$string['optionvalue_placeholder'] = 'Value (e.g. oncampus, online, section_a)';
$string['paused_with_restrictions'] = 'Saved as paused. {$a} activity(ies) restrict content with this field: while paused, students without a selection (including new ones) will not be able to see them.';
$string['pluginname'] = 'Course Access';
$string['privacy:history'] = 'Course access selection history';
$string['privacy:metadata:local_courseaccess_history'] = 'Stores history of selection changes';
$string['privacy:metadata:local_courseaccess_history:changed_by'] = 'The ID of the user who made the change';
$string['privacy:metadata:local_courseaccess_history:conditionid'] = 'The ID of the condition.';
$string['privacy:metadata:local_courseaccess_history:courseid'] = 'The ID of the course';
$string['privacy:metadata:local_courseaccess_history:new_optionid'] = 'The new option';
$string['privacy:metadata:local_courseaccess_history:old_optionid'] = 'The previous option';
$string['privacy:metadata:local_courseaccess_history:timecreated'] = 'Time when the change was made';
$string['privacy:metadata:local_courseaccess_history:userid'] = 'The ID of the user whose selection changed';
$string['privacy:metadata:local_courseaccess_sel'] = 'Stores user selections for course access';
$string['privacy:metadata:local_courseaccess_sel:conditionid'] = 'The ID of the condition.';
$string['privacy:metadata:local_courseaccess_sel:courseid'] = 'The ID of the course';
$string['privacy:metadata:local_courseaccess_sel:optionid'] = 'The ID of the selected option';
$string['privacy:metadata:local_courseaccess_sel:timecreated'] = 'Time when the selection was made';
$string['privacy:metadata:local_courseaccess_sel:timemodified'] = 'Time when the selection was last changed';
$string['privacy:metadata:local_courseaccess_sel:userid'] = 'The ID of the user who made the selection';
$string['privacy:selections'] = 'Course access selections';
$string['profilefield'] = 'Profile Field';
$string['profilefield_help'] = 'Technical identifier automatically generated from the course ID. Use it when setting up access restrictions on Moodle sections, activities, and resources.';
$string['profilefieldcategory'] = 'Course accesses';
$string['removeoption'] = 'Remove';
$string['requiredfieldmissing'] = 'Required field missing';
$string['saved_paused_notice'] = 'Saved as paused. Students will not see anything yet. Activate it when you finish configuring the restrictions.';
$string['saveselection'] = 'Save Selection';
$string['saving'] = 'Saving...';
$string['selectcondition'] = 'Make Your Selection';
$string['selectconditional'] = 'Make Your Selection';
$string['selectconditional_help'] = 'Choose a access to assign to this course. Students will be required to select one of its options.';
$string['selectionchanged'] = 'Your selection has been changed successfully';
$string['selectionsaved'] = 'Your selection has been saved successfully';
$string['selectionssaved'] = 'Your selections have been saved successfully';
$string['selectoption'] = 'Select {$a}';
$string['selectoption_default'] = 'Please select an option';
$string['settings'] = 'Settings';
$string['sortorder'] = 'Sort Order';
$string['sortorder_help'] = 'Display order (lower numbers appear first)';
$string['statusactive'] = 'Active';
$string['statuspaused'] = 'Paused';
$string['step1_heading'] = 'Step 1: Define the access';
$string['step2_heading'] = 'Step 2: Add the options';
$string['unknownerror'] = 'An unknown error occurred.';
