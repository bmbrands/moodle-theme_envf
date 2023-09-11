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
namespace theme_envf\form;

use coding_exception;
use context_system;
use core_component;
use core_user;
use dml_exception;
use moodleform;
use theme_envf\local\utils;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/lib/formslib.php');

/**
 * Class user_edit_form.
 *
 * @copyright 1999 Martin Dougiamas  http://dougiamas.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package theme_envf
 */
class user_edit_form extends moodleform {

    /**
     * Define the form.
     *
     * @throws coding_exception
     */
    public function definition() {
        global $CFG, $COURSE;

        $mform = $this->_form;

        if (!is_array($this->_customdata)) {
            throw new coding_exception('invalid custom data for user_edit_form');
        }
        $user = $this->_customdata['user'];
        $allowchangepassword = !empty($this->_customdata['allowchangepassword']); // Works with value to false.
        $allowchangeemail = $this->_customdata['allowchangeemail'] ?? true;
        $displayemail = $this->_customdata['displayemail'] ?? true;
        // Works with value to false.
        $hascancelbutton = !empty($this->_customdata['hascancelbutton']) && $this->_customdata['hascancelbutton'];
        $userid = $user->id;

        if (empty($user->country)) {
            // We must unset the value here so $CFG->country can be used as default one.
            unset($user->country);
        }

        // Add some extra hidden fields.
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'course', $COURSE->id);
        $mform->setType('course', PARAM_INT);

        // Shared fields.
        if ($user->id > 0) {
            useredit_load_preferences($user, false);
        }

        $strrequired = get_string('required');
        $stringman = get_string_manager();

        // Add the necessary names.
        foreach (useredit_get_required_name_fields() as $fullname) {
            $purpose = user_edit_map_field_purpose($user->id, $fullname);
            $mform->addElement('text', $fullname, get_string($fullname), 'maxlength="100" size="30"' . $purpose);
            if ($stringman->string_exists('missing' . $fullname, 'core')) {
                $strmissingfield = get_string('missing' . $fullname, 'core');
            } else {
                $strmissingfield = $strrequired;
            }
            $mform->addRule($fullname, $strmissingfield, 'required', null, 'client');
            $mform->setType($fullname, PARAM_NOTAGS);
        }

        $enabledusernamefields = useredit_get_enabled_name_fields();
        // Add the enabled additional name fields.
        foreach ($enabledusernamefields as $addname) {
            $purpose = user_edit_map_field_purpose($user->id, $addname);
            $mform->addElement('text', $addname, get_string($addname), 'maxlength="100" size="30"' . $purpose);
            $mform->setType($addname, PARAM_NOTAGS);
        }

        // Do not show email field if change confirmation is pending.
        if ($displayemail) {
            if ($user->id > 0 && !empty($CFG->emailchangeconfirmation) && !empty($user->preference_newemail)) {
                $notice = get_string('emailchangepending', 'auth', $user);
                $notice .= '<br /><a href="edit.php?cancelemailchange=1&amp;id=' . $user->id . '">'
                        . get_string('emailchangecancel', 'auth') . '</a>';
                $mform->addElement('static', 'emailpending', get_string('email'), $notice);
            } else {

                $purpose = user_edit_map_field_purpose($user->id, 'email');
                $mform->addElement('text', 'email', get_string('email'), 'maxlength="100" size="30"' . $purpose);
                $mform->setType('email', PARAM_RAW_TRIMMED);
                if (!$allowchangeemail) {
                    $mform->freeze('email');
                } else {
                    $mform->addRule('email', $strrequired, 'required', null, 'client');
                }
            }
        } else {
            $mform->addElement('hidden', 'email');
            $mform->setType('email', PARAM_RAW_TRIMMED);
        }

        $mform->addElement('hidden', 'auth', $user->auth);
        $mform->setType('auth', PARAM_ALPHANUM);
        $mform->addHelpButton('auth', 'chooseauthmethod', 'auth');

        $purpose = user_edit_map_field_purpose($userid, 'username');
        $usernamelabel = utils::get_username_label($userid);

        $mform->addElement('text', 'username', $usernamelabel, 'size="20"' . $purpose);
        $mform->addHelpButton('username', 'username', 'auth');
        $mform->setType('username', PARAM_RAW);

        list($authoptions, $cannotchangepass, $cannotchangeusername) = $this->get_auth_info($userid);
        if ($userid !== -1) {
            if (in_array($user->auth, $cannotchangeusername)) {
                $mform->freeze('username');
            }
        }

        if ($allowchangepassword) {
            if (!empty($CFG->passwordpolicy)) {
                $mform->addElement('static', 'passwordpolicyinfo', '', print_password_policy());
            }

            $purpose = user_edit_map_field_purpose($userid, 'password');
            $mform->addElement('passwordunmask', 'newpassword', get_string('newpassword'), 'size="20"' . $purpose);
            $mform->addHelpButton('newpassword', 'newpassword');
            $mform->setType('newpassword', core_user::get_property_type('password'));
            $mform->disabledIf('newpassword', 'createpassword', 'checked');

            $mform->disabledIf('newpassword', 'auth', 'in', $cannotchangepass);
        }

        $mform->addElement('text', 'city', get_string('city'), 'maxlength="120" size="21"');
        $mform->setType('city', PARAM_TEXT);
        if (!empty($CFG->defaultcity)) {
            $mform->setDefault('city', $CFG->defaultcity);
        }

        $purpose = user_edit_map_field_purpose($user->id, 'country');
        $choices = get_string_manager()->get_list_of_countries();
        $choices = array('' => get_string('selectacountry') . '...') + $choices;
        $mform->addElement('select', 'country', get_string('selectacountry'), $choices, $purpose);
        if (!empty($CFG->country)) {
            $mform->setDefault('country', core_user::get_property_default('country'));
        }

        // Display a custom button if needed.
        $submitlabel = get_string('updatemyprofile', 'theme_envf');
        $btndisplayoptions = [];
        $btnclassoverride = $this->_customdata['btnclassoverride'] ?? false;
        if ($btnclassoverride) {
            $btndisplayoptions['customclassoverride'] = $btnclassoverride;
        }
        if ($hascancelbutton) {
            // When two elements we need a group.
            $buttonarray = array();
            $buttonarray[] = &$mform->createElement(
                    'submit',
                    'submitbutton',
                    $submitlabel,
                    null,
                    null,
                    $btndisplayoptions);
            $buttonarray[] = &$mform->createElement('cancel');
            $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
            $mform->closeHeaderBefore('buttonar');
        } else {
            // No group needed.
            $mform->addElement('submit', 'submitbutton', $submitlabel,
                    null,
                    null,
                    $btndisplayoptions);
            $mform->closeHeaderBefore('submitbutton');
        }

        $this->set_data($user);
    }

    /**
     * Get auth information
     *
     * @param int $userid
     * @return array
     * @throws coding_exception
     */
    protected function get_auth_info($userid) {
        $auths = core_component::get_plugin_list('auth');
        $enabled = get_string('pluginenabled', 'core_plugin');
        $disabled = get_string('plugindisabled', 'core_plugin');
        $authoptions = array($enabled => array(), $disabled => array());
        $cannotchangepass = array();
        $cannotchangeusername = array();
        foreach ($auths as $auth => $unused) {
            $authinst = get_auth_plugin($auth);

            if (!$authinst->is_internal() || $auth === 'psup') {
                $cannotchangeusername[] = $auth;
            }

            $passwordurl = $authinst->change_password_url();
            if (!($authinst->can_change_password() && empty($passwordurl))) {
                if (!($userid < 1 && $authinst->is_internal())) {
                    // This is unlikely but we can not create account without password
                    // when plugin uses passwords, we need to set it initially at least.
                    $cannotchangepass[] = $auth;
                }
            }
            if (is_enabled_auth($auth)) {
                $authoptions[$enabled][$auth] = get_string('pluginname', "auth_{$auth}");
            } else {
                $authoptions[$disabled][$auth] = get_string('pluginname', "auth_{$auth}");
            }
        }
        return array($authoptions, $cannotchangepass, $cannotchangeusername);
    }

    /**
     * Extend the form definition after the data has been parsed.
     */
    public function definition_after_data() {
        $mform = $this->_form;

        // Trim required name fields.
        foreach (useredit_get_required_name_fields() as $field) {
            $mform->applyFilter($field, 'trim');
        }
    }

    /**
     * Validate incoming form data.
     *
     * @param array $usernew
     * @param array $files
     * @return array
     * @throws dml_exception
     * @throws coding_exception
     */
    public function validation($usernew, $files) {
        global $CFG, $DB;

        $errors = parent::validation($usernew, $files);

        $usernew = (object) $usernew;
        $user = $DB->get_record('user', array('id' => $usernew->id));

        // Validate email.
        if (!isset($usernew->email)) {
            $errors['email'] = get_string('invalidemail');
        } else if (!validate_email($usernew->email)) {
            $errors['email'] = get_string('invalidemail');
        } else if (($usernew->email !== $user->email) && empty($CFG->allowaccountssameemail)) {
            // Make a case-insensitive query for the given email address.
            $select = $DB->sql_equal('email', ':email', false) . ' AND mnethostid = :mnethostid AND id <> :userid';
            $params = array(
                    'email' => $usernew->email,
                    'mnethostid' => $CFG->mnet_localhost_id,
                    'userid' => $usernew->id
            );
            // If there are other user(s) that already have the same email, show an error.
            if ($DB->record_exists_select('user', $select, $params)) {
                $errors['email'] = get_string('emailexists');
            }
        }

        if (isset($usernew->email) && (($usernew->email === $user->email) && over_bounce_threshold($user))) {
            $errors['email'] = get_string('toomanybounces');
        }

        if (isset($usernew->email) && !empty($CFG->verifychangedemail) &&
                !isset($errors['email']) && !has_capability('moodle/user:update', context_system::instance())) {
            $errorstr = email_is_not_allowed($usernew->email);
            if ($errorstr !== false) {
                $errors['email'] = $errorstr;
            }
        }

        if (!empty($usernew->newpassword)) {
            $errmsg = ''; // Prevent eclipse warning.
            if (!check_password_policy($usernew->newpassword, $errmsg, $usernew)) {
                $errors['newpassword'] = $errmsg;
            }
        }

        // Next the customisable profile fields.
        $errors += profile_validation($usernew, $files);

        return $errors;
    }

}

