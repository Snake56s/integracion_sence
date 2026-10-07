<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_heading(
        'block_sence_integration/general_settings',
        get_string('settings_header', 'block_sence_integration'),
        ''
    ));

    $settings->add(new admin_setting_configtext(
        'block_sence_integration/rut_otec',
        get_string('rut_otec', 'block_sence_integration'),
        get_string('rut_otec_desc', 'block_sence_integration'),
        '',
        PARAM_TEXT
    ));

    $settings->add(new admin_setting_configtext(
        'block_sence_integration/token_sence',
        get_string('token_sence', 'block_sence_integration'),
        get_string('token_sence_desc', 'block_sence_integration'),
        '',
        PARAM_TEXT
    ));

    $settings->add(new admin_setting_configtext(
        'block_sence_integration/idsistema',
        get_string('idsistema', 'block_sence_integration'),
        get_string('idsistema_desc', 'block_sence_integration'),
        '1350',
        PARAM_INT
    ));

    $settings->add(new admin_setting_configtext(
        'block_sence_integration/api_base_url',
        get_string('api_base_url', 'block_sence_integration'),
        get_string('api_base_url_desc', 'block_sence_integration'),
        'https://auladigital.sence.cl/gestor/API/avance-sic',
        PARAM_URL
    ));

    $settings->add(new admin_setting_configtext(
        'block_sence_integration/min_grade_approval',
        get_string('min_grade_approval', 'block_sence_integration'),
        get_string('min_grade_approval_desc', 'block_sence_integration'),
        '60',
        PARAM_INT
    ));

    $settings->add(new admin_setting_configtext(
        'block_sence_integration/min_progress_approval',
        get_string('min_progress_approval', 'block_sence_integration'),
        get_string('min_progress_approval_desc', 'block_sence_integration'),
        '75',
        PARAM_INT
    ));
}
