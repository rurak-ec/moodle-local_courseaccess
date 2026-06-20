@local @local_course_access @javascript
Feature: Students must choose a course access option before viewing the course
  In order to split students by an attribute (shift, group, ...)
  As a teacher
  I need an active condition to block the course with a selection modal until the student chooses

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "users" exist:
      | username | firstname | lastname |
      | student1 | Sam       | Student  |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | student1 | C1     | student |
    And the following "local_course_access > conditions" exist:
      | course | name  | options                             |
      | C1     | Shift | Morning:morning,Afternoon:afternoon |

  Scenario: The blocking selection modal appears and saving it unblocks the course
    When I log in as "student1"
    And I am on "Course 1" course homepage
    Then "[data-region='lcc-modal']" "css_element" should exist
    And I should see "Shift"
    When I set the field "Select an option:" to "Morning"
    And I click on "Save" "button" in the "[data-region='lcc-modal']" "css_element"
    Then "[data-region='lcc-modal']" "css_element" should not exist
