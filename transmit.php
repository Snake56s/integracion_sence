<?php
// This file is part of Moodle - http://moodle.org/

require_once(__DIR__ . '/../../config.php');
use block_sence_integration\data_builder;
use block_sence_integration\client;

$courseid = required_param('courseid', PARAM_INT);
$mappingid = optional_param('mappingid', 0, PARAM_INT);
$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
$context = context_course::instance($course->id);

require_login($course);
require_capability('block/sence_integration:transmit', $context);
require_sesskey();

$PAGE->set_url(new moodle_url('/blocks/sence_integration/transmit.php', ['courseid' => $course->id]));
$PAGE->set_context($context);
$PAGE->set_title('Transmisión Manual SENCE');
$PAGE->set_heading($course->fullname . ': Transmisión SENCE');

echo $OUTPUT->header();

if ($mappingid > 0) {
    $mappings = $DB->get_records('block_sence_courses', ['id' => $mappingid, 'courseid' => $course->id, 'enabled' => 1]);
} else {
    $mappings = $DB->get_records('block_sence_courses', ['courseid' => $course->id, 'enabled' => 1]);
}

if (empty($mappings)) {
    echo $OUTPUT->notification('No hay códigos SENCE configurados en este curso.', 'warning');
} else {
    echo html_writer::tag('h3', 'Resultado del Envío a SENCE Gestor Intermedio');

    foreach ($mappings as $mapping) {
        echo html_writer::tag('h4', "Transmisión: Oferta {$mapping->codigo_oferta} - Sección {$mapping->codigo_grupo}", ['class' => 'mt-3']);

        $payload = data_builder::build_course_payload($course, $mapping);

        if (empty($payload['listaAlumnos'])) {
            echo $OUTPUT->notification("No se encontraron alumnos con RUT (Número de ID) válido asociados a este grupo/curso.", 'warning');
            continue;
        }

        $res = client::send_progress($payload);

        // Save log
        $log = new \stdClass();
        $log->courseid = $course->id;
        $log->sence_course_id = $mapping->id;
        $log->id_proceso = !empty($res['data']['id_proceso']) ? (string)$res['data']['id_proceso'] : null;
        $log->status = ($res['success'] && empty($res['data']['datosError'])) ? 'success' : 'error';
        $log->payload = json_encode($payload, JSON_UNESCAPED_UNICODE);
        $log->response = !empty($res['raw_response']) ? $res['raw_response'] : json_encode($res);
        $log->timecreated = time();
        $DB->insert_record('block_sence_logs', $log);

        if ($res['success']) {
            echo $OUTPUT->notification("Proceso completado exitosamente con SENCE. ID Proceso: " . ($log->id_proceso ?? 'N/A'), 'success');
        } else {
            echo $OUTPUT->notification("Error en la llamada a la API: " . ($res['error'] ?? 'Código HTTP ' . $res['http_code']), 'error');
        }

        echo '<div class="accordion mb-3" id="acc' . $mapping->id . '">';
        echo '  <div class="accordion-item">';
        echo '    <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#col' . $mapping->id . '">Ver Detalle JSON Enviado y Respuesta</button></h2>';
        echo '    <div id="col' . $mapping->id . '" class="accordion-collapse collapse p-3">';
        echo '      <h5>Payload Enviado:</h5><pre style="background: #f8f9fa; padding: 10px; max-height: 250px; overflow: auto;">' . s(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</pre>';
        echo '      <h5>Respuesta del Gestor SENCE:</h5><pre style="background: #f8f9fa; padding: 10px; max-height: 250px; overflow: auto;">' . s($res['raw_response'] ?? json_encode($res, JSON_PRETTY_PRINT)) . '</pre>';
        echo '    </div>';
        echo '  </div>';
        echo '</div>';
    }
}

echo html_writer::link(new moodle_url('/course/view.php', ['id' => $course->id]), '<i class="fa fa-arrow-left"></i> Volver al Curso', ['class' => 'btn btn-primary mt-3']);

echo $OUTPUT->footer();
