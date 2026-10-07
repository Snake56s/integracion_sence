<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Integración SENCE SIC';
$string['sence_integration:addinstance'] = 'Añadir un nuevo bloque de Integración SENCE';
$string['sence_integration:myaddinstance'] = 'Añadir un nuevo bloque de Integración SENCE al Área Personal';
$string['sence_integration:manage'] = 'Administrar configuraciones e IDs de SENCE';
$string['sence_integration:transmit'] = 'Transmitir avances manualmente a SENCE';

// Settings global
$string['settings_header'] = 'Configuración General de SENCE SIC';
$string['rut_otec'] = 'RUT OTEC';
$string['rut_otec_desc'] = 'RUT del OTEC sin puntos y con guión (ej: 75062000-3).';
$string['token_sence'] = 'Token de Autenticación SENCE';
$string['token_sence_desc'] = 'Token UUID en mayúsculas provisto por sistemas SENCE.';
$string['idsistema'] = 'ID de Sistema';
$string['idsistema_desc'] = 'ID del sistema ante SENCE (por defecto 1350).';
$string['api_base_url'] = 'URL Base API Gestor Intermedio';
$string['api_base_url_desc'] = 'Endpoint base de SENCE (por defecto: https://auladigital.sence.cl/gestor/API/avance-sic).';
$string['min_grade_approval'] = 'Nota mínima de aprobación (%)';
$string['min_grade_approval_desc'] = 'Porcentaje de nota mínima requerida (ej: 60 o 75 segun escala).';
$string['min_progress_approval'] = 'Progreso mínimo de aprobación (%)';
$string['min_progress_approval_desc'] = 'Porcentaje de avance obligatorio completado requerido (ej: 75 o 100).';

// Block strings
$string['block_title'] = 'Integración SENCE';
$string['manage_sence_ids'] = 'Gestionar IDs SENCE del Curso';
$string['test_connection'] = 'Probar Conexión';
$string['send_progress_now'] = 'Enviar Avance Ahora';
$string['view_logs'] = 'Ver Historial de Envíos';
$string['registered_ids_count'] = 'Códigos SENCE registrados en este curso: {$a}';
$string['no_sence_codes'] = 'No hay códigos SENCE configurados para este curso. Haga clic en Gestionar para asociar códigos de oferta/sección o grupos.';

// Form strings
$string['codigo_oferta'] = 'Código Oferta SENCE';
$string['codigo_oferta_help'] = 'Código de la oferta SENCE (ej: CAP-21-01-13-9999-1).';
$string['codigo_grupo'] = 'Código Grupo / Sección';
$string['codigo_grupo_help'] = 'Código de la sección SENCE asociado.';
$string['codigo_envio'] = 'Código de Envío (Opcional)';
$string['moodle_group'] = 'Grupo / Cohorte Moodle asociada';
$string['all_course_students'] = '-- Todo el curso (Sin grupo) --';
$string['add_new_sence_id'] = 'Agregar nuevo ID SENCE a este curso';
$string['save_changes'] = 'Guardar Cambios';
$string['delete_confirm'] = '¿Está seguro de eliminar este registro SENCE?';
$string['action_success'] = 'Operación realizada con éxito.';
$string['transmission_success'] = 'Transmisión completada exitosamente. ID Proceso: {$a}';
$string['transmission_error'] = 'Error en la transmisión a SENCE: {$a}';
