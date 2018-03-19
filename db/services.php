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
 * Custom web services for this plugin.
 *
 * @package    local_remote_courses
 * @copyright  2015 Lafayette College ITS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$functions = array(
    'local_ws_campus_mgmt_get_user_language_level' => array(
        'classname'    => 'local_ws_campus_mgmt_external',
        'methodname'   => 'get_user_language_level',
        'description'  => 'Get user\'s language level by username and language.',
        'type'         => 'read',
        'capabilities' => 'moodle/grade:viewall'
    ),

    'local_ws_campus_mgmt_get_user_all_languages_levels' => array(
        'classname'    => 'local_ws_campus_mgmt_external',
        'methodname'   => 'get_user_all_languages_levels',
        'description'  => 'Get user\'s levels in all language by username.',
        'type'         => 'read',
        'capabilities' => 'moodle/grade:viewall'
    ),

    'local_ws_campus_mgmt_enrol_user' => array(
        'classname'   => 'local_ws_campus_mgmt_external',
        'methodname'  => 'enrol_user',
        'description' => 'Manually enrol user',
        'capabilities'=> 'enrol/manual:enrol',
        'type'        => 'write',
    ),

    'local_ws_campus_mgmt_unenrol_user' => array(
        'classname'   => 'local_ws_campus_mgmt_external',
        'methodname'  => 'unenrol_user',
        'description' => 'Manually unenrol user',
        'capabilities'=> 'enrol/manual:unenrol',
        'type'        => 'write',
    ),
);

$services = array(
        'Campus Management Web Services' => array(
                'functions' => array ('local_ws_campus_mgmt_get_user_language_level',
                                      'local_ws_campus_mgmt_get_user_all_languages_levels',
                                      'local_ws_campus_mgmt_enrol_user',
                                      'local_ws_campus_mgmt_unenrol_user'),
                'restrictedusers' => 1,
                'enabled'=>1,
                'shortname'=>'ws_campus_mgmt'
        )
);
