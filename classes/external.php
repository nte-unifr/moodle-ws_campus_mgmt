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
 * External functions for returning course information.
 *
 * @package    local_ws_campus_mgmt
 * @copyright  2017 DIT-Centre NTE, University of Fribourg
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//require_once($CFG->libdir . "/classes/user.php");
require_once($CFG->dirroot.'/lib/externallib.php');

const LOCAL_WS_CAMPUS_MGMT_SCALE_FR = 45;
const LOCAL_WS_CAMPUS_MGMT_SCALE_DE = 78;
const LOCAL_WS_CAMPUS_MGMT_SCALE_EN = 51;
const LOCAL_WS_CAMPUS_MGMT_SCALE_IT = 49;
const LOCAL_WS_CAMPUS_MGMT_STUDENT_ROLE_ID = 5;

class local_ws_campus_mgmt_external extends external_api {

    private static function get_language_level($userid, $language) {
        global $DB;

        switch ($language) {
            case 'fr':
                $scaleid = LOCAL_WS_CAMPUS_MGMT_SCALE_FR;
                break;
            case 'de':
                $scaleid = LOCAL_WS_CAMPUS_MGMT_SCALE_DE;
                break;
            case 'en':
                $scaleid = LOCAL_WS_CAMPUS_MGMT_SCALE_EN;
                break;
            case 'it':
                $scaleid = LOCAL_WS_CAMPUS_MGMT_SCALE_IT;
                break;
            default:
                throw new moodle_exception('invalid language', 'error');
        }

        $result = -1;

        $grade = $DB->get_records_sql('select {grade_grades}.finalgrade from {grade_grades} join {grade_items} where ({grade_grades}.itemid = {grade_items}.id) and ({grade_grades}.finalgrade is not null) and ({grade_items}.scaleid = :scaleid) and ({grade_grades}.userid = :userid) order by {grade_grades}.timemodified desc',array('scaleid'=>$scaleid, 'userid'=>$userid),0,1);

//          print_r($grade);
        if (!empty($grade)) {
            $result = intval(current($grade)->finalgrade);
        }

        return $result;
    }


    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_user_language_level_parameters() {
    return new external_function_parameters(
        array(
            'username' => new external_value(PARAM_USERNAME, 'Username', VALUE_REQUIRED),
            'language' => new external_value(PARAM_ALPHA, 'Language', VALUE_REQUIRED)
            )
        );
    }

    /**
     * Get a user's level in specificed language.
     *
     * @param string $username
     * @param string $language
     * @return int
     */
    public static function get_user_language_level($username, $language) {
        global $DB, $CFG;

        require_capability('moodle/grade:viewall',context_system::instance());

        // Validate parameters passed from webservice.
        $params = self::validate_parameters(self::get_user_language_level_parameters(), array('username' => $username,'language' => $language));

        // Extract the userid from the username.
          //$userid = core_user::get_user_by_username($username,'id')->id;
        $userid = $DB->get_field('user', 'id', array('username' => $username,'mnethostid' => $CFG->mnet_localhost_id));

        $result = -1;
        if ($userid) {
            $result = self::get_language_level($userid, $language);
        }

        return $result;
    }

    /**
     * Returns description of get_user_language_level_returns() result value.
     *
     * @return \external_description
     */
    public static function get_user_language_level_returns() {
        return new external_value(PARAM_INT, 'The user\'s language level');
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_user_all_languages_levels_parameters() {
    return new external_function_parameters(
        array(
            'username' => new external_value(PARAM_USERNAME, 'Username', VALUE_REQUIRED)
            )
        );
    }

    /**
     * Get a user's levels in all languages.
     *
     * @param string $username
     * @param string $language
     * @return int
     */
    public static function get_user_all_languages_levels($username) {
        global $DB, $CFG;

        require_capability('moodle/grade:viewall',context_system::instance());

        // Validate parameters passed from webservice.
        $params = self::validate_parameters(self::get_user_all_languages_levels_parameters(), array('username' => $username));

        // Extract the userid from the username.
          //$userid = core_user::get_user_by_username($username,'id')->id;
        $userid = $DB->get_field('user', 'id', array('username' => $username,'mnethostid' => $CFG->mnet_localhost_id));

        $result = array();
        if ($userid) {
            foreach (array("fr","de","en","it") as $language) {
                $grade = self::get_language_level($userid, $language);
                if ($grade <> -1) {
                        $result[] = array('language' => $language, 'level' => $grade);
                }
            }
        }

        return $result;
    }

    /**
     * Returns description of get_user_all_languages_levels() result value.
     *
     * @return \external_description
     */
    public static function get_user_all_languages_levels_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'language' => new external_value(PARAM_ALPHA, 'Language'),
                    'level'   => new external_value(PARAM_INT, 'Level in language')
                )
            )
        );
    }

    public static function enrol_user_parameters() {
        return new external_function_parameters(
            array(
                'username' => new external_value(PARAM_USERNAME, 'Username', VALUE_REQUIRED),
                'firstname' => new external_value(PARAM_TEXT, 'First name', VALUE_REQUIRED),
                'lastname' => new external_value(PARAM_TEXT, 'Last name', VALUE_REQUIRED),
                'email' => new external_value(PARAM_EMAIL, 'Email', VALUE_REQUIRED),
                'courseid' => new external_value(PARAM_INT, 'Course ID', VALUE_REQUIRED),
            )
        );
    }

    public static function enrol_user($username,$firstname,$lastname,$email,$courseid) {
        global $DB, $CFG;

        $params = self::validate_parameters(self::enrol_user_parameters(),
            array('username' => $username,'firstname' => $firstname,'lastname' => $lastname,'email' => $email,'courseid' => $courseid));

        $userid = $DB->get_field('user', 'id', array('username' => $username,'mnethostid' => $CFG->mnet_localhost_id));
        if (!$userid) {
            require_once("$CFG->dirroot/user/lib.php");
            $user = new stdClass();
            $user -> firstname = $firstname;
            $user -> lastname = $lastname;
            $user -> email = $email;
            $user -> username = $username;
            $user -> auth = "shibboleth";
            $user -> confirmed = 1;
            $user -> mnethostid = $CFG->mnet_localhost_id;
            $userid = user_create_user($user,false,false);
        }

        require_once($CFG->libdir . '/enrollib.php');
        $enrol = enrol_get_plugin('manual');

        require_capability('enrol/manual:enrol',context_system::instance());

        // Check manual enrolment plugin instance is enabled/exist.

/*        $instance = null;
        $enrolinstances = enrol_get_instances($courseid, true);
        foreach ($enrolinstances as $courseenrolinstance) {
            if ($courseenrolinstance->enrol == "manual") {
                $instance = $courseenrolinstance;
                break;
            }
        }*/

        $instance = $DB->get_record('enrol', array('courseid' => $courseid, 'enrol' => 'manual'));
        if (empty($instance)) {
            $errorparams = new stdClass();
            $errorparams->courseid = $courseid;
                throw new moodle_exception('wsnoinstance', 'enrol_manual', '', $errorparams);
        }

        // Finally proceed the enrolment.
        $enrol->enrol_user($instance, $userid, LOCAL_WS_CAMPUS_MGMT_STUDENT_ROLE_ID, 0, 0, ENROL_USER_ACTIVE);

    }

    public static function enrol_user_returns() {
        return null;
    }

    public static function unenrol_user_parameters() {
        return new external_function_parameters(
            array(
                'username' => new external_value(PARAM_USERNAME, 'Username', VALUE_REQUIRED),
                'courseid' => new external_value(PARAM_INT, 'Course ID', VALUE_REQUIRED)
            )
        );
    }

    public static function unenrol_user($username,$courseid) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::unenrol_user_parameters(), array('username' => $username, 'courseid' => $courseid));

        $userid = $DB->get_field('user', 'id', array('username' => $username,'mnethostid' => $CFG->mnet_localhost_id));
        if (!$userid) {
            throw new invalid_parameter_exception('User does not exist: '. $username);
        }

        require_once($CFG->libdir . '/enrollib.php');
        $enrol = enrol_get_plugin('manual');

        require_capability('enrol/manual:unenrol', context_system::instance());

        $instance = $DB->get_record('enrol', array('courseid' => $courseid, 'enrol' => 'manual'));
        if (empty($instance)) {
            $errorparams = new stdClass();
            $errorparams->courseid = $courseid;
                throw new moodle_exception('wsnoinstance', 'enrol_manual', '', $errorparams);
        }

        $enrol->unenrol_user($instance, $userid);
    }

    public static function unenrol_user_returns() {
        return null;
    }

}
