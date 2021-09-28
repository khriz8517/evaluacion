<?php

// use core_completion\progress;
// use core_course\external\course_summary_exporter;

error_reporting(E_ALL);
require_once(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->dirroot . '/enrol/externallib.php');

try {
	global $USER, $PAGE;
	$details = $_POST;
	$returnArr = array();

	if (!isset($_REQUEST['request_type']) || strlen($_REQUEST['request_type']) == false) {
		throw new Exception();
	}

	switch ($_REQUEST['request_type']) {
		case 'getPreguntasOpcionesEvaluacion':
			$returnArr = getPreguntasOpcionesEvaluacion();
			break;
		case 'insertResultadoEvaluacion':
			$puntaje = $_REQUEST['puntaje'];
			$cursoid = $_REQUEST['cursoid'];
			$coursemoduleid = $_REQUEST['coursemoduleid'];
			$module = $_REQUEST['module'];
			$sesskey = $_REQUEST['sesskey'];
			$returnArr = insertResultadoEvaluacion($puntaje, $cursoid, $coursemoduleid, $module, $sesskey);
			break;
	}

} catch (Exception $e) {
	$returnArr['status'] = false;
	$returnArr['data'] = $e->getMessage();
}

header('Content-type: application/json');

echo json_encode($returnArr);
exit();

/**
 * getPreguntasOpcionesEvaluacion
 * * obtiene las preguntas de la evaluacion y sus opciones
 */
function getPreguntasOpcionesEvaluacion(){
	global $DB, $USER;

	// $result = $DB->get_field('aq_eval_user_puntaje_data', 'puntaje_porcentaje', [
	// 	'userid' => $USER->id
	// ]);

	$data = [];

	$preguntas = $DB->get_records('aq_evaluacion_data', [
		'active' => 1
	]);

	foreach ($preguntas as $key => $value) {
		array_push($data, (object) array(
			'id' => $value->id,
			'pregunta' => $value->pregunta,
			'opciones' => $DB->get_records('aq_evaluacion_options_data',[
				'preguntaid' => $value->id,
				'active' => 1
			], null, 'id, opcion, preguntaid, is_valid, active')
		));
	}

	$output = [
		'preguntas' => $data,
		// 'result' => $result == false ? 0 : intval($result) 
	];

	return $output;
}

/**
 * insertResultadoEvaluacion
 * * guarda el resultado de la evaluacion del usuario
 * ! EL PUNTAJE ES PORCENTUAL
 * @param puntaje es el puntaje obtenido por el usuario 
 * @param sesskey es la sesion del usuario
 */
function insertResultadoEvaluacion($puntaje, $cursoid, $coursemoduleid, $module, $sesskey){
	global $DB, $USER;
	require_sesskey();

	$if_aproved = $DB->get_records_sql('SELECT * FROM mdl_aq_eval_user_puntaje_data
                where userid = '.$USER->id.'
                and moduleid = '.$coursemoduleid.'
                and module = '.$module.'
                and course = '.$cursoid.'
                and puntaje_porcentaje >= 80', []);

	if(count($if_aproved) == 0){
		$data = array(
			'userid' => $USER->id,
			'course' => $cursoid,
			'module' => $module,
			'moduleid' => $coursemoduleid,
			'puntaje_porcentaje' => $puntaje,
			'created_at' => time()
		);
		$insert_id = $DB->insert_record('aq_eval_user_puntaje_data', $data);
		return $insert_id;
	}
	return 'Ya se aprobo esta evaluacion';
}