<?php
require_once('../../config.php');
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/moodlelib.php');  // For email_to_user()

// Ensure the user is logged in and is an admin.
require_login();
$context = context_system::instance();
require_capability('local/csvupload:uploadcsv', $context);

global $DB, $USER;

class csv_upload_form extends moodleform {
    
    // Define the form.
    public function definition() {
        $mform = $this->_form;

        // File picker element for uploading CSV.
        $mform->addElement('filepicker', 'csvfile', get_string('uploadfile', 'local_csvupload'), null, ['accepted_types' => '.csv']);
        $mform->addRule('csvfile', null, 'required', null, 'client');
        $mform->addElement('submit', 'submitbutton', get_string('uploadcsv', 'local_csvupload'));
    }
}

$mform = new csv_upload_form();

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/csvupload/index.php'));
} else if ($data = $mform->get_data()) {
    $csvfile = $mform->get_file_content('csvfile');
    $csvfilename = $mform->get_new_filename('csvfile');

    // Save the uploaded file to a temporary location.
    $tempfile = make_temp_directory('csv') . '/' . $csvfilename;
    file_put_contents($tempfile, $csvfile);

    // Open and read the CSV file.
    $csvdata = [];
    if (($handle = fopen($tempfile, 'r')) !== false) {
        while (($row = fgetcsv($handle, 1000, ",")) !== false) {
            // Ensure there are at least 3 columns (firstname, lastname, email).
            if (count($row) >= 3) {
                $csvdata[] = [
                    'firstname' => $row[0],
                    'lastname'  => $row[1],
                    'email'     => $row[2]
                ];
            }
        }
        fclose($handle);
    }
   
    // Display the uploaded CSV data as a preview.
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('previewcsv', 'local_csvupload'));

    if (!empty($csvdata)) {
        // Create an HTML table to show the preview of CSV data.
        $table = new html_table();
        $table->head = ['First Name', 'Last Name', 'Email'];

        foreach ($csvdata as $row) {
            $table->data[] = [$row['firstname'], $row['lastname'], $row['email']];
        }

        // Output the table.
        echo html_writer::table($table);
    } else {
        echo $OUTPUT->notification('The uploaded CSV file is empty or invalid.', \core\output\notification::NOTIFY_ERROR);
    }

    echo $OUTPUT->footer();
    exit;

    if (!empty($csvdata)) {
        foreach ($csvdata as $row) {
            // Prepare the data to insert into the table.
            $record = new stdClass();
            $record->firstname = $row['firstname'];
            $record->lastname = $row['lastname'];
            $record->email = $row['email'];
            $record->timecreated = time();  // Current timestamp

            // Insert the record into the custom table.
            $DB->insert_record('local_csvupload', $record);

            // Prepare the email content.
            $user = new stdClass();
            $user->email = $row['email'];
            $user->firstname = $row['firstname'];
            $user->lastname = $row['lastname'];

            // Define the email content
            $subject = 'Welcome to our platform';
            $message = "Dear {$row['firstname']} {$row['lastname']},\n\nWelcome to our platform! We are excited to have you with us.\n\nBest regards,\nAdmin";

            // Queue the email to the user.
            $success = email_to_user($user, $USER, $subject, $message);
            if (!$success) {
                echo $OUTPUT->notification("Failed to send email to {$row['email']}.", \core\output\notification::NOTIFY_ERROR);
            }
        }

        // Display a success message after insertion.
        \core\notification::add(get_string('uploadsuccess', 'local_csvupload'), \core\output\notification::NOTIFY_SUCCESS);
    } else {
        echo $OUTPUT->notification('The uploaded CSV file is empty or invalid.', \core\output\notification::NOTIFY_ERROR);
    }

}

// Display the form.
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('uploadcsv', 'local_csvupload'));

$mform->display();

echo $OUTPUT->footer();
