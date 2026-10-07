<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

$tasks = [
    [
        'classname'   => 'block_sence_integration\task\send_daily_progress',
        'blocking'    => 0,
        'minute'      => '0',
        'hour'        => '22',
        'day'         => '*',
        'month'       => '*',
        'dayofweek'   => '*',
    ],
];
