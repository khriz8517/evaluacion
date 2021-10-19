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
 * Description of evaluacion restore task
 *
 * @package    mod_evaluacion
 * @copyright  2010 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/evaluacion/backup/moodle2/restore_evaluacion_stepslib.php'); // Because it exists (must)

class restore_evaluacion_activity_task extends restore_activity_task {

    /**
     * Define (add) particular settings this activity can have
     *
     * @return void
     */
    protected function define_my_settings() {
        // No particular settings for this activity
    }

    /**
     * Define (add) particular steps this activity can have
     *
     * @return void
     */
    protected function define_my_steps() {
        // Choice only has one structure step
        $this->add_step(new restore_evaluacion_activity_structure_step('evaluacion_structure', 'evaluacion.xml'));
    }

    /**
     * Define the contents in the activity that must be
     * processed by the link decoder
     *
     * @return array
     */
    static public function define_decode_contents() {
        $contents = array();

        $contents[] = new restore_decode_content('evaluacion', array('intro'), 'evaluacion');
        $contents[] = new restore_decode_content('evaluacion_chapters', array('content'), 'evaluacion_chapter');

        return $contents;
    }

    /**
     * Define the decoding rules for links belonging
     * to the activity to be executed by the link decoder
     *
     * @return array
     */
    static public function define_decode_rules() {
        $rules = array();

        // List of evaluacions in course
        $rules[] = new restore_decode_rule('evaluacionINDEX', '/mod/evaluacion/index.php?id=$1', 'course');

        // evaluacion by cm->id
        $rules[] = new restore_decode_rule('evaluacionVIEWBYID', '/mod/evaluacion/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('evaluacionVIEWBYIDCH', '/mod/evaluacion/view.php?id=$1&amp;chapterid=$2', array('course_module', 'evaluacion_chapter'));

        // evaluacion by evaluacion->id
        $rules[] = new restore_decode_rule('evaluacionVIEWBYB', '/mod/evaluacion/view.php?b=$1', 'evaluacion');
        $rules[] = new restore_decode_rule('evaluacionVIEWBYBCH', '/mod/evaluacion/view.php?b=$1&amp;chapterid=$2', array('evaluacion', 'evaluacion_chapter'));

        // Convert old evaluacion links MDL-33362 & MDL-35007
        $rules[] = new restore_decode_rule('evaluacionSTART', '/mod/evaluacion/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('evaluacionCHAPTER', '/mod/evaluacion/view.php?id=$1&amp;chapterid=$2', array('course_module', 'evaluacion_chapter'));

        return $rules;
    }

    /**
     * Define the restore log rules that will be applied
     * by the {@link restore_logs_processor} when restoring
     * evaluacion logs. It must return one array
     * of {@link restore_log_rule} objects
     *
     * @return array
     */
    static public function define_restore_log_rules() {
        $rules = array();

        $rules[] = new restore_log_rule('evaluacion', 'add', 'view.php?id={course_module}', '{evaluacion}');
        $rules[] = new restore_log_rule('evaluacion', 'update', 'view.php?id={course_module}&chapterid={evaluacion_chapter}', '{evaluacion}');
        $rules[] = new restore_log_rule('evaluacion', 'update', 'view.php?id={course_module}', '{evaluacion}');
        $rules[] = new restore_log_rule('evaluacion', 'view', 'view.php?id={course_module}&chapterid={evaluacion_chapter}', '{evaluacion}');
        $rules[] = new restore_log_rule('evaluacion', 'view', 'view.php?id={course_module}', '{evaluacion}');
        $rules[] = new restore_log_rule('evaluacion', 'print', 'tool/print/index.php?id={course_module}&chapterid={evaluacion_chapter}', '{evaluacion}');
        $rules[] = new restore_log_rule('evaluacion', 'print', 'tool/print/index.php?id={course_module}', '{evaluacion}');
        $rules[] = new restore_log_rule('evaluacion', 'exportimscp', 'tool/exportimscp/index.php?id={course_module}', '{evaluacion}');
        // To convert old 'generateimscp' log entries
        $rules[] = new restore_log_rule('evaluacion', 'generateimscp', 'tool/generateimscp/index.php?id={course_module}', '{evaluacion}',
                'evaluacion', 'exportimscp', 'tool/exportimscp/index.php?id={course_module}', '{evaluacion}');
        $rules[] = new restore_log_rule('evaluacion', 'print chapter', 'tool/print/index.php?id={course_module}&chapterid={evaluacion_chapter}', '{evaluacion_chapter}');
        $rules[] = new restore_log_rule('evaluacion', 'update chapter', 'view.php?id={course_module}&chapterid={evaluacion_chapter}', '{evaluacion_chapter}');
        $rules[] = new restore_log_rule('evaluacion', 'add chapter', 'view.php?id={course_module}&chapterid={evaluacion_chapter}', '{evaluacion_chapter}');
        $rules[] = new restore_log_rule('evaluacion', 'view chapter', 'view.php?id={course_module}&chapterid={evaluacion_chapter}', '{evaluacion_chapter}');

        return $rules;
    }

    /**
     * Define the restore log rules that will be applied
     * by the {@link restore_logs_processor} when restoring
     * course logs. It must return one array
     * of {@link restore_log_rule} objects
     *
     * Note this rules are applied when restoring course logs
     * by the restore final task, but are defined here at
     * activity level. All them are rules not linked to any module instance (cmid = 0)
     *
     * @return array
     */
    static public function define_restore_log_rules_for_course() {
        $rules = array();

        $rules[] = new restore_log_rule('evaluacion', 'view all', 'index.php?id={course}', null);

        return $rules;
    }
}
