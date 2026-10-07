<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'SENCE SIC Integration';
$string['sence_integration:addinstance'] = 'Add a new SENCE Integration block';
$string['sence_integration:myaddinstance'] = 'Add a new SENCE Integration block to Dashboard';
$string['sence_integration:manage'] = 'Manage SENCE configurations and IDs';
$string['sence_integration:transmit'] = 'Transmit progress manually to SENCE';

// Settings global
$string['settings_header'] = 'General SENCE SIC Settings';
$string['rut_otec'] = 'OTEC RUT';
$string['rut_otec_desc'] = 'OTEC RUT without dots and with hyphen (e.g., 75062000-3).';
$string['token_sence'] = 'SENCE Authentication Token';
$string['token_sence_desc'] = 'UUID token in uppercase provided by SENCE systems.';
$string['idsistema'] = 'System ID';
$string['idsistema_desc'] = 'System ID for SENCE (default 1350).';
$string['api_base_url'] = 'Intermediate Manager API Base URL';
$string['api_base_url_desc'] = 'SENCE Base endpoint (default: https://auladigital.sence.cl/gestor/API/avance-sic).';
$string['min_grade_approval'] = 'Minimum approval grade (%)';
$string['min_grade_approval_desc'] = 'Minimum required grade percentage (e.g. 60).';
$string['min_progress_approval'] = 'Minimum approval progress (%)';
$string['min_progress_approval_desc'] = 'Minimum required mandatory progress (e.g. 75).';

// Block strings
$string['block_title'] = 'SENCE Integration';
$string['manage_sence_ids'] = 'Manage Course SENCE IDs';
$string['test_connection'] = 'Test Connection';
$string['send_progress_now'] = 'Send Progress Now';
$string['view_logs'] = 'View Transmission Logs';
$string['registered_ids_count'] = 'SENCE codes registered in this course: {$a}';
$string['no_sence_codes'] = 'No SENCE codes configured for this course. Click Manage to link offer/section codes or groups.';

// Form strings
$string['codigo_oferta'] = 'SENCE Offer Code';
$string['codigo_oferta_help'] = 'SENCE Offer Code (e.g. CAP-21-01-13-9999-1).';
$string['codigo_grupo'] = 'Group / Section Code';
$string['codigo_grupo_help'] = 'Associated SENCE Section Code.';
$string['codigo_envio'] = 'Transmission Code (Optional)';
$string['moodle_group'] = 'Associated Moodle Group / Cohort';
$string['all_course_students'] = '-- Whole course (No group) --';
$string['add_new_sence_id'] = 'Add new SENCE ID to this course';
$string['save_changes'] = 'Save Changes';
$string['delete_confirm'] = 'Are you sure you want to delete this SENCE record?';
$string['action_success'] = 'Operation completed successfully.';
$string['transmission_success'] = 'Transmission completed successfully. Process ID: {$a}';
$string['transmission_error'] = 'Error transmitting to SENCE: {$a}';
