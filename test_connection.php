<?php
// This file is part of Moodle - http://moodle.org/

require_once(__DIR__ . '/../../config.php');
use block_sence_integration\client;

$courseid = required_param('courseid', PARAM_INT);
$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
$context = context_course::instance($course->id);

require_login($course);
require_capability('block/sence_integration:manage', $context);
require_sesskey();

$PAGE->set_url(new moodle_url('/blocks/sence_integration/test_connection.php', ['courseid' => $course->id]));
$PAGE->set_context($context);
$PAGE->set_title('Prueba de Conexión SENCE');
$PAGE->set_heading($course->fullname . ': Prueba SENCE');

echo $OUTPUT->header();
echo html_writer::tag('h3', 'Prueba de Conexión al Gestor Intermedio SENCE');

$res = client::test_connection();

if ($res['success']) {
    echo $OUTPUT->notification("Conexión con el servidor SENCE exitosa (HTTP 200).", 'success');
} else {
    echo $OUTPUT->notification("Error de conexión: " . ($res['error'] ?? 'Código HTTP: ' . $res['http_code']), 'error');
}

echo '<h5>Respuesta del Servicio:</h5>';
echo '<pre style="background: #f8f9fa; padding: 15px; border-radius: 5px;">' . s(is_array($res['response']) ? json_encode($res['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $res['response']) . '</pre>';

echo html_writer::link(new moodle_url('/course/view.php', ['id' => $course->id]), '<i class="fa fa-arrow-left"></i> Volver al Curso', ['class' => 'btn btn-secondary mt-3']);

echo $OUTPUT->footer();
