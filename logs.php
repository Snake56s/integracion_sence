<?php
// This file is part of Moodle - http://moodle.org/

require_once(__DIR__ . '/../../config.php');

$courseid = required_param('courseid', PARAM_INT);
$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
$context = context_course::instance($course->id);

require_login($course);
require_capability('block/sence_integration:manage', $context);

$PAGE->set_url(new moodle_url('/blocks/sence_integration/logs.php', ['courseid' => $course->id]));
$PAGE->set_context($context);
$PAGE->set_title('Historial de Envíos SENCE');
$PAGE->set_heading($course->fullname . ': Historial SENCE');

echo $OUTPUT->header();
echo html_writer::tag('h3', 'Historial de Transmisiones SENCE');

$logs = $DB->get_records('block_sence_logs', ['courseid' => $course->id], 'timecreated DESC', '*', 0, 50);

if (empty($logs)) {
    echo $OUTPUT->notification('No hay registros de transmisiones para este curso.', 'info');
} else {
    $table = new html_table();
    $table->head = ['Fecha', 'ID Proceso', 'Estado', 'Respuesta / Detalles'];

    foreach ($logs as $l) {
        $badge = ($l->status === 'success') ? '<span class="badge bg-success">Éxito</span>' : '<span class="badge bg-danger">Error</span>';
        $time = userdate($l->timecreated);
        $proc = s($l->id_proceso ?? 'N/A');

        $details = '<details><summary>Ver Respuesta</summary><pre style="font-size:0.75rem; max-height:150px; overflow:auto;">' . s($l->response) . '</pre></details>';

        $table->data[] = [
            $time,
            $proc,
            $badge,
            $details
        ];
    }

    echo html_writer::table($table);
}

echo html_writer::link(new moodle_url('/course/view.php', ['id' => $course->id]), '<i class="fa fa-arrow-left"></i> Volver al Curso', ['class' => 'btn btn-secondary mt-3']);

echo $OUTPUT->footer();
