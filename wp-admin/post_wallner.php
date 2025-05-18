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

function modifiedValues($value)
{
	return $value["origin"] != $value["value"];
}

function arrayFindObjectElement($objArray, $needleField, $needleValue)
{
	foreach ($objArray as $key => $value) {
		if ($value->$needleField === $needleValue)
			return $value;
	}

	return null;
}

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

// Read post data
$title_values = [];
$desc_values = [];
$customfield_values = [];
$tag_values = [];
$category_values = [];


$vars = $_POST;

if (gettype($vars) == "array") {

	$keys = array_keys($vars);
	foreach ($keys as $key) {
		if (str_starts_with($key, "_sf_file_title_")) {
			$el = $vars[$key];
			$exp = explode("_", $key);
			$ct = count($exp);
			$file_id = $exp[count($exp) - 1];
			$title_values[$file_id]["field"] = $key;
			if (str_contains($key, 'origin')) {
				$title_values[$file_id]["origin"] = $el;
			} else {
				$title_values[$file_id]["value"] = $el;
			}
			// error_log($key . ", file_id=" . $file_id . ", value=" . print_r($el, true));
		}
		if (str_starts_with($key, "_sf_file_description_")) {
			$el = $vars[$key];
			$exp = explode("_", $key);
			$ct = count($exp);
			$file_id = $exp[count($exp) - 1];
			$desc_values[$file_id]["field"] = $key;
			if (str_contains($key, 'origin')) {
				$desc_values[$file_id]["origin"] = $el;
			} else {
				$desc_values[$file_id]["value"] = $el;
			}
			// error_log($key . ", file_id=" . $file_id . ", value=" . print_r($el, true));
		}
		if (str_starts_with($key, "_sf_file_cf")) {
			$el = $vars[$key];
			$exp = explode("_", $key);
			$ct = count($exp);
			$file_id = $exp[count($exp) - 2];
			$cf_id = $exp[count($exp) - 1];
			$customfield_values[$file_id]["field"] = $key;
			if (str_contains($key, 'origin')) {
				$customfield_values[$file_id][$cf_id]["origin"] = $el;
			} else {
				$customfield_values[$file_id][$cf_id]["value"] = $el;
			}
			// error_log($key . ", file_id=" . $file_id . ", cf_id=" . $cf_id . ", value=" . print_r($el, true));
		}
		if (str_starts_with($key, "_sf_file_tags")) {
			$el = $vars[$key];
			$exp = explode("_", $key);
			$ct = count($exp);
			$file_id = $exp[count($exp) - 1];
			$tag_values[$file_id]["field"] = $key;
			if (str_contains($key, 'origin')) {
				$tag_values[$file_id]["origin"] = $el;
			} else {
				$tag_values[$file_id]["value"] = $el;
			}
			// error_log($key . ", file_id=" . $file_id . ", value=" . print_r($el, true));
		}
		if (str_starts_with($key, "_sf_file_cat")) {
			$el = $vars[$key];
			$exp = explode("_", $key);
			$ct = count($exp);
			$file_id = $exp[count($exp) - 2];
			$cf_id = $exp[count($exp) - 1];
			$category_values[$file_id]["field"] = $key;
			if (str_contains($key, 'origin')) {
				$category_values[$file_id][$cf_id]["origin"] = $el;
			} else {
				$category_values[$file_id][$cf_id]["value"] = $el;
			}
			// error_log($key . ", file_id=" . $file_id . ", value=" . print_r($el, true));
		}
	}
}



error_log("Title values:" . print_r($title_values, true));
error_log("Description values:" . print_r($desc_values, true));
error_log("Tag values:" . print_r($tag_values, true));
error_log("Custom Field values:" . print_r($customfield_values, true));
error_log("Category values:" . print_r($category_values, true));

// Update values
$tfiltered = array_filter($title_values, "modifiedValues");
error_log("titles filtered " . print_r($tfilt, true));

foreach ($tfiltered as $key => $value) {
	error_log("update title " . print_r($key, true));
	error_log("update value " . print_r($value, true));
	$id = intval($key);
	error_log("id, val: " . print_r($id, true) . " --> " . print_r($value["value"], true));

	$my_post = array(
		'ID' => $id,
		'post_title' => sanitize_text_field($value["value"]),
	);

	$res = wp_update_post($my_post);
	error_log("update RESULT " . print_r($res, true));
}

$desfiltered = array_filter($desc_values, "modifiedValues");
foreach ($desfiltered as $key => $value) {
	error_log("update description " . print_r($key, true));
	error_log("update value " . print_r($value, true));
	$id = intval($key);
	error_log("id, val: " . print_r($id, true) . " --> " . print_r($value["value"], true));

	$res = update_post_meta($id, '_sf_description', $value["value"]);
	error_log("update RESULT " . print_r($res, true));
}

$tagfiltered = array_filter($tag_values, "modifiedValues");
$alltags = get_terms('shared-file-tag', array('hide_empty' => 0));
error_log("  ALL_TAGS" . print_r($alltags, true));

foreach ($tagfiltered as $key => $value) {
	$id = intval($key);

	$oldTags = explode(',', $value["origin"]);
	$newTags = explode(',', $value["value"]);
	$termsToRemove = [];
	foreach ($oldTags as $key1 => $value1) {
		if ($value1 !== null && trim($value1) !== '' && !in_array($value1, $newTags)) {
			error_log("  Delete Tag from file " . $key . ": " . $value1);
			array_push($termsToRemove, $value1);
		}
	}

	if (count($termsToRemove) !== 0) {
		error_log("  Delete Tags array " . print_r($termsToRemove, true));
		wp_remove_object_terms($id, $termsToRemove, 'shared-file-tag');
	}

	$needleField = 'name';
	$termIdsAdded = [];
	foreach ($newTags as $key1 => $value1) {
		if ($value1 !== null && trim($value1) !== '') {
			$valTrim = trim($value1);
			error_log("  Insert Tag from file " . $key . ": " . $valTrim);

			$found = arrayFindObjectElement($alltags, $needleField, $valTrim);
			error_log("CONTAINS found " . print_r($found, true));
			if ($found) {
				$termId = $found->term_id;
				error_log("    Insert found termid " . print_r($termId, true));
				array_push($termIdsAdded, $found->term_id);
			} else {
				$new_tag_args = [];
				$new_tag = wp_insert_term($valTrim, 'shared-file-tag', $new_tag_args);
				error_log("    Insert result " . print_r($new_tag, true));
				if (isset($new_tag['term_id']) && $term_id = intval($new_tag['term_id'])) {
					array_push($termIdsAdded, $term_id);
				}
			}
		}
	}
	if (count($termIdsAdded) !== 0) {
		$setTagsResult = wp_set_post_terms($id, $termIdsAdded, 'shared-file-tag');
		error_log("    Insert setTagsResult " . print_r($setTagsResult, true));
	}
}


/*TODOs:
0. Checkbox-Values werden nicht genau so übergeben, CHECKED STATUS???
0. Description anzeigen und speichern

1. Speichern einer einfachen Variable
2. Finden der geänderten Werte: 
	a) einfach über die hidden inputs, VT: BE-gesteuert, NT: doppelt so viele Formulardaten
	b) hidden input variable mit Liste der geänderten Daten: VT: Weniger Formularfelder und damit Daten, NT: FE-Lösung ist fehleranfälliger, falls JS fehlerhaft
	c) optimal wäre es, die Formulardaten per ajax zu schicken (jquery)
3. Speichern aller Werte
* Welche Spalten fehlen noch: Description
4. Paging
5. Tests
6. Doku, welche Files alles erstellt und angepasst werden müssen
7. Integration
*/

wp_redirect($sendback_test);