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
 * Define all the backup steps that will be used by the backup_evaluacion_activity_task
 *
 * @package    mod_evaluacion
 * @copyright  2010 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Structure step to backup one evaluacion activity
 */
class backup_evaluacion_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // Define each element separated.
        $evaluacion = new backup_nested_element('evaluacion', array('id'), array(
            'name', 'intro', 'introformat', 'numbering', 'navstyle',
            'customtitles', 'timecreated', 'timemodified'));
        $chapters = new backup_nested_element('chapters');
        $chapter = new backup_nested_element('chapter', array('id'), array(
            'pagenum', 'subchapter', 'title', 'content', 'contentformat',
            'hidden', 'timemcreated', 'timemodified', 'importsrc'));

        $tags = new backup_nested_element('chaptertags');
        $tag = new backup_nested_element('tag', array('id'), array('itemid', 'rawname'));

        $evaluacion->add_child($chapters);
        $chapters->add_child($chapter);

        // Define sources
        $evaluacion->set_source_table('evaluacion', array('id' => backup::VAR_ACTIVITYID));
        $chapter->set_source_table('evaluacion_chapters', array('evaluacionid' => backup::VAR_PARENTID));

        // Define file annotations
        $evaluacion->annotate_files('mod_evaluacion', 'intro', null); // This file area hasn't itemid
        $chapter->annotate_files('mod_evaluacion', 'chapter', 'id');

        $evaluacion->add_child($tags);
        $tags->add_child($tag);

        // All these source definitions only happen if we are including user info.
        if (core_tag_tag::is_enabled('mod_evaluacion', 'evaluacion_chapters')) {
            $tag->set_source_sql('SELECT t.id, ti.itemid, t.rawname
                                    FROM {tag} t
                                    JOIN {tag_instance} ti ON ti.tagid = t.id
                                   WHERE ti.itemtype = ?
                                     AND ti.component = ?
                                     AND ti.contextid = ?', array(
                backup_helper::is_sqlparam('evaluacion_chapters'),
                backup_helper::is_sqlparam('mod_evaluacion'),
                backup::VAR_CONTEXTID));
        }

        // Return the root element (evaluacion), wrapped into standard activity structure
        return $this->prepare_activity_structure($evaluacion);
    }
}
