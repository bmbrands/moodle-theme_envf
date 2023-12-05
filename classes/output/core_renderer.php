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
 * Presets management
 *
 * @package   theme_clboost
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_envf\output;

use action_menu;
use coding_exception;
use html_writer;
use moodle_url;
use navigation_node;
use stdClass;
use theme_envf\local\utils;

/**
 * Renderers to align Moodle's HTML with that expected by Bootstrap
 *
 *
 * @package   theme_envf
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class core_renderer extends \theme_clboost\output\core_renderer {
    /**
     * Return false (no compact logo)
     *
     * @param int $maxwidth The maximum width, or null when the maximum width does not matter.
     * @param int $maxheight The maximum height, or null when the maximum height does not matter.
     * @return moodle_url|false
     */
    public function get_compact_logo_url($maxwidth = 300, $maxheight = 300) {
        return $this->get_logo_url($maxwidth, $maxheight); // No compact logo here.
    }

    /**
     * Get additional global information that can be used in this template
     *
     * @return stdClass
     * @throws coding_exception
     */
    public function get_template_additional_information() {
        $additionalinfo = parent::get_template_additional_information();
        $additionalinfo->orglist = utils::convert_address_config($this->page);
        $additionalinfo->legallinks = utils::convert_legallinks_config();
        $attributes = ['rel' => 'stylesheet', 'type' => 'text/css'];
        $urls = $this->page->theme->css_urls($this->page);
        $code = '';
        foreach ($urls as $url) {
            $attributes['href'] = $url;
            $code .= html_writer::empty_tag('link', $attributes);
            // This id is needed in first sheet only so that theme may override YUI sheets loaded on the fly.
            unset($attributes['id']);
        }
        $additionalinfo->h5p_extra_css = $code;
        return $additionalinfo;
    }

    // Menus.

    /**
     * MCMS Menus
     *
     * @return mixed
     */
    public function mcms_menu() {
        $renderer = $this->page->get_renderer('local_mcms', 'menu');
        return $renderer->mcms_menu();
    }

    /**
     * We want to show the custom menus as a list of links in the footer on small screens.
     * Just return the menu object exported so we can render it differently.
     */
    public function mcms_menu_menu_flat() {
        $renderer = $this->page->get_renderer('local_mcms', 'menu');
        return $renderer->mcms_menu_menu_flat();
    }

    /**
     * We want to show the custom menus as a list of links in the footer on small screens.
     * Just return the menu object exported so we can render it differently.
     */
    public function mcms_menu_menu_mobile() {
        $renderer = $this->page->get_renderer('local_mcms', 'menu');
        return $renderer->mcms_menu_menu_mobile();
    }

    /**
     * This is an optional menu that can be added to a layout by a theme. It contains the
     * menu for the most specific thing from the settings block. E.g. Module administration.
     *
     * @return string
     */
    public function region_main_settings_menu() {
        global $CFG;
        $context = $this->page->context;
        $menu = new action_menu();

        if ($context->contextlevel == CONTEXT_MODULE) {

            $this->page->navigation->initialise();
            $node = $this->page->navigation->find_active_node();
            $buildmenu = false;
            // If the settings menu has been forced then show the menu.
            if ($this->page->is_settings_menu_forced()) {
                $buildmenu = true;
            } else if (!empty($node) && ($node->type == navigation_node::TYPE_ACTIVITY ||
                    $node->type == navigation_node::TYPE_RESOURCE)) {

                $items = $this->page->navbar->get_items();
                $navbarnode = end($items);
                // We only want to show the menu on the first page of the activity. This means
                // the breadcrumb has no additional nodes.
                if ($navbarnode && ($navbarnode->key === $node->key && $navbarnode->type == $node->type)) {
                    $buildmenu = true;
                }
            }
            // ENVF Modifications.
            require_once($CFG->dirroot.'/course/lib.php');
            $courseformat = course_get_format($this->page->course);
            $buildmenu = $buildmenu && (
                (($courseformat->get_format() == 'envfpsup') &&
                    has_capability('moodle/grade:viewall', $context))
                || ($courseformat->get_format() != 'envfpsup')
                );
            // END ENVF Modifications.
            if ($buildmenu) {
                // Get the course admin node from the settings navigation.
                $node = $this->page->settingsnav->find('modulesettings', navigation_node::TYPE_SETTING);
                if ($node) {
                    // Build an action menu based on the visible nodes from this navigation tree.
                    $this->build_action_menu_from_navigation($menu, $node);
                }
            }

        } else if ($context->contextlevel == CONTEXT_COURSECAT) {
            // For course category context, show category settings menu, if we're on the course category page.
            if ($this->page->pagetype === 'course-index-category') {
                $node = $this->page->settingsnav->find('categorysettings', navigation_node::TYPE_CONTAINER);
                if ($node) {
                    // Build an action menu based on the visible nodes from this navigation tree.
                    $this->build_action_menu_from_navigation($menu, $node);
                }
            }

        } else {
            $items = $this->page->navbar->get_items();
            $navbarnode = end($items);

            if ($navbarnode && ($navbarnode->key === 'participants')) {
                $node = $this->page->settingsnav->find('users', navigation_node::TYPE_CONTAINER);
                if ($node) {
                    // Build an action menu based on the visible nodes from this navigation tree.
                    $this->build_action_menu_from_navigation($menu, $node);
                }

            }
        }
        return $this->render($menu);
    }

    /**
     * Render the Tools menu in the navbar.
     * @return string HTML containing the rendered menu.
     */
    public function toolsmenu() {
        global $PAGE;
        $template = new \stdClass();
        $template->menuitems = [];

        if ($PAGE->primarynav) {
            foreach ($PAGE->primarynav->children as $node) {
                if ($node->icon && $node->icon->pix == 'i/navigationitem') {
                    $node->icon->pix = 'book';
                }
                $template->menuitems[] = [
                    'id' => $node->key,
                    'url' => $node->action,
                    'text' => $node->text,
                    'icon' => $this->render($node->icon),
                    'color' => 'primary',
                    'newwindow' => false
                ];
            }
        }
        // Add the secondary navigation items on these page layouts
        $pagelayouts = ['mycourses', 'my-index', 'frontpage', 'admin'];
        $secondarynavitems = ['editsettings', 'participants', 'coursereports', 'questionbank', 'contentbank'];
        if (in_array($PAGE->pagelayout, $pagelayouts) && $PAGE->secondarynav) {
            foreach ($PAGE->secondarynav->children as $node) {
                if (!in_array($node->key, $secondarynavitems)) {
                    continue;
                }
                if ($node->icon && $node->icon->pix == 'i/navigationitem') {
                    $node->icon->pix = 'book';
                }
                $template->menuitems[] = [
                    'id' => $node->key,
                    'url' => $node->action,
                    'text' => $node->text,
                    'icon' => $this->render($node->icon),
                    'color' => 'primary',
                    'newwindow' => false
                ];
            }
        }

        // Look for plugins adding menu items.
        $callback = get_plugins_with_function('theme_envf_tools_menu_items');
        foreach ($callback as $plugin => $function) {
            $template->menuitems = array_merge($template->menuitems, $function());
        }
        $template->hasitems = !empty($template->menuitems);
        return $this->render_from_template('theme_envf/toolsmenu', $template);
    }
}
