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
 * block_trainingmatrix_teacher block.
 *
 * @package    block_trainingmatrix_teacher
 * @copyright  2026 LMS-Labs
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3 or later
 */

/**
 * Staff training oversight block for users who can view all training records.
 *
 * @package    block_trainingmatrix_teacher
 * @copyright  2026 LMS-Labs
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3 or later
 */
class block_trainingmatrix_teacher extends block_base {
    /**
     * Initialise the block title.
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_trainingmatrix_teacher');
    }

    /**
     * Build the block content. Users without oversight permission get an empty (hidden) block.
     *
     * @return stdClass
     */
    public function get_content() {
        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';

        if (!isloggedin() || isguestuser()) {
            return $this->content;
        }
        if (!has_capability('local/trainingmatrix:viewall', context_system::instance())) {
            return $this->content;
        }

        $this->content->text = \local_trainingmatrix\output\training_cards::teacher();
        return $this->content;
    }

    /**
     * Where the block may be added.
     *
     * @return array
     */
    public function applicable_formats() {
        return [
            'all' => false,
            'site' => true,
            'site-index' => true,
            'my' => true,
        ];
    }

    /**
     * Only one instance per page.
     *
     * @return bool
     */
    public function instance_allow_multiple() {
        return false;
    }

    /**
     * No global settings.
     *
     * @return bool
     */
    public function has_config() {
        return false;
    }
}
