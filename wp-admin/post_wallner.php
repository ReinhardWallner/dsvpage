<?php
/**
 * Edit post administration panel.
 *
 * Manage Post actions: post, edit, delete, etc.
 *
 * @package WordPress
 * @subpackage Administration
 */

/** WordPress Administration Bootstrap */
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

if (isset($_POST['post_type']) && $post && $post_type !== $_POST['post_type']) {
	wp_die(__('A post type mismatch has been detected.'), __('Sorry, you are not allowed to edit this item.'), 400);
}


$vars = $_POST;
error_log("POST VALUES " . print_r($vars, true));

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


	error_log("Title values:" . print_r($title_values, true));
	error_log("Description values:" . print_r($desc_values, true));
	error_log("Tag values:" . print_r($tag_values, true));
	error_log("Custom Field values:" . print_r($customfield_values, true));
	error_log("Category values:" . print_r($category_values, true));

	// Update values
	$tfiltered = array_filter($title_values, "modifiedValues");
	error_log("titles filtered " . print_r($tfiltered, true));

	saveTitles($tfiltered);

	$desfiltered = array_filter($desc_values, "modifiedValues");
	saveDescriptions($desfiltered);

	$tagsfiltered = array_filter($tag_values, "modifiedValues");
	saveTags($tagsfiltered);

	$customfield_valuesfiltered = modifiedValuesArray($customfield_values);
	saveCustomFields($customfield_valuesfiltered);

	$category_valuesfiltered = modifiedValuesArray($category_values);
	saveCategories($category_valuesfiltered);
}

/*TODOs:
0. Checkbox-Values werden nicht genau so übergeben, CHECKED STATUS???
0. Description anzeigen und speichern
0. Umlaute, Sonderzeichen in allen Feldern prüfen

1. Speichern einer einfachen Variable
2. Finden der geänderten Werte: 
	a) einfach über die hidden inputs, VT: BE-gesteuert, NT: doppelt so viele Formulardaten
	b) hidden input variable mit Liste der geänderten Daten: VT: Weniger Formularfelder und damit Daten, NT: FE-Lösung ist fehleranfälliger, falls JS fehlerhaft
	c) optimal wäre es, die Formulardaten per ajax zu schicken (jquery)
3. Speichern aller Werte
* Welche Spalten fehlen noch: Description
	* Info, dass Speichern erfolgreich war
4. Paging
5. Verwerfen Button (reload)
6. Suche nach Text und Kategorien-Filterung
6. Tests
7. Doku, welche Files alles erstellt und angepasst werden müssen
8. Integration
*/

wp_redirect($sendback_test);