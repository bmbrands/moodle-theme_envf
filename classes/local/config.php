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
 * All constant in one place
 *
 * @package   theme_envf
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_envf\local;
use local_mcms\page_utils;

/**
 * Theme constants. In one place.
 *
 * @package   theme_envf
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class config extends \theme_clboost\local\config {
    public static function get_layouts() {
        $layouts = parent::get_layouts();
        $layouts[page_utils::PAGE_LAYOUT_NAME] = array(
                'file' => 'mcmspage.php',
                'regions' => array('content'),
                'defaultregion' => 'content',
        );
        $layouts['mydashboard'] = array(
                'file' => 'columns2.php',
                'regions' => array('side-pre'),
                'defaultregion' => 'side-pre',
                'options' => array('nonavbar' => true, 'langmenu' => true),
        );
        return $layouts;
    }
}
