<?php

/**
 * Plugin Name: DM Berlin Grading System
 * Plugin URI: http://designmissoula.com
 * Description: A grader for Berlin Questions.
 * Version: 1.3.6
 * Author: Bradford Knowlton
 * Author URI: http://bradknowlton.com
 * License: GPL2
 * GitHub Plugin URI: https://github.com/DesignMissoula/DM-Berlin-Grading-System
 * GitHub Branch: master
 */

// change the 11 here to your form ID
$form_id = '11';

// http://www.gravityhelp.com/forums/topic/simple-calculations
// add_filter('gform_confirmation_'.$form_id, 'dm_berlin_confirmation', 10, 4);

// add_filter("gform_pre_render", "dm_berlin_confirmation");

add_filter('gform_pre_render_'.$form_id, 'dm_berlin_confirmation');

function dm_berlin_confirmation($form) {

	// var_dump($_POST);

	global $form_id, $lead;
	
    $current_page = GFFormDisplay::get_current_page($form["id"]);

	if ($current_page == 2){
	
	// $fields = GFCommon::get_fields_by_type($form, array('quiz'));
		
	foreach( $form['fields'] as &$field ) {
	
				
				if( $field['type'] != 'quiz'){
					continue;
				}
	
				// var_dump($field);
	
                $weighted_score_enabled = rgar($field, "gquizWeightedScoreEnabled");
                // $value                  = RGFormsModel::get_lead_field_value($lead, $field);
                
                $value = $_POST['input_'.$field['id']];
                
                // for checkbox inputs with multiple correct choices
                $completely_correct = true;

                $choices = $field["choices"];
                                
				foreach ($choices as $choice) {
                    $is_choice_correct = isset($choice['gquizIsCorrect']) && $choice['gquizIsCorrect'] == "1" ? true : false;

                    $choice_weight           = isset($choice['gquizWeight']) ? (float)$choice['gquizWeight'] : 1;
                    $choice_class            = $is_choice_correct ? "gquiz-correct-choice " : "";
                    $response_matches_choice = false;
                    $user_responded          = true;
                    if (is_array($value)) {
                        foreach ($value as $item) {
                            if (RGFormsModel::choice_value_match($field, $choice, $item)) {
                                $response_matches_choice = true;
                                break;
                            }
                        }
                    } elseif (empty($value)) {
                        $response_matches_choice = false;
                        $user_responded          = false;
                    } else {
                        $response_matches_choice = RGFormsModel::choice_value_match($field, $choice, $value) ? true : false;

                    }
                    $is_response_correct = $is_choice_correct && $response_matches_choice;
                    if ($response_matches_choice && $weighted_score_enabled){
                        $total_score += $choice_weight;
                        // 
                        $results[$field['id']] = $choice_weight;
                    }

                } // end foreach choice

            } // end foreach field
	
	
	// var_dump($results);
	
	if ( ($results['1'] + $results['2'] + $results['3'] + $results['4'] + $results['5'] ) >= 2){
		$category_1 = 1;
	}else{
		$category_1 = 0;
	}

	if ( ( $results['6'] + $results[7] + $results[8] ) >= 2){
		$category_2 = 1;
	}else{
		$category_2 = 0;
	}
	
	if($lead[12] && $lead[11]){ // avoid division by 0 error

		if ( $results[10] || ($lead[12] / ($lead[11] * $lead[11])) > 30 ){
			$category_3 = 1;
		}else{
			$category_3 = 0;
		}

	}else{

		if ( $results[10] ){
			$category_3 = 1;
		}else{
			$category_3 = 0;
		}
		
	}
	
	
	if( ($category_1 + $category_2 + $category_3) >= 2 ){
		$risk = 'high';
	}else {
		$risk = 'low';
	}
	
	//loop back through form fields to get html field (id 3 on my form) that we are populating with the data gathered above
		foreach($form["fields"] as &$field)
		{
			//get html field
			if ($field["id"] == 18)
			{
			
				//set the field content to the html
				$field["content"] = $risk;
				$field['defaultValue'] = $risk;
			}
		}
	} // end page check
	
	return $form;
	
}