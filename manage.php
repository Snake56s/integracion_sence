<?php
// This file is part of Moodle - http://moodle.org/

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

$courseid = required_param('courseid', PARAM_INT);
$action   = optional_param('action', '', PARAM_ALPHA);
$id       = optional_param('id', 0, PARAM_INT);

$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
$context = context_course::instance($course->id);

require_login($course);
require_capability('block/sence_integration:manage', $context);

$PAGE->set_url(new moodle_url('/blocks/sence_integration/manage.php', ['courseid' => $course->id]));
$PAGE->set_context($context);
$PAGE->set_title(get_string('manage_sence_ids', 'block_sence_integration'));
$PAGE->set_heading($course->fullname . ': ' . get_string('manage_sence_ids', 'block_sence_integration'));

// Handle Delete Action
if ($action === 'delete' && $id && confirm_sesskey()) {
    $DB->delete_records('block_sence_courses', ['id' => $id, 'courseid' => $course->id]);
    redirect(new moodle_url('/blocks/sence_integration/manage.php', ['courseid' => $course->id]), get_string('action_success', 'block_sence_integration'), null, \core\output\notification::NOTIFY_SUCCESS);
}

// Handle Form Submission (Add / Edit)
if ($data = data_submitted() && confirm_sesskey()) {
    $codigo_oferta = required_param('codigo_oferta', PARAM_TEXT);
    $codigo_grupo  = required_param('codigo_grupo', PARAM_TEXT);
    $codigo_envio  = optional_param('codigo_envio', '', PARAM_TEXT);
    $groupid       = optional_param('groupid', 0, PARAM_INT);
    $rut_otec      = optional_param('rut_otec', '', PARAM_TEXT);
    $token_sence   = optional_param('token_sence', '', PARAM_TEXT);

    $record = new stdClass();
    $record->courseid = $course->id;
    $record->groupid = $groupid;
    $record->codigo_oferta = trim($codigo_oferta);
    $record->codigo_grupo = trim($codigo_grupo);
    $record->codigo_envio = trim($codigo_envio);
    $record->rut_otec = trim($rut_otec);
    $record->token_sence = trim($token_sence);
    $record->enabled = 1;
    $record->timemodified = time();

    if ($id > 0) {
        $record->id = $id;
        $DB->update_record('block_sence_courses', $record);
    } else {
        $record->timecreated = time();
        $DB->insert_record('block_sence_courses', $record);
    }

    redirect(new moodle_url('/blocks/sence_integration/manage.php', ['courseid' => $course->id]), get_string('action_success', 'block_sence_integration'), null, \core\output\notification::NOTIFY_SUCCESS);
}

// Render Page
echo $OUTPUT->header();

$records = $DB->get_records('block_sence_courses', ['courseid' => $course->id]);
$groups = groups_get_all_groups($course->id);

echo html_writer::tag('h3', get_string('manage_sence_ids', 'block_sence_integration'));
echo html_writer::tag('p', 'En esta sección puedes vincular uno o varios códigos de oferta y sección SENCE a este curso o dividirlos por grupos de participantes.');

// Table of current mappings
if (!empty($records)) {
    $table = new html_table();
    $table->head = [
        'Código Oferta',
        'Código Grupo / Sección',
        'Código Envío',
        'Grupo Asociado',
        'RUT OTEC / Token Personalizado',
        'Acciones'
    ];
    foreach ($records as $r) {
        $grp_text = get_string('all_course_students', 'block_sence_integration');
        if (!empty($r->groupid) && isset($groups[$r->groupid])) {
            $grp_text = format_string($groups[$r->groupid]->name);
        }

        $custom_cred = (!empty($r->rut_otec) ? s($r->rut_otec) : '<i>Por defecto global</i>');

        $delete_url = new moodle_url('/blocks/sence_integration/manage.php', [
            'courseid' => $course->id,
            'action' => 'delete',
            'id' => $r->id,
            'sesskey' => sesskey()
        ]);
        $del_btn = html_writer::link($delete_url, '<i class="fa fa-trash"></i> Eliminar', [
            'class' => 'btn btn-sm btn-outline-danger',
            'onclick' => 'return confirm("' . get_string('delete_confirm', 'block_sence_integration') . '");'
        ]);

        $table->data[] = [
            s($r->codigo_oferta),
            s($r->codigo_grupo),
            s($r->codigo_envio),
            $grp_text,
            $custom_cred,
            $del_btn
        ];
    }
    echo html_writer::table($table);
}

// Add New Mapping Form
echo html_writer::tag('h4', get_string('add_new_sence_id', 'block_sence_integration'), ['class' => 'mt-4']);

?>
<form method="post" action="<?php echo $PAGE->url; ?>" class="card p-4 bg-light">
    <input type="hidden" name="sesskey" value="<?php echo sesskey(); ?>">
    <input type="hidden" name="id" value="0">

    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label"><strong><?php echo get_string('codigo_oferta', 'block_sence_integration'); ?> *</strong></label>
            <input type="text" name="codigo_oferta" class="form-control" placeholder="Ej: CAP-21-01-13-9999-1" required>
        </div>
        <div class="col-md-6">
            <label class="form-label"><strong><?php echo get_string('codigo_grupo', 'block_sence_integration'); ?> *</strong></label>
            <input type="text" name="codigo_grupo" class="form-control" placeholder="Ej: CAP-21-01-13-9999-1" required>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label"><strong><?php echo get_string('moodle_group', 'block_sence_integration'); ?></strong></label>
            <select name="groupid" class="form-select">
                <option value="0"><?php echo get_string('all_course_students', 'block_sence_integration'); ?></option>
                <?php foreach ($groups as $g): ?>
                    <option value="<?php echo $g->id; ?>"><?php echo format_string($g->name); ?></option>
                <?php endforeach; ?>
            </select>
            <small class="form-text text-muted">Permite asignar este ID SENCE a un grupo específico de alumnos.</small>
        </div>
        <div class="col-md-6">
            <label class="form-label"><strong><?php echo get_string('codigo_envio', 'block_sence_integration'); ?></strong></label>
            <input type="text" name="codigo_envio" class="form-control" placeholder="Opcional: ID de envío interno">
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label">RUT OTEC (Opcional si usa el global)</label>
            <input type="text" name="rut_otec" class="form-control" placeholder="Ej: 75062000-3">
        </div>
        <div class="col-md-6">
            <label class="form-label">Token SENCE (Opcional si usa el global)</label>
            <input type="text" name="token_sence" class="form-control" placeholder="UUID en mayúsculas">
        </div>
    </div>

    <button type="submit" class="btn btn-primary mt-2">
        <i class="fa fa-plus-circle"></i> <?php echo get_string('add_new_sence_id', 'block_sence_integration'); ?>
    </button>
</form>
<?php

echo html_writer::link(new moodle_url('/course/view.php', ['id' => $course->id]), '<i class="fa fa-arrow-left"></i> Volver al Curso', ['class' => 'btn btn-secondary mt-3']);

echo $OUTPUT->footer();
