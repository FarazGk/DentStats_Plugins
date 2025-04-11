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

    $score_mode = '2_digit_score'; // default score mode
    foreach ($field_data_array as $field) {
        if (isset($field['name']) && $field['name'] === 'radio-1' && isset($field['value'])) {
            $score_mode = sanitize_text_field($field['value']);
            break;
        }
    }
    ds_custom_log('Score mode: ' . $score_mode);

    // Define the fields we are interested in
    $expected_fields = [
        'AA' => '',
        'PAT' => '',
        'TS' => '',
        'GPA' => '',
        'Science GPA' => '',
        'Shadowing' => '',
        'Shadow_Hours_Minimum' => '',
        'Letters_Of_Evaluation' => '',
        'Letters_Of_Evaluation Count' => '',
        'Strength of evaluation letter' => '',
        'Volunteering' => '',
        'Volunteering Hours' => ''
    ];

    // Extract the values for the expected fields
    foreach ($field_data_array as $field) {
        if (isset($field['name']) && isset($field['value'])) {
            $field_name_map = [
                ($score_mode === '3_digit_score') ? 'number-9'  : 'number-2' => 'AA',
                ($score_mode === '3_digit_score') ? 'number-10' : 'number-3' => 'DAT',
                ($score_mode === '3_digit_score') ? 'number-11' : 'number-4' => 'PAT',
                ($score_mode === '3_digit_score') ? 'number-12' : 'number-5' => 'TS',
                'number-6' => 'GPA',
                'number-7' => 'Science GPA',
                'checkbox-1' => 'Shadowing',
                'number-1' => 'Shadow_Hours_Minimum',
                'checkbox-3' => 'Letters_Of_Evaluation',
                'number-8' => 'Letters_Of_Evaluation Count',
                'slider-1' => 'Strength of evaluation letter',
                'checkbox-4' => 'Volunteering',
                'select-1' => 'Volunteering Hours'
            ];

            $key = $field_name_map[$field['name']] ?? null;
            if ($key) {
                if (is_array($field['value'])) {
                    // For checkboxes and multi-select fields
                    $expected_fields[$key] = sanitize_text_field(implode(',', $field['value']));
                } else {
                    $expected_fields[$key] = sanitize_text_field($field['value']);
                }
            } else {
                ds_custom_log('Unknown field name: ' . $field['name']);
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

    // Compute min and max acceptance rates
    $acceptance_rates = array();
    foreach ($schools_data as $school) {
        if (!empty($school->accepted_percentage)) {
            $acceptance_rates[] = (float)$school->accepted_percentage;
        }
    }

    if (!empty($acceptance_rates)) {
        $min_acceptance_rate = min($acceptance_rates);
        $max_acceptance_rate = max($acceptance_rates);
    } else {
        // Set default values if acceptance rates are missing
        $min_acceptance_rate = 0;
        $max_acceptance_rate = 100;
    }

    $results = [];

    ds_custom_log('Started calculating chances.');
    foreach ($schools_data as $school) {
    $chance = calculate_chance($user_input_data, $school, $score_mode);
        $results[] = [
            'school' => $school->name,
            'chance' => $chance
        ];
    }
    ds_custom_log('Finished calculating chances.');

    // Generate personalized suggestions based on user input
    $suggestions = calculate_personalized_suggestions($user_input_data, $score_mode);
    ds_custom_log('Generated personalized suggestions: ' . print_r($suggestions, true));

    // Generate PDF with a unique filename based on user and timestamp
    ds_custom_log('Starting PDF generation.');
    $pdf_filename = generate_pdf_filename($username);
    $pdf_url = ds_generate_pdf($results, $pdf_filename, $suggestions);
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

function calculate_chance($user_input, $school, $score_mode = '2_digit_score') {
    $aa_column  = ($score_mode === '3_digit_score') ? 'AA_3DScore'  : 'aa';
    $dat_column = ($score_mode === '3_digit_score') ? 'DAT_3DScore' : 'dat';
    $pat_column = ($score_mode === '3_digit_score') ? 'PAT_3DScore' : 'pat';
    $ts_column  = ($score_mode === '3_digit_score') ? 'TS_3DScore'  : 'ts';

    static $execution_counter = 0; // Counter variable, set to 2 to allow execution twice
    $score = 0;

    // Define the weights for each factor
    $weights = [
        'gpa' => 20,
        'science_gpa' => 20,
        'AA' => 8,
        'DAT' => 8,
        'PAT' => 8,
        'TS' => 8,
        'shadowing_score' => 8,
        'letters_score' => 8,
        'volunteering_score' => 4,
        'accepted_percentage' => 8
    ];

    // Identify missing data in the school's data
    $null_fields = [];

    if (empty($school->min_gpa) || empty($school->avg_gpa)) {
        $null_fields['gpa'] = $weights['gpa'];
    }
    if (empty($school->min_gpascience) || empty($school->avg_gpascience)) {
        $null_fields['science_gpa'] = $weights['science_gpa'];
    }
    if (empty($school->{$aa_column})) {
        $null_fields['AA'] = $weights['AA'];
    }
    if (empty($school->{$dat_column})) {
        $null_fields['DAT'] = $weights['DAT'];
    }
    if (empty($school->{$pat_column})) {
        $null_fields['PAT'] = $weights['PAT'];
    }
    if (empty($school->{$ts_column})) {
        $null_fields['TS'] = $weights['TS'];
    }
    if (empty($school->shadowing) || empty($school->shadow_hours_minimum)) {
        $null_fields['shadowing_score'] = $weights['shadowing_score'];
    }
    if (empty($school->letters_of_evaluation_count)) {
        $null_fields['letters_score'] = $weights['letters_score'];
    }
    if (empty($school->accepted_percentage)) {
        $null_fields['accepted_percentage'] = $weights['accepted_percentage'];
    }

    // Remove the weights of the missing categories
    $total_weight = array_sum($weights);
    $missing_weight = array_sum($null_fields);
    foreach ($null_fields as $key => $weight) {
        unset($weights[$key]);
    }

    // Redistribute the missing weight proportionally among the remaining categories
    $remaining_weight = array_sum($weights);
    foreach ($weights as $key => &$weight) {
        $weight += ($weight / $remaining_weight) * $missing_weight;
    }
    unset($weight); // break reference

    // Logging for debugging purposes, executed only twice
    if ($execution_counter > 0) {
        ds_custom_log('Calculating chance for school: ' . $school->name);
        ds_custom_log('Null fields for this school: ' . print_r($null_fields, true));
        ds_custom_log('Weights after redistribution: ' . print_r($weights, true));
    }

    // Initialize an array to hold individual category scores for logging
    $category_scores = [];

    // Set the minimum score
    $min_score = 10;
    
    // Now calculate each component score

    // GPA Score
    if (!isset($null_fields['gpa']) && isset($user_input['GPA']) && $user_input['GPA'] !== '') {
        $student_gpa = (float)$user_input['GPA'];
        $school_min_gpa = (float)$school->min_gpa;
        $school_avg_gpa = (float)$school->avg_gpa;

        if ($student_gpa < $school_min_gpa) {
            $gpa_score = 0;
        } elseif ($student_gpa >= $school_avg_gpa) {
            $gpa_score = 100;
        } else {
            // Adjusted scoring formula with min_score
            $gpa_score = $min_score + (100 - $min_score) * ($student_gpa - $school_min_gpa) / ($school_avg_gpa - $school_min_gpa);
        }

        // Cap the score at 100%
        $gpa_score = min($gpa_score, 100);

        $weighted_gpa_score = $weights['gpa'] * $gpa_score / 100;
        $score += $weighted_gpa_score;

        // Log the GPA score
        if ($execution_counter > 0) {
            $category_scores['GPA'] = $weighted_gpa_score;
        }
    }

    // Science GPA Score
    if (!isset($null_fields['science_gpa']) && isset($user_input['Science GPA']) && $user_input['Science GPA'] !== '') {
        $student_science_gpa = (float)$user_input['Science GPA'];
        $school_min_gpascience = (float)$school->min_gpascience;
        $school_avg_gpascience = (float)$school->avg_gpascience;

        if ($student_science_gpa < $school_min_gpascience) {
            $science_gpa_score = 0;
        } elseif ($student_science_gpa >= $school_avg_gpascience) {
            $science_gpa_score = 100;
        } else {
            // Adjusted scoring formula with min_score
            $science_gpa_score = $min_score + (100 - $min_score) * ($student_science_gpa - $school_min_gpascience) / ($school_avg_gpascience - $school_min_gpascience);
        }

        // Cap the score at 100%
        $science_gpa_score = min($science_gpa_score, 100);

        $weighted_science_gpa_score = $weights['science_gpa'] * $science_gpa_score / 100;
        $score += $weighted_science_gpa_score;

        // Log the Science GPA score
        if ($execution_counter > 0) {
            $category_scores['Science GPA'] = $weighted_science_gpa_score;
        }
    }

    // AA Score
    if (!isset($null_fields['AA']) && isset($user_input['AA']) && $school->{$aa_column} !== '') {
        $student_aa = (float)$user_input['AA'];
        $school_aa = (float)$school->{$aa_column};

        if ($school_aa != 0) {
            if ($student_aa >= $school_aa) {
                $aa_score = 100;
            } else {
                $aa_score = 100 * $student_aa / $school_aa;
            }
        } else {
            $aa_score = 100;
        }

        $weighted_aa_score = $weights['AA'] * $aa_score / 100;
        $score += $weighted_aa_score;

        // Log the AA score
        if ($execution_counter > 0) {
            $category_scores['AA'] = $weighted_aa_score;
        }
    }

    // DAT Score
    if (!isset($null_fields['DAT']) && isset($user_input['DAT']) && $school->{$dat_column} !== '') {
        $student_dat = (float)$user_input['DAT'];
        $school_dat = (float)$school->{$dat_column};

        if ($school_dat != 0) {
            if ($student_dat >= $school_dat) {
                $dat_score = 100;
            } else {
                $dat_score = 100 * $student_dat / $school_dat;
            }
        } else {
            $dat_score = 100;
        }

        $weighted_dat_score = $weights['DAT'] * $dat_score / 100;
        $score += $weighted_dat_score;

        // Log the DAT score
        if ($execution_counter > 0) {
            $category_scores['DAT'] = $weighted_dat_score;
        }
    }

    // PAT Score
    if (!isset($null_fields['PAT']) && isset($user_input['PAT']) && $school->{$pat_column} !== '') {
        $student_pat = (float)$user_input['PAT'];
        $school_pat = (float)$school->{$pat_column};

        if ($school_pat != 0) {
            if ($student_pat >= $school_pat) {
                $pat_score = 100;
            } else {
                $pat_score = 100 * $student_pat / $school_pat;
            }
        } else {
            $pat_score = 100;
        }

        $weighted_pat_score = $weights['PAT'] * $pat_score / 100;
        $score += $weighted_pat_score;

        // Log the PAT score
        if ($execution_counter > 0) {
            $category_scores['PAT'] = $weighted_pat_score;
        }
    }

    // TS Score
    if (!isset($null_fields['TS']) && isset($user_input['TS']) && $school->{$ts_column} !== '') {
        $student_ts = (float)$user_input['TS'];
        $school_ts = (float)$school->{$ts_column};

        if ($school_ts != 0) {
            if ($student_ts >= $school_ts) {
                $ts_score = 100;
            } else {
                $ts_score = 100 * $student_ts / $school_ts;
            }
        } else {
            $ts_score = 100;
        }

        $weighted_ts_score = $weights['TS'] * $ts_score / 100;
        $score += $weighted_ts_score;

        // Log the TS score
        if ($execution_counter > 0) {
            $category_scores['TS'] = $weighted_ts_score;
        }
    }

    // Shadowing Score
    if (!isset($null_fields['shadowing_score']) && isset($user_input['Shadowing']) && isset($user_input['Shadow_Hours_Minimum']) && $school->shadowing !== '' && $school->shadow_hours_minimum !== '') {
        $school_requires_shadowing = strtolower($school->shadowing) === 'yes' ? true : false;
        $student_has_shadowing = strtolower($user_input['Shadowing']) === 'yes' ? true : false;

        $student_shadow_hours = (float)$user_input['Shadow_Hours_Minimum'];
        $school_shadow_hours_min = (float)$school->shadow_hours_minimum;

        if ($student_has_shadowing) {
            if ($student_shadow_hours >= $school_shadow_hours_min) {
                $shadowing_score = 100;
            } else {
                if ($school_shadow_hours_min != 0) {
                    $shadowing_score = 100 * $student_shadow_hours / $school_shadow_hours_min;
                } else {
                    $shadowing_score = 100;
                }
            }
        } else {
            $shadowing_score = 0;
        }

        $weighted_shadowing_score = $weights['shadowing_score'] * $shadowing_score / 100;
        $score += $weighted_shadowing_score;

        // Log the Shadowing score
        if ($execution_counter > 0) {
            $category_scores['Shadowing'] = $weighted_shadowing_score;
        }
    }

    // Letters of Evaluation Score
    if (!isset($null_fields['letters_score']) && isset($user_input['Letters_Of_Evaluation Count']) && isset($user_input['Strength of evaluation letter']) && $school->letters_of_evaluation_count !== '') {
        $school_letters_required = (int)$school->letters_of_evaluation_count;
        $student_letters_count = (int)$user_input['Letters_Of_Evaluation Count'];
        $letters_count_score = $student_letters_count >= $school_letters_required ? 100 : 100 * $student_letters_count / $school_letters_required;

        $letters_strength = (float)$user_input['Strength of evaluation letter'];
        $letters_strength_score = ($letters_strength / 4) * 100;

        $letters_score = 0.5 * $letters_count_score + 0.5 * $letters_strength_score;

        $weighted_letters_score = $weights['letters_score'] * $letters_score / 100;
        $score += $weighted_letters_score;

        // Log the Letters score
        if ($execution_counter > 0) {
            $category_scores['Letters of Evaluation'] = $weighted_letters_score;
        }
    }

    // Volunteering Score
    if (isset($user_input['Volunteering']) && strtolower($user_input['Volunteering']) === 'yes' && isset($user_input['Volunteering Hours'])) {
        $volunteering_hours = $user_input['Volunteering Hours'];
        if ($volunteering_hours === '1-10') {
            $volunteering_score = 33;
        } elseif ($volunteering_hours === '10-100') {
            $volunteering_score = 66;
        } elseif ($volunteering_hours === 'Over 100') {
            $volunteering_score = 100;
        } else {
            $volunteering_score = 0;
        }

        $weighted_volunteering_score = $weights['volunteering_score'] * $volunteering_score / 100;
        $score += $weighted_volunteering_score;

        // Log the Volunteering score
        if ($execution_counter > 0) {
            $category_scores['Volunteering'] = $weighted_volunteering_score;
        }
    } else {
        // If volunteering is not done
        $volunteering_score = 0;
        $weighted_volunteering_score = $weights['volunteering_score'] * $volunteering_score / 100;
        $score += $weighted_volunteering_score;

        // Log the Volunteering score
        if ($execution_counter > 0) {
            $category_scores['Volunteering'] = $weighted_volunteering_score;
        }
    }

    // Accepted Percentage Score
    if (!isset($null_fields['accepted_percentage'])) {
        // Remove '%' sign and convert to float
        $accepted_percentage_str = str_replace('%', '', $school->accepted_percentage);
        $accepted_percentage_value = (float)$accepted_percentage_str;

        // Directly calculate the weighted score
        $accepted_percentage_score = $weights['accepted_percentage'] * $accepted_percentage_value / 10;

        // Ensure the score does not exceed the maximum possible for this category
        $accepted_percentage_score = min($accepted_percentage_score, $weights['accepted_percentage']);

        $score += $accepted_percentage_score;

        // Log the Accepted Percentage score
        if ($execution_counter > 0) {
            $category_scores['Accepted Percentage'] = $accepted_percentage_score;
        }
    }


    // Now, compute the overall chance
    $chance = $score; // Since weights sum to 100 after adjustment

    // Adjust for school competitiveness using acceptance rate
    // (Assuming $min_acceptance_rate and $max_acceptance_rate are accessible here)
    global $min_acceptance_rate, $max_acceptance_rate;

    if (!empty($school->accepted_percentage) && $max_acceptance_rate > $min_acceptance_rate) {
        // Remove '%' sign and convert to float for adjustment
        $school_acceptance_rate_str = str_replace('%', '', $school->accepted_percentage);
        $school_acceptance_rate = (float)$school_acceptance_rate_str;
        
        // Normalize the acceptance rate
        $normalized_acceptance_rate = ($school_acceptance_rate - $min_acceptance_rate) / ($max_acceptance_rate - $min_acceptance_rate);

        // Compute the competitiveness factor
        $competitiveness_factor = 1 - $normalized_acceptance_rate;

        // Set the adjustment weight (you can adjust this value between 0 and 0.2)
        $adjustment_weight = 0.1;

        // Calculate the adjustment
        $adjustment = $competitiveness_factor * $adjustment_weight;

        // Adjust the chance
        $adjusted_chance = $chance * (1 - $adjustment);
    } else {
        $adjusted_chance = $chance;
    }

    // Log the category scores
    if ($execution_counter > 0) {
        ds_custom_log('Category scores for this school: ' . print_r($category_scores, true));
        $execution_counter--; // Decrement the counter after logging
        if (!isset($null_fields['accepted_percentage'])) {
            $accepted_percentage_str = str_replace('%', '', $school->accepted_percentage);
            $accepted_percentage_value = (float)$accepted_percentage_str;
            ds_custom_log('accepted_percentage_value: ' . print_r($accepted_percentage_value, true));

            
        }
    }

    return round($adjusted_chance, 2);
}


function calculate_personalized_suggestions($user_input, $score_mode, $max_suggestions = 3) {
    $averages = get_stored_averages($score_mode);
    if (!$averages) {
        return [
            [
                'suggestion' => 'Unable to retrieve average data. Please try again later.',
                'resource_link' => ''
            ]
        ];
    }

    $deviations = calculate_deviations($user_input, $averages);

    // Filter deviations to only include categories where the deviation is less than -2%
    $filtered_deviations = array_filter($deviations, function($item) {
        return $item['deviation'] <= -2;
    });

    if (empty($filtered_deviations)) {
        return [
            [
                'suggestion' => 'Your inputs are above the average dental student in all categories. Great job!',
                'resource_link' => ''
            ]
        ];
    }

    // Sort deviations in ascending order to get the most underperforming categories first
    uasort($filtered_deviations, function($a, $b) {
        return $a['deviation'] <=> $b['deviation'];
    });

    ds_custom_log('deviations: ' . print_r($deviations, true));

    $suggestions = [];
    foreach ($filtered_deviations as $category => $item) {
        $suggestion_item = generate_suggestion_and_link($category, $item['deviation'], $item['user_value']);

        // Only add non-empty suggestions
        if (!empty($suggestion_item['suggestion'])) {
            $suggestions[] = $suggestion_item;

            if (count($suggestions) >= $max_suggestions) {
                break; // Limit to the top 'max_suggestions' categories
            }
        }
    }

    return $suggestions;
}

function get_stored_averages($score_mode = '2_digit_score') {
    global $wpdb;
    return $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}dental_school_averages 
            WHERE score_mode = %s LIMIT 1",
            $score_mode
        ),
        ARRAY_A
    );
}

/**
 * Calculates the percentage deviation between a user value and an average value.
 *
 * @param mixed $user_value The user-provided value.
 * @param mixed $average_value The reference average value.
 * @return float The calculated deviation percentage.
 */
function calculate_deviation($user_value, $average_value) {
    if ($average_value === null || $average_value <= 0) {
        return 0;
    }
    if ($user_value === null) {
        $user_value = 0; // Treat null as 0.
    }
    return (($user_value - $average_value) / $average_value) * 100;
}

/**
/**
 * Calculates deviations for various categories using the stored averages passed in.
 *
 * Expects that $averages contains the following keys:
 * - shadow_hours_minimum
 * - gpa
 * - science_gpa
 * - AA, DAT, PAT, TS (which reflect either 2-digit or 3-digit scores based on the selected mode)
 *
 * Logs the comparisons for each test score and then calculates an average for the test scores.
 *
 * @param array $user_input The user-provided input values.
 * @param array $averages   The stored averages from the database.
 * @return array An associative array of deviations.
 */
function calculate_deviations($user_input, $averages) {
    // Define the test score keys.
    $test_keys = ['DAT', 'AA', 'PAT', 'TS'];
    
    // Log individual comparisons for each test score.
    foreach ($test_keys as $key) {
        $user_val   = (isset($user_input[$key]) && $user_input[$key] !== '') ? (float)$user_input[$key] : null;
        $stored_val = (isset($averages[$key]) && $averages[$key] !== '') ? (float)$averages[$key] : null;
        ds_custom_log("Deviation Calculation - {$key}: user value = " . var_export($user_val, true) . ", stored average = " . var_export($stored_val, true));
    }
    
    // Compute the average test score for the user.
    $user_test_scores = [];
    foreach ($test_keys as $key) {
        if (isset($user_input[$key]) && $user_input[$key] !== '') {
            $user_test_scores[] = (float)$user_input[$key];
        }
    }
    $user_test_average = (count($user_test_scores) > 0) ? (array_sum($user_test_scores) / count($user_test_scores)) : null;
    
    // Compute the stored average test score.
    $stored_test_scores = [];
    foreach ($test_keys as $key) {
        if (isset($averages[$key]) && $averages[$key] !== '') {
            $stored_test_scores[] = (float)$averages[$key];
        }
    }
    $stored_test_average = (count($stored_test_scores) > 0) ? (array_sum($stored_test_scores) / count($stored_test_scores)) : null;
    
    ds_custom_log("Deviation Calculation - Test scores average: user = " . var_export($user_test_average, true) . ", stored = " . var_export($stored_test_average, true));
    
    // Build and return the deviations array.
    return [
        'shadow_hours_minimum' => [
            'deviation'  => calculate_deviation($user_input['Shadow_Hours_Minimum'] ?? null, $averages['shadow_hours_minimum'] ?? null),
            'user_value' => $user_input['Shadow_Hours_Minimum'] ?? null,
        ],
        'gpa' => [
            'deviation'  => calculate_deviation($user_input['GPA'] ?? null, $averages['gpa'] ?? null),
            'user_value' => $user_input['GPA'] ?? null,
        ],
        'science_gpa' => [
            'deviation'  => calculate_deviation($user_input['Science GPA'] ?? null, $averages['science_gpa'] ?? null),
            'user_value' => $user_input['Science GPA'] ?? null,
        ],
        'test_scores' => [
            'deviation'  => calculate_deviation($user_test_average, $stored_test_average),
            'user_value' => $user_test_average,
        ],
    ];
}

function generate_suggestion_and_link($category, $deviation, $user_value) {
    $suggestion = "";
    $resource_link = "";
    $percentage_off = abs(round($deviation, 2)); // Convert deviation to positive number and round

    switch ($category) {
        case 'shadow_hours_minimum':
            if ($user_value === null || $user_value == 0) {
                $suggestion = "You have not reported any shadowing hours. Gaining shadowing experience is crucial to strengthen your application.";
            } else {
                $suggestion = "Your shadowing hours are {$percentage_off}% below the average. Consider increasing your shadowing experience to improve your chances.";
            }
            $resource_link = "https://dentstats.com/resources/shadowing-2/";
            break;
        case 'gpa':
            $suggestion = "Your GPA is {$percentage_off}% lower than the average. Consider focusing on improving your GPA to enhance your application.";
            $resource_link = "https://dentstats.com/resources/low-gpa/";
            break;
        case 'science_gpa':
            $suggestion = "Your science_gpa is {$percentage_off}% lower than the average. Consider focusing on improving your science_gpa to enhance your application.";
            $resource_link = "https://dentstats.com/resources/low-gpa/";
            break;    
        case 'test_scores':
            $suggestion = "Your test scores are {$percentage_off}% below the average. Consider retaking the DAT to increase your score and strengthen your application.";
            $resource_link = "https://dentstats.com/resources/dat-2/";
            break;
        // Add other cases if needed
    }

    return [
        'suggestion' => $suggestion,
        'resource_link' => $resource_link
    ];
}


function generate_pdf_filename($username) {
    $timestamp = date('Y-m-d_H-i-s');
    return $username . '_' . $timestamp . '.pdf';
}
?>
