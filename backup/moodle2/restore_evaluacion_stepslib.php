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
 * Define all the restore steps that will be used by the restore_evaluacion_activity_task
 *
 * @package    mod_evaluacion
 * @copyright  2010 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Structure step to restore one evaluacion activity
 */
class restore_evaluacion_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {
        $paths = array();

        $paths[] = new restore_path_element('evaluacion', '/activity/evaluacion');
        $paths[] = new restore_path_element('evaluacion_chapter', '/activity/evaluacion/chapters/chapter');
        $paths[] = new restore_path_element('evaluacion_chapter_tag', '/activity/evaluacion/chaptertags/tag');

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Process evaluacion tag information
     * @param array $data information
     */
    protected function process_evaluacion($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        // Any changes to the list of dates that needs to be rolled should be same during course restore and course reset.
        // See MDL-9367.

        $newitemid = $DB->insert_record('evaluacion', $data);
        $this->apply_activity_instance($newitemid);
    }

    /**
     * Process chapter tag information
     * @param array $data information
     */
    protected function process_evaluacion_chapter($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $data->evaluacionid = $this->get_new_parentid('evaluacion');

        $newitemid = $DB->insert_record('evaluacion_chapters', $data);
        $this->set_mapping('evaluacion_chapter', $oldid, $newitemid, true);
    }

    protected function process_evaluacion_chapter_tag($data) {
        $data = (object)$data;

        if (!core_tag_tag::is_enabled('mod_evaluacion', 'evaluacion_chapters')) { // Tags disabled in server, nothing to process.
            return;
        }

        $tag = $data->rawname;

        if (!$itemid = $this->get_mappingid('evaluacion_chapter', $data->itemid)) {
            return;
        }

        $context = context_module::instance($this->task->get_moduleid());
        core_tag_tag::add_item_tag('mod_evaluacion', 'evaluacion_chapters', $itemid, $context, $tag);
    }

    protected function after_execute() {
        global $DB;

        // Add evaluacion related files
        $this->add_related_files('mod_evaluacion', 'intro', null);
        $this->add_related_files('mod_evaluacion', 'chapter', 'evaluacion_chapter');
    }
}
