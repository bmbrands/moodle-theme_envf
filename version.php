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

defined('MOODLE_INTERNAL') || die;

$plugin->version   = 2023091702; /* This is the version number to increment when changes needing an update are made */
$plugin->requires  = 2019111800;
$plugin->release   = '0.1.0';
$plugin->maturity  = MATURITY_ALPHA;
$plugin->component = 'theme_envf';
$plugin->dependencies = [
    'auth_psup' => ANY_VERSION,
    'block_mcms' => ANY_VERSION,
    'block_sponsors' => ANY_VERSION,
    'block_featured_courses' => ANY_VERSION,
    'block_thumblinks_action' => ANY_VERSION,
    'block_faq' => ANY_VERSION,
    'format_envfpsup' => ANY_VERSION,
    'filter_envf' => ANY_VERSION,
    'local_mcms' => ANY_VERSION,
    'local_dash_by_role' => ANY_VERSION,
    'mod_questionnaire' => ANY_VERSION,
    'mod_customcert' => ANY_VERSION,
    'report_customsql' => ANY_VERSION,
    'theme_boost' => ANY_VERSION,
    'theme_clboost' => ANY_VERSION,
    'tool_gdpr_plus' => ANY_VERSION
];
