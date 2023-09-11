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
 * Theme plugin version definition.
 *
 * @package   theme_envf
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Serves any files associated with the theme settings.
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return bool
 * @throws coding_exception
 */
function theme_envf_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    return theme_clboost\local\utils::generic_pluginfile('envf', $course, $cm, $context, $filearea, $args, $forcedownload,
        $options);
}

/**
 * Extend form password
 *
 * @param MoodleQuickForm $mform
 * @param stdClass $user
 * @return void
 * @throws coding_exception
 */
function theme_envf_extend_set_password_form(MoodleQuickForm $mform, $user) {
    $element = $mform->getElement('username2');
    $element->setLabel(utils::get_username_label($user->id));
    $email = $mform->createElement('static', 'email', get_string('email'), $user->email);

    $mform->insertElementBefore($email, 'username2');

}