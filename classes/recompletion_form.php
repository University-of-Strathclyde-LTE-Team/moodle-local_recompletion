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
 * Edit course completion settings - the form definition.
 *
 * @package     local_recompletion
 * @copyright   2017 Dan Marsden
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Defines the course completion settings form.
 *
 * @copyright   2017 Dan Marsden
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_recompletion_recompletion_form extends moodleform {
    /** @var string */
    const RECOMPLETION_TYPE_DISABLED = '';

    /** @var string */
    const RECOMPLETION_TYPE_PERIOD = 'period';

    /** @var string */
    const RECOMPLETION_TYPE_ONDEMAND = 'ondemand';
    /**
     * Defines the form fields.
     */
    public function definition() {

        $mform = $this->_form;
        $course = $this->_customdata['course'];
        $config = get_config('local_recompletion');

        $context = \context_course::instance($course->id);

        $editoroptions = array(
            'subdirs' => 0,
            'maxbytes' => 0,
            'maxfiles' => 0,
            'changeformat' => 0,
            'context' => $context,
            'noclean' => 0,
            'trusttext' => 0,
            'cols' => '50',
            'rows' => '8'
        );

        $mform->addElement('select', 'recompletiontype', get_string('recompletiontype', 'local_recompletion'), [
            self::RECOMPLETION_TYPE_DISABLED => get_string('recompletiontype:disabled', 'local_recompletion'),
            self::RECOMPLETION_TYPE_PERIOD => get_string('recompletiontype:period', 'local_recompletion'),
            self::RECOMPLETION_TYPE_ONDEMAND => get_string('recompletiontype:ondemand', 'local_recompletion'),
        ]);
        $mform->setDefault('recompletiontype', $config->recompletiontype ?? '');
        $mform->addHelpButton('recompletiontype', 'recompletiontype', 'local_recompletion');

        $options = array('optional' => false, 'defaultunit' => 86400);
        $mform->addElement('duration', 'recompletionduration', get_string('recompletionrange', 'local_recompletion'), $options);
        $mform->addHelpButton('recompletionduration', 'recompletionrange', 'local_recompletion');
        $mform->hideIf('recompletionduration', 'recompletiontype', 'not', 'period');
        $mform->setDefault('recompletionduration', $config->duration);

        $mform->addElement('checkbox', 'recompletionemailenable', get_string('recompletionemailenable', 'local_recompletion'));
        $mform->setDefault('recompletionemailenable', $config->emailenable);
        $mform->addHelpButton('recompletionemailenable', 'recompletionemailenable', 'local_recompletion');
        $mform->hideIf('recompletionemailenable', 'recompletiontype', 'eq', '');

        $mform->addElement('checkbox', 'recompletionunenrolenable', get_string('recompletionunenrolenable', 'local_recompletion'));
        $mform->setDefault('recompletionunenrolenable', $config->unenrolenable);
        $mform->addHelpButton('recompletionunenrolenable', 'recompletionunenrolenable', 'local_recompletion');
        $mform->hideIf('recompletionunenrolenable', 'recompletiontype', 'eq', '');

        // Email Notification settings.
        $mform->addElement('header', 'emailheader', get_string('emailrecompletiontitle', 'local_recompletion'));
        $mform->setExpanded('emailheader', false);
        $mform->addElement('text', 'recompletionemailsubject', get_string('recompletionemailsubject', 'local_recompletion'),
                'size = "80"');
        $mform->setType('recompletionemailsubject', PARAM_TEXT);
        $mform->addHelpButton('recompletionemailsubject', 'recompletionemailsubject', 'local_recompletion');
        $mform->disabledIf('recompletionemailsubject', 'recompletiontype', 'eq', '');
        $mform->disabledIf('recompletionemailsubject', 'recompletionemailenable', 'notchecked');
        $mform->setDefault('recompletionemailsubject', $config->emailsubject);

        $mform->addElement('editor', 'recompletionemailbody', get_string('recompletionemailbody', 'local_recompletion'),
            $editoroptions);
        $mform->setDefault('recompletionemailbody', array('text' => $config->emailbody,
            'format' => FORMAT_HTML));
        $mform->addHelpButton('recompletionemailbody', 'recompletionemailbody', 'local_recompletion');
        $mform->disabledIf('recompletionemailbody', 'recompletiontype', 'eq', '');
        $mform->disabledIf('recompletionemailbody', 'recompletionemailenable', 'notchecked');

        // Advanced recompletion settings.
        // Delete data section.
        $mform->addElement('header', 'advancedheader', get_string('advancedrecompletiontitle', 'local_recompletion'));
        $mform->setExpanded('advancedheader', false);

        $mform->addElement('checkbox', 'deletegradedata', get_string('deletegradedata', 'local_recompletion'));
        $mform->setDefault('deletegradedata', $config->deletegradedata);
        $mform->addHelpButton('deletegradedata', 'deletegradedata', 'local_recompletion');

        $mform->addElement('checkbox', 'archivecompletiondata', get_string('archivecompletiondata', 'local_recompletion'));
        // If we are forcing completion data archive, always be ticked.
        $archivedefault = $config->forcearchivecompletiondata ? 1 : $config->archivecompletiondata;
        $mform->setDefault('archivecompletiondata', $archivedefault);
        $mform->addHelpButton('archivecompletiondata', 'archivecompletiondata', 'local_recompletion');

        // Get all plugins that are supported.
        $plugins = local_recompletion_get_supported_plugins();
        foreach ($plugins as $plugin) {
            $fqn = 'local_recompletion\\plugins\\' . $plugin;
            $fqn::editingform($mform);
        }

        $mform->addElement('header', 'restrictionsheader', get_string('restrictionsheader', 'local_recompletion'));
        $restrictions = local_recompletion_get_supported_restrictions();
        foreach ($restrictions as $plugin) {
            $fqn = 'local_recompletion\\local\\restrictions\\' . $plugin;
            $fqn::editingform($mform);
        }
        $mform->disabledIf('deletegradedata', 'recompletiontype', 'eq', '');
        $mform->disabledIf('archivecompletiondata', 'recompletiontype', 'eq', '');
        $mform->disabledIf('archivecompletiondata', 'forcearchive', 'eq');

        // Add common action buttons.
        $this->add_action_buttons();

        // Add hidden fields.
        $mform->addElement('hidden', 'course', $course->id);
        $mform->setType('course', PARAM_INT);
        $mform->addElement('hidden', 'forcearchive', $config->forcearchivecompletiondata);
        $mform->setType('forcearchive', PARAM_BOOL);
    }
}
