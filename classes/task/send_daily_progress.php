<?php
// This file is part of Moodle - http://moodle.org/

namespace block_sence_integration\task;

use core\task\scheduled_task;
use block_sence_integration\data_builder;
use block_sence_integration\client;

defined('MOODLE_INTERNAL') || die();

class send_daily_progress extends scheduled_task {

    public function get_name() {
        return get_string('send_progress_now', 'block_sence_integration');
    }

    public function execute() {
        global $DB;

        mtrace("Starting SENCE daily progress transmission task...");

        $mappings = $DB->get_records('block_sence_courses', ['enabled' => 1]);
        if (empty($mappings)) {
            mtrace("No active SENCE course mappings found.");
            return;
        }

        foreach ($mappings as $mapping) {
            $course = $DB->get_record('course', ['id' => $mapping->courseid]);
            if (!$course) {
                mtrace("Course ID {$mapping->courseid} not found. Skipping.");
                continue;
            }

            mtrace("Processing SENCE mapping ID {$mapping->id}: Offer {$mapping->codigo_oferta}, Group {$mapping->codigo_grupo} for Course: {$course->fullname}");

            $payload = data_builder::build_course_payload($course, $mapping);
            if (empty($payload['listaAlumnos'])) {
                mtrace("No students with valid RUT found for this mapping. Skipping transmission.");
                continue;
            }

            $res = client::send_progress($payload);

            // Log result
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
                mtrace("Successfully transmitted. Process ID: " . ($log->id_proceso ?? 'N/A'));
            } else {
                mtrace("Error transmitting to SENCE API: " . ($res['error'] ?? 'API error response'));
            }
        }

        mtrace("SENCE daily progress transmission completed.");
    }
}
