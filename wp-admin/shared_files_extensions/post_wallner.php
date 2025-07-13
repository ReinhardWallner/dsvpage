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
require_once __DIR__ . '/admin.php';

include "post_wallner_helper_functions.php";
include "post_wallner_save.php";

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


if (isset($_POST['post_type']) && $post && $post_type !== $_POST['post_type']) {
	wp_die(__('A post type mismatch has been detected.'), __('Sorry, you are not allowed to edit this item.'), 400);
}


$vars = $_POST;
// error_log("POST VALUES " . print_r($vars, true));

if (gettype($vars) == "array") {
	// Read post data
	$title_values = [];
	$desc_values = [];
	$customfield_values = [];
	$tag_values = [];
	$category_values = [];

	$keys = array_keys($vars);
	foreach ($keys as $key) {
		if (str_starts_with($key, "_sf_file_title_")) {
			$file_id = addSingleIdValuesToArray($vars, $key, $title_values);
		}
		if (str_starts_with($key, "_sf_file_description_")) {
			$file_id = addSingleIdValuesToArray($vars, $key, $desc_values);
		}
		if (str_starts_with($key, "_sf_file_cf")) {
			$file_id = addDoubleIdValuesToArray($vars, $key, $customfield_values);
		}
		if (str_starts_with($key, "_sf_file_tags")) {
			$file_id = addSingleIdValuesToArray($vars, $key, $tag_values);
		}
		if (str_starts_with($key, "_sf_file_cat")) {
			$file_id = addDoubleIdValuesToArray($vars, $key, $category_values);
		}
	}


	// error_log("Title values:" . print_r($title_values, true));
	// error_log("Description values:" . print_r($desc_values, true));
	// error_log("Tag values:" . print_r($tag_values, true));
	// error_log("Custom Field values:" . print_r($customfield_values, true));
	// error_log("Category values:" . print_r($category_values, true));

	// Update values
	$tfiltered = array_filter($title_values, "modifiedValues");
	// error_log("titles filtered " . print_r($tfiltered, true));

	saveTitles($tfiltered);

	$desfiltered = array_filter($desc_values, "modifiedValues");
	// error_log("Description values desfiltered:" . print_r($desfiltered, true));
	saveDescriptions($desfiltered);

	$tagsfiltered = array_filter($tag_values, "modifiedValues");
	saveTags($tagsfiltered);

	$customfield_valuesfiltered = modifiedValuesArray($customfield_values);
	saveCustomFields($customfield_valuesfiltered);

	$category_valuesfiltered = modifiedValuesArray($category_values);
	saveCategories($category_valuesfiltered);
}

/*TODOs:
* Leser/Autoren
* Nicht berechtigte User

-----------------------------------------------------------------------------------
6. Tests
7. Doku, welche Files alles erstellt und angepasst werden müssen
8. Integration

-----------------------------------------------------------------------------------
Weitere Features:
* Verwerfen Button: Seite neu laden
* Zip-File download der Daten für eine Kategorie, max 30 Lieder (ca. 20-30MB) // Fertig
	* Speichergröße beschränken???
	* Nur die aktuelle Seite
	* Alle Seiten in einzelne Files

-----------------------------------------------------------------------------------
* Weiteres Kategorienzuordnungsformular??? 
	--> Auswahl einer Kategorie
	--> Liedersuche, dann Klick um Hinzuzufügen
*/

wp_redirect($sendback_test);