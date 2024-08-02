<?php

if (!defined('ABSPATH')) {
    exit;
}

// Hook into Forminator form submission
add_action('forminator_custom_form_submit_before_set_fields', 'ds_interview_form_handler', 10, 3);

function ds_interview_form_handler($entry, $form_id, $field_data_array) {
    ds_custom_log('------------------------------------------------------------');
    ds_custom_log('Form submission received. Form ID: ' . $form_id);

    if ($form_id != 2341) {
        ds_custom_log('Form ID does not match. Exiting handler.');
        return;
    }

    global $wpdb;
    $user_id = get_current_user_id();
    $user_info = get_userdata($user_id);
    $username = $user_info->user_login;
    $user_email = $user_info->user_email;

    ds_custom_log("User ID: $user_id, Username: $username, Email: $user_email");

    // Define the fields we are interested in
    $expected_fields = [
        'AA' => '',
        'DAT' => '',
        'PAT' => '',
        'TS' => '',
        'GPA' => '',
        'Science GPA' => '',
        'Shadowing' => '',
        'Shadow_Hours_Minimum' => '',
        'Letters_Of_Evaluation' => '',
        'Letters_Of_Evaluation Count' => '',
        'Strength of evaluation letter' => ''
    ];

    // Extract the values for the expected fields
    foreach ($field_data_array as $field) {
        if (isset($field['name']) && isset($field['value'])) {
            switch ($field['name']) {
                case 'number-2': // AA
                    $expected_fields['AA'] = sanitize_text_field($field['value']);
                    break;
                case 'number-3': // DAT
                    $expected_fields['DAT'] = sanitize_text_field($field['value']);
                    break;
                case 'number-4': // PAT
                    $expected_fields['PAT'] = sanitize_text_field($field['value']);
                    break;
                case 'number-5': // TS
                    $expected_fields['TS'] = sanitize_text_field($field['value']);
                    break;
                case 'number-6': // GPA
                    $expected_fields['GPA'] = sanitize_text_field($field['value']);
                    break;
                case 'number-7': // Science GPA
                    $expected_fields['Science GPA'] = sanitize_text_field($field['value']);
                    break;
                case 'checkbox-1': // Shadowing
                    $expected_fields['Shadowing'] = sanitize_text_field($field['value'][0]);
                    break;
                case 'number-1': // Shadow_Hours_Minimum
                    $expected_fields['Shadow_Hours_Minimum'] = sanitize_text_field($field['value']);
                    break;
                case 'checkbox-3': // Letters_Of_Evaluation
                    $expected_fields['Letters_Of_Evaluation'] = sanitize_text_field($field['value'][0]);
                    break;
                case 'number-8': // Letters_Of_Evaluation Count
                    $expected_fields['Letters_Of_Evaluation Count'] = sanitize_text_field($field['value']);
                    break;
                case 'slider-1': // Strength of evaluation letter
                    $expected_fields['Strength of evaluation letter'] = sanitize_text_field($field['value']);
                    break;
                default:
                    ds_custom_log('Unknown field name: ' . $field['name']);
                    break;
            }
        }
    }

    ds_custom_log('Extracted fields: ' . print_r($expected_fields, true));

    // Ensure at least some data is present
    $user_input_data = array_filter($expected_fields);
    if (empty($user_input_data)) {
        ds_custom_log('No valid user input data found. Exiting handler.');
        return;
    }

    // Get data from the custom table
    $table_name = $wpdb->prefix . 'us_dental_schools_data';
    $schools_data = $wpdb->get_results("SELECT * FROM $table_name");

    if ($schools_data) {
        ds_custom_log('Data retrieved from custom table. Number of rows: ' . count($schools_data));
    } else {
        ds_custom_log('No data retrieved from custom table.');
        return;
    }

    $results = [];

    ds_custom_log('Started calculating chances.');
    foreach ($schools_data as $school) {
        // Add your comparison logic here
        $chance = calculate_chance($user_input_data, $school);
        $results[] = [
            'school' => $school->name,
            'chance' => $chance
        ];
    }
    ds_custom_log('Finished calculating chances.');

    // Generate PDF with a unique filename based on user and timestamp
    ds_custom_log('Starting PDF generation.');
    $pdf_filename = generate_pdf_filename($username);
    $pdf_url = ds_generate_pdf($results, $pdf_filename);
    if ($pdf_url) {
        ds_custom_log('PDF generated. URL: ' . $pdf_url);

        // Insert the file URL into the user_interview_evaluation_file_access table
        $table_name = $wpdb->prefix . 'user_interview_evaluation_file_access';
        $wpdb->insert($table_name, [
            'user_id' => $user_id,
            'username' => $username,
            'file_url' => $pdf_url,
            'timestamp' => current_time('mysql'),
        ]);

        if ($wpdb->last_error) {
            ds_custom_log('Database error: ' . $wpdb->last_error);
        } else {
            ds_custom_log('File access record added for user ID: ' . $user_id);
        }

        // Log the action
        ds_log_user_action($user_id, 'Form Submission', 'Generated PDF URL: ' . $pdf_url);
    } else {
        ds_custom_log('PDF generation failed.');
    }
}

function calculate_chance($user_input, $school) {
    // Example comparison logic
    // Replace with your own logic
    return rand(0, 100);
}

function generate_pdf_filename($username) {
    $timestamp = date('Y-m-d_H-i-s');
    return $username . '_' . $timestamp . '.pdf';
}
?>
