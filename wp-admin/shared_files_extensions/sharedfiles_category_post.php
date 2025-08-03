<?php
/**
 * TODO.
 *
 * TODO Manage Post actions: post, edit, delete, etc.
 *
 * @package WALLNER
 * @subpackage SharedFileExtension
 */

/** WordPress Administration Bootstrap */
// TODO: Ist diese Zeile NÖTIG???
require_once dirname(__DIR__) . '/admin.php';

include "sharedfiles_helperfunctions.php";
include "sharedfiles_post_savedetails.php";

$action = !empty($_REQUEST['action']) ? sanitize_text_field($_REQUEST['action']) : '';

/**
 * @global string       $post_type        Global post type.
 * @global WP_Post_Type $post_type_object Global post type object.
 * @global WP_Post      $post             Global post object.
 */
global $post_type, $post_type_object, $post;

if ($post) {
	$post_type = $post->post_type;
	$post_type_object = get_post_type_object($post_type);
}

$sendback_test = wp_get_referer();
$sendback_test = strtok($sendback_test, '?');

$params = arrayFindObjectElementPerKeyContains($_POST, "referer_parm_");

$i = 0;
foreach($params as $key => $value){
	if($i == 0){
		$sendback_test .= "?" . $key . "=" . $value;
	}
	else {
		$sendback_test .= "&" . $key . "=" . $value;
	}
	$i++;
}

// error_log("POST _POST " . print_r($_POST, true) . ", sendback_test url " . print_r($sendback_test, true));

if (isset($_POST['post_type']) && $post && $post_type !== $_POST['post_type']) {
	wp_die(__('A post type mismatch has been detected.'), __('Sorry, you are not allowed to edit this item.'), 400);
}


$vars = $_POST;
 error_log("POST CAT VALUES " . print_r($vars, true));
 error_log(", sendback_test url " . print_r($sendback_test, true));

if (gettype($vars) == "array") {
	// Read post data
	$categorySlug = $vars["referer_parm_sf_category"];
	$selectedFiles = $vars["selectedFiles"];

	replaceCategories($categorySlug, $selectedFiles);
}


wp_redirect($sendback_test);