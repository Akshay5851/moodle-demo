<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) { // Check if the current user has the capability to configure the site (typically admins).
    $settings = new admin_settingpage('local_csvupload', get_string('pluginname', 'local_csvupload'));

    // You can add any settings you want here, like a link to the CSV upload page or description.
    $settings->add(new admin_setting_heading(
        'local_csvupload_settings',
        get_string('pluginname', 'local_csvupload'),
        get_string('uploadcsv', 'local_csvupload') // Add description or heading.
    ));

    // Add the settings page to the "Local plugins" section of the admin menu.
    $ADMIN->add('localplugins', $settings);
}

if ($ADMIN->fulltree) {
    // Checkbox setting to enable sending random emails.
    $settings->add(new admin_setting_configcheckbox(
        'local_sendrandomemail/enabled',
        get_string('sendrandomemail', 'local_csvupload'),
        get_string('sendrandomemail_desc', 'local_csvupload'),
        0 // Default value
    ));
}
