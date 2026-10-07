<?php
// This file is part of Moodle - http://moodle.org/

namespace block_sence_integration;

use stdClass;
use context_course;
use completion_info;
use grade_grade;
use grade_item;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->libdir . '/completionlib.php');

class data_builder {

    /**
     * Builds SENCE JSON Payload for a specific course and SENCE mapping configuration.
     *
     * @param stdClass $course Moodle course record
     * @param stdClass $sence_mapping Record from mdl_block_sence_courses
     * @return array
     */
    public static function build_course_payload(stdClass $course, stdClass $sence_mapping): array {
        global $DB, $CFG;

        $rut_otec = !empty($sence_mapping->rut_otec) ? $sence_mapping->rut_otec : get_config('block_sence_integration', 'rut_otec');
        $token = !empty($sence_mapping->token_sence) ? strtoupper($sence_mapping->token_sence) : strtoupper(get_config('block_sence_integration', 'token_sence'));
        $idsistema = (int)(get_config('block_sence_integration', 'idsistema') ?: 1350);

        $min_grade = (float)(get_config('block_sence_integration', 'min_grade_approval') ?: 60);
        $min_progress = (float)(get_config('block_sence_integration', 'min_progress_approval') ?: 75);

        // Get modules / sections of course.
        $modinfo = get_fast_modinfo($course);
        $sections = $modinfo->get_section_info_all();

        // Count Course Activities
        $cant_asinc = 0;
        $cant_sinc = 0;
        $module_definitions = [];

        foreach ($sections as $sectionnum => $section) {
            if ($sectionnum == 0 && empty($section->name)) {
                // General section might not be a SENCE module unless named
            }
            $section_name = trim($section->name ?? '');
            if (empty($section_name)) {
                $section_name = 'Modulo ' . $sectionnum;
            }

            $mod_asinc = 0;
            $mod_sinc = 0;
            $mod_activities = [];

            if (!empty($modinfo->sections[$sectionnum])) {
                foreach ($modinfo->sections[$sectionnum] as $cmid) {
                    $cm = $modinfo->cms[$cmid];
                    if (!$cm->uservisible && $cm->visible == 0) {
                        continue;
                    }

                    $act_name = mb_substr(strip_tags($cm->name), 0, 50);
                    $mod_activities[] = [
                        'cmid' => $cm->id,
                        'name' => $act_name,
                        'modname' => $cm->modname
                    ];

                    if ($cm->modname === 'bigbluebuttonbn' || $cm->modname === 'zoom' || $cm->modname === 'teams') {
                        $mod_sinc++;
                        $cant_sinc++;
                    } else {
                        $mod_asinc++;
                        $cant_asinc++;
                    }
                }
            }

            $module_definitions[$sectionnum] = [
                'name' => $section_name,
                'cant_asinc' => $mod_asinc,
                'cant_sinc' => $mod_sinc,
                'activities' => $mod_activities
            ];
        }

        // Fetch enrolled students belonging strictly to the designated Moodle Group
        if (!empty($sence_mapping->groupid)) {
            // Get members strictly in this Moodle group
            $group_members = groups_get_members($sence_mapping->groupid, 'u.*');
            $enrolled_users = $group_members ?: [];
        } else {
            // Whole course fallback
            $context = context_course::instance($course->id);
            $enrolled_users = get_enrolled_users($context, 'mod/assign:submit', 0, 'u.*');
        }

        $lista_alumnos = [];
        $today = date('Y-m-d');
        $completion = new completion_info($course);

        foreach ($enrolled_users as $user) {
            // Clean and split RUT from idnumber
            $idnumber = trim($user->idnumber ?? '');
            $clean_idnumber = str_replace(['.', ' ', '-'], '', $idnumber);
            if (empty($clean_idnumber)) {
                // If idnumber is empty, skip or try fallback
                continue;
            }

            $dv_alumno = strtoupper(substr($clean_idnumber, -1));
            $rut_alumno = (int)substr($clean_idnumber, 0, -1);

            // User enrollment dates
            $user_enrol = $DB->get_record_sql("
                SELECT ue.timestart, ue.timeend 
                FROM {user_enrolments} ue 
                JOIN {enrol} e ON e.id = ue.enrolid 
                WHERE e.courseid = ? AND ue.userid = ?
            ", [$course->id, $user->id], IGNORE_MULTIPLE);

            $timestart = ($user_enrol && !empty($user_enrol->timestart)) ? $user_enrol->timestart : $course->startdate;
            $timeend = ($user_enrol && !empty($user_enrol->timeend)) ? $user_enrol->timeend : $course->enddate;

            $fecha_inicio = !empty($timestart) ? date('Y-m-d', $timestart) : $today;
            $fecha_fin = !empty($timeend) ? date('Y-m-d', $timeend) : $today;

            // Connectivity time (seconds)
            $tiempo_conectividad = 1; // Default min > 0
            if ($DB->get_manager()->table_exists('block_dedication')) {
                $ded = $DB->get_record('block_dedication', ['userid' => $user->id, 'courseid' => $course->id]);
                if ($ded && !empty($ded->timespent)) {
                    $tiempo_conectividad = (int)$ded->timespent;
                }
            } else {
                // Fallback: estimate from logstore_standard_log
                $log_count = $DB->count_records('logstore_standard_log', ['courseid' => $course->id, 'userid' => $user->id]);
                $tiempo_conectividad = max(60, $log_count * 120);
            }

            // Overall Grade
            $course_item = grade_item::fetch_course_item($course->id);
            $grade_val = 0;
            if ($course_item) {
                $grade = new grade_grade(['itemid' => $course_item->id, 'userid' => $user->id], true);
                if ($grade && !is_null($grade->finalgrade)) {
                    $max = $course_item->grademax ?: 100;
                    $min = $course_item->grademin ?: 0;
                    $grade_val = round((($grade->finalgrade - $min) / ($max - $min)) * 100);
                }
            }

            // Overall Progress
            $course_complete = $completion->is_course_complete($user->id);
            $progress_percentage = 0;
            if ($completion->is_enabled()) {
                $progress_percentage = round($completion->get_user_progress($user->id, $course->id) ?? 0);
            }

            // Calculate Course State: 1 = Cursando, 2 = Aprobado, 3 = Reprobado
            $estado = 1;
            if ($grade_val >= $min_grade && $progress_percentage >= $min_progress) {
                $estado = 2; // Aprobado
            } else if (!empty($timeend) && $timeend < time()) {
                $estado = 3; // Reprobado
            }

            // Build Module list for this user
            $lista_modulos = [];
            foreach ($module_definitions as $secnum => $mod_def) {
                if (empty($mod_def['activities'])) {
                    continue;
                }

                $mod_time = max(1, round($tiempo_conectividad / max(1, count($module_definitions))));
                $mod_progress = $progress_percentage; // approximate or calculate per section items
                $mod_estado = $estado;
                $mod_nota = $grade_val;

                $act_list = [];
                foreach ($mod_def['activities'] as $act) {
                    $act_list[] = [
                        'codigoActividad' => $act['name']
                    ];
                }

                $lista_modulos[] = [
                    'codigoModulo' => $mod_def['name'],
                    'tiempoConectividad' => $mod_time,
                    'estado' => $mod_estado,
                    'porcentajeAvance' => $mod_progress,
                    'fechaInicio' => $fecha_inicio,
                    'fechaFin' => $fecha_fin,
                    'notaModulo' => $mod_nota,
                    'cantActividadAsincronica' => $mod_def['cant_asinc'],
                    'cantActividadSincronica' => $mod_def['cant_sinc'],
                    'listaActividades' => $act_list
                ];
            }

            $lista_alumnos[] = [
                'rutAlumno' => $rut_alumno,
                'dvAlumno' => (string)$dv_alumno,
                'tiempoConectividad' => $tiempo_conectividad,
                'estado' => $estado,
                'porcentajeAvance' => $progress_percentage,
                'fechaInicio' => $fecha_inicio,
                'fechaFin' => $fecha_fin,
                'fechaEjecucion' => $today,
                'evaluacionFinal' => $grade_val,
                'listaModulos' => $lista_modulos
            ];
        }

        return [
            'rutOtec' => $rut_otec,
            'idSistema' => $idsistema,
            'token' => $token,
            'codigoOferta' => $sence_mapping->codigo_oferta,
            'codigoGrupo' => $sence_mapping->codigo_grupo,
            'codigoEnvio' => !empty($sence_mapping->codigo_envio) ? $sence_mapping->codigo_envio : $sence_mapping->codigo_oferta,
            'cantActividadAsincronica' => $cant_asinc,
            'cantActividadSincronica' => $cant_sinc,
            'listaAlumnos' => $lista_alumnos
        ];
    }
}
