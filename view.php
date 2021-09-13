<?php 

require_once('../../config.php');
require_once('lib.php');
// require_once($CFG->dirroot.'/mod/page/lib.php');
// require_once($CFG->dirroot.'/mod/page/locallib.php');
// require_once($CFG->libdir.'/completionlib.php');

$id = required_param('id', PARAM_INT);    // Course Module ID

if (!$cm = get_coursemodule_from_id('evaluacion', $id)) {
    print_error('Course Module ID was incorrect'); // NOTE this is invalid use of print_error, must be a lang string id
}
if (!$course = $DB->get_record('course', array('id'=> $cm->course))) {
    print_error('course is misconfigured');  // NOTE As above
}
if (!$evaluacion = $DB->get_record('evaluacion', array('id'=> $cm->instance))) {
    print_error('course module is incorrect'); // NOTE As above
}

global $DB, $USER, $CFG;

require_login();

$strpages = get_string('modulenameplural', 'evaluacion');

$context = context_system::instance();
$PAGE->set_url(new moodle_url('/mod/evaluacion/index.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title('Evaluacion');
$PAGE->set_heading('Evaluacion');

$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
require_course_login($course, true, $cm);

$templateContext = (object)[
    'sesskey' => sesskey(),
    'cursoid' => $cm->course,
    'coursemoduleid' => $id,
    'userid' => $USER->id,
];


echo $OUTPUT->header();
echo $OUTPUT->render_from_template('mod_evaluacion/evaluacion', $templateContext);
echo $OUTPUT->footer();