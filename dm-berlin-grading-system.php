<?php

/**
 * Plugin Name: DM Berlin Grading System
 * Plugin URI: http://designmissoula.com
 * Description: A grader for Berlin Questions.
 * Version: 1.0
 * Author: Bradford Knowlton
 * Author URI: http://bradknowlton.com
 * License: GPL2
 * GitHub Plugin URI: https://github.com/DesignMissoula/DM-Berlin-Grading-System
 * GitHub Branch: master
 */




// http://www.gravityhelp.com/forums/topic/simple-calculations
// change the 41 here to your form ID
add_action('gform_pre_submission_41', 'ch_awesomeness_rating');
function ch_awesomeness_rating($form) {

	// set up one array for each step of the form
	// each array contains the input IDs of the fields we want to sum on each page
	// IDs do not need to be consecutive using this method
	$step_1_fields = array('input_2',  'input_3',  'input_4',  'input_5',  'input_6',  'input_7',  'input_8',  'input_9',  'input_10', 'input_11');
	$step_2_fields = array('input_14', 'input_15', 'input_16', 'input_17', 'input_18', 'input_19', 'input_20', 'input_21', 'input_22', 'input_23');
	$step_3_fields = array('input_27', 'input_28', 'input_29', 'input_30', 'input_31', 'input_32', 'input_33', 'input_34', 'input_35', 'input_36');

	// loop through inputs for each step individually
	$culture = 0;
	foreach($step_1_fields as $value)
		// add each value to $step1_score
		$culture += rgpost($value);

	$process = 0;
	foreach($step_2_fields as $value)
		// do the same for step 2
		$process += rgpost($value);

	$behavior = 0;
	foreach($step_3_fields as $value)
		// and also for step 3
		$behavior += rgpost($value);

	// total of the subtotals for each step
	$overall = $culture + $process + $behavior;

	// submit these calculated values to the form so they are stored with the entry and can be used in the confirmation
	$_POST['input_38'] = $culture;
	$_POST['input_39'] = $process;
	$_POST['input_40'] = $behavior;
	$_POST['input_41'] = $overall;	

	// be sure to return the form when we're done
	return $form;
}

// http://www.gravityhelp.com/forums/topic/simple-calculations
// change the 41 here to your form ID
add_filter('gform_confirmation_41', 'ch_courage_confirmation', 10, 4);
function ch_courage_confirmation($confirmation, $form, $lead, $ajax) {

	// beginning of the confirmation message
	$confirmation = "<a name='gf_41' class='gform_anchor' ></a><div id='gforms_confirmation_message' class='gform_confirmation_message_41' style='text-align:left;'>";

	// set the "lowest score" message as a default and change it if a higher score is achieved
	$grading = 'This score assumes that you probably have "lack of courage" issues in all three areas of culture, process, and behavior. You’ll need to pick your battles and figure out where you can concentrate your efforts at first.';

	// reset the confirmation message based on the overall score, checking for lowest scores first
	// this will bump the message up if a higher overall score is found
	// this could have been done with an if, else if statement as well
	if($lead[41] > 75)
		$grading = 'Not a bad start. Pay close attention to which of the three areas (or particular questions) scored highest and lowest. Can you do more of the bright spots? Can you scrap some things that are completely not courageous?';
	if($lead[41] > 150)
		$grading = 'Your organization is well on the way to being courageous! You can freely concentrate on the areas that scored lower than others.';
	if($lead[41] > 225)
		$grading = 'If you have more than about 240 points, please call us, because we want to feature you on the blog as an example of a courageous organization. Nice job.';

	// append this conditional information to the confirmation text entered in the form builder
	$confirmation .= "<strong>Overall score: " . $lead[41] .".</strong> ". $grading;

	// display the subtotal score on the confirmation page as well
	// 38 is courage, 39 is process, 40 is behavior
	$confirmation .= '<h4>Here are your totals for each section</h4><ul class="step_scores" style="padding-left:20px;"><li><strong>Courage:</strong> ' .$lead[38]. '</li><li><strong>Process:</strong> ' .$lead[39]. '</li><li><strong>Behavior:</strong> ' .$lead[40]. '</li></ul></div>';

	// return the confirmation	
	return $confirmation;
}