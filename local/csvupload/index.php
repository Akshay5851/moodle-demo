<?php
require_once('../../config.php');

// Ensure the user is logged in and is an admin.
require_login();
$context = context_system::instance();
require_capability('local/csvupload:uploadcsv', $context);

$PAGE->set_url(new moodle_url('/local/csvupload/index.php'));
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'local_csvupload'));
$PAGE->set_heading(get_string('pluginname', 'local_csvupload'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'local_csvupload'));

$url = new moodle_url('/local/csvupload/upload.php');
echo html_writer::link($url, get_string('uploadcsv', 'local_csvupload'));

echo $OUTPUT->footer();
