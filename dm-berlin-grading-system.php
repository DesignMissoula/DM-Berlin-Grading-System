<?php

/**
 * Plugin Name: DM Berlin Grading System
 * Plugin URI: http://designmissoula.com
 * Description: A grader for Berlin Questions.
 * Version: 1.3.5
 * Author: Bradford Knowlton
 * Author URI: http://bradknowlton.com
 * License: GPL2
 * GitHub Plugin URI: https://github.com/DesignMissoula/DM-Berlin-Grading-System
 * GitHub Branch: master
 */

// change the 11 here to your form ID
$form_id = '11';

// http://www.gravityhelp.com/forums/topic/simple-calculations
add_filter('gform_confirmation_'.$form_id, 'dm_berlin_confirmation', 10, 4);

function dm_berlin_confirmation($confirmation, $form, $lead, $ajax) {

	global $form_id;
	
	$fields = GFCommon::get_fields_by_type($form, array('quiz'));
		
	foreach ($fields as $field) {
                $weighted_score_enabled = rgar($field, "gquizWeightedScoreEnabled");
                $value                  = RGFormsModel::get_lead_field_value($lead, $field);

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
	
	
	if ( ($results['1'] + $results['2'] + $results['3'] + $results['4'] + $results['5'] ) >= 2){
		$category_1 = 1;
	}else{
		$category_1 = 0;
	}

	if ( ($results[5] + $results[6] + $results[7] + $results[8] + $results[9] ) >= 2){
		$category_2 = 1;
	}else{
		$category_2 = 0;
	}
	if($lead[12] && $lead[11]){

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
	}else if( ($category_1 + $category_2 + $category_3) == 1 ){
		$risk = 'low';
	}else{
		$risk = 'none';
	}
	
	// beginning of the confirmation message
	$confirmation = "<a name='gf_".$form_id."' class='gform_anchor' ></a><div id='gforms_confirmation_message' class='gform_confirmation_message_".$form_id."' style='text-align:left;'>";

	// set the "lowest score" message as a default and change it if a higher score is achieved
	$grading = 'This score assumes that you probably have "no risk" issues in all three areas of culture, process.';

	// reset the confirmation message based on the overall score, checking for lowest scores first
	// this will bump the message up if a higher overall score is found
	// this could have been done with an if, else if statement as well
	if($risk == 'high')
		$grading = 'Very high risk, seek professional attention';
	if($risk == 'low')
		$grading = 'Low risk.';
	
	// append this conditional information to the confirmation text entered in the form builder
	$confirmation .= "<strong>Overall score: " . ucwords($risk) ." risk.</strong> ". $grading;

	// return the confirmation	
	return $confirmation;
}