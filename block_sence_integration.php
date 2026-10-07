<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

class block_sence_integration extends block_base {

    public function init() {
        $this->title = get_string('block_title', 'block_sence_integration');
    }

    public function applicable_formats() {
        return [
            'course-view' => true,
            'site' => false,
            'my' => false
        ];
    }

    public function instance_allow_multiple() {
        return false;
    }

    public function has_config() {
        return true;
    }

    public function get_content() {
        global $COURSE, $DB, $OUTPUT;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';

        if (empty($COURSE->id) || $COURSE->id == SITEID) {
            return $this->content;
        }

        $context = context_course::instance($COURSE->id);
        $canmanage = has_capability('block/sence_integration:manage', $context);
        $cantransmit = has_capability('block/sence_integration:transmit', $context);

        // Fetch registered SENCE IDs for this course.
        $sence_records = $DB->get_records('block_sence_courses', ['courseid' => $COURSE->id]);
        $count = count($sence_records);

        $html = '<div class="block_sence_content">';

        if ($count > 0) {
            $html .= '<div class="alert alert-info py-1 px-2 mb-2" style="font-size: 0.85rem;">';
            $html .= get_string('registered_ids_count', 'block_sence_integration', $count);
            $html .= '</div>';

            $html .= '<ul class="list-group list-group-flush mb-3" style="font-size: 0.85rem;">';
            foreach ($sence_records as $rec) {
                $groupname = get_string('all_course_students', 'block_sence_integration');
                $member_count = 0;
                if (!empty($rec->groupid)) {
                    $group = $DB->get_record('groups', ['id' => $rec->groupid]);
                    if ($group) {
                        $groupname = format_string($group->name);
                        $member_count = $DB->count_records('groups_members', ['groupid' => $group->id]);
                    }
                } else {
                    $member_count = count_enrolled_users($context);
                }

                $html .= '<li class="list-group-item px-1 py-2">';
                $html .= '<div class="d-flex justify-content-between align-items-center mb-1">';
                $html .= '<strong><i class="fa fa-users text-primary"></i> ' . $groupname . '</strong>';
                $html .= '<span class="badge bg-secondary">' . $member_count . ' alumnos</span>';
                $html .= '</div>';
                $html .= '<div class="small text-muted mb-1">';
                $html .= 'Oferta: <code>' . s($rec->codigo_oferta) . '</code><br>';
                $html .= 'Sección: <code>' . s($rec->codigo_grupo) . '</code>';
                $html .= '</div>';

                if ($cantransmit) {
                    $transmit_single_url = new moodle_url('/blocks/sence_integration/transmit.php', [
                        'courseid' => $COURSE->id,
                        'mappingid' => $rec->id,
                        'sesskey' => sesskey()
                    ]);
                    $html .= html_writer::link($transmit_single_url, '<i class="fa fa-paper-plane"></i> Enviar este Grupo', [
                        'class' => 'btn btn-xs btn-outline-success w-100 py-0 mt-1',
                        'style' => 'font-size: 0.78rem;'
                    ]);
                }
                $html .= '</li>';
            }
            $html .= '</ul>';
        } else {
            $html .= '<div class="alert alert-warning py-1 px-2 mb-2" style="font-size: 0.85rem;">';
            $html .= get_string('no_sence_codes', 'block_sence_integration');
            $html .= '</div>';
        }

        // Action buttons
        $html .= '<div class="d-grid gap-2">';

        if ($canmanage) {
            $manage_url = new moodle_url('/blocks/sence_integration/manage.php', ['courseid' => $COURSE->id]);
            $html .= html_writer::link($manage_url, '<i class="fa fa-cogs"></i> ' . get_string('manage_sence_ids', 'block_sence_integration'), [
                'class' => 'btn btn-sm btn-outline-primary mb-1 w-100 text-left'
            ]);
        }

        if ($cantransmit && $count > 0) {
            $transmit_url = new moodle_url('/blocks/sence_integration/transmit.php', [
                'courseid' => $COURSE->id,
                'sesskey' => sesskey()
            ]);
            $html .= html_writer::link($transmit_url, '<i class="fa fa-paper-plane"></i> ' . get_string('send_progress_now', 'block_sence_integration'), [
                'class' => 'btn btn-sm btn-success mb-1 w-100 text-left'
            ]);

            $test_url = new moodle_url('/blocks/sence_integration/test_connection.php', [
                'courseid' => $COURSE->id,
                'sesskey' => sesskey()
            ]);
            $html .= html_writer::link($test_url, '<i class="fa fa-plug"></i> ' . get_string('test_connection', 'block_sence_integration'), [
                'class' => 'btn btn-sm btn-outline-secondary mb-1 w-100 text-left'
            ]);
        }

        if ($canmanage) {
            $logs_url = new moodle_url('/blocks/sence_integration/logs.php', ['courseid' => $COURSE->id]);
            $html .= html_writer::link($logs_url, '<i class="fa fa-history"></i> ' . get_string('view_logs', 'block_sence_integration'), [
                'class' => 'btn btn-sm btn-outline-info w-100 text-left'
            ]);
        }

        $html .= '</div></div>';

        $this->content->text = $html;
        return $this->content;
    }
}
