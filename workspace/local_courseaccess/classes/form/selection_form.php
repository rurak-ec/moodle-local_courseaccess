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

namespace local_courseaccess\form;

use local_courseaccess\selection;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * The form where a student chooses the options of a course condition: a list (one) or checkboxes (several).
 *
 * Used by select.php (the first choice, without JavaScript) and change_selection.php (changing it).
 *
 * Custom data:
 * - condition: the condition with its options;
 * - current: int[] the options the student has chosen (ticked or selected to start with);
 * - cancel: bool whether to show a cancel button.
 *
 * @package    local_courseaccess
 * @copyright  2026 Rurak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class selection_form extends \moodleform {
    /** @var string Name of the group of checkboxes. */
    private const GROUP = 'options';

    /** @var string Prefix of each checkbox name inside the group (followed by the option id). */
    private const PREFIX = 'o';

    /**
     * Define the form.
     */
    protected function definition() {
        $mform = $this->_form;
        $condition = $this->_customdata['condition'];
        $current = array_map('intval', $this->_customdata['current'] ?? []);
        $name = format_string($condition->name);

        if (selection::is_multiple($condition)) {
            $boxes = [];
            foreach ($condition->options as $option) {
                $boxes[] = $mform->createElement(
                    'advcheckbox',
                    self::PREFIX . $option->id,
                    '',
                    format_string($option->name)
                );
            }
            // One checkbox per line: the group is a wrapping flex row, and a full-width item breaks the line.
            $mform->addGroup($boxes, self::GROUP, $name, \html_writer::div('', 'w-100'), true);
            foreach ($current as $optionid) {
                $mform->setDefault(self::GROUP . '[' . self::PREFIX . $optionid . ']', 1);
            }
            $max = (int)($condition->maxselect ?? 0);
            if ($max > 0) {
                $mform->addElement('static', 'maxhint', '', get_string('modalmaxhint', 'local_courseaccess', $max));
            }
        } else {
            $choices = ['' => get_string('choosedots')];
            foreach ($condition->options as $option) {
                $choices[$option->id] = format_string($option->name);
            }
            $mform->addElement('select', 'optionid', $name, $choices);
            if ($current) {
                $mform->setDefault('optionid', reset($current));
            }
        }

        $this->add_action_buttons(!empty($this->_customdata['cancel']), get_string('saveselection', 'local_courseaccess'));
    }

    /**
     * The choice must be valid for the condition (at least one option, and no more than it allows).
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        $condition = $this->_customdata['condition'];
        try {
            selection::validate($condition, self::chosen($condition, $data));
        } catch (\moodle_exception $e) {
            $errors[selection::is_multiple($condition) ? self::GROUP : 'optionid'] = $e->getMessage();
        }
        return $errors;
    }

    /**
     * The options chosen in the submitted form.
     *
     * @return int[] Empty when the form was not submitted or is not valid
     */
    public function get_optionids(): array {
        $data = $this->get_data();
        if (!$data) {
            return [];
        }
        return self::chosen($this->_customdata['condition'], (array)$data);
    }

    /**
     * Read the chosen option ids from the form data.
     *
     * @param \stdClass $condition
     * @param array $data
     * @return int[]
     */
    private static function chosen(\stdClass $condition, array $data): array {
        if (!selection::is_multiple($condition)) {
            return empty($data['optionid']) ? [] : [(int)$data['optionid']];
        }
        $ids = [];
        foreach ((array)($data[self::GROUP] ?? []) as $key => $ticked) {
            if (!empty($ticked)) {
                $ids[] = (int)substr((string)$key, strlen(self::PREFIX));
            }
        }
        return $ids;
    }
}
