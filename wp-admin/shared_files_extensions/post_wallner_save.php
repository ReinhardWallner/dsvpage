<?php

function saveTitles($tfiltered): void
{
	foreach ($tfiltered as $key => $value) {
		$id = intval($key);

		$my_post = array(
			'ID' => $id,
			'post_title' => sanitize_text_field($value["value"]),
		);

		$res = wp_update_post($my_post);
	}
}

function saveDescriptions($desfiltered)
{
	foreach ($desfiltered as $key => $value) {
		$id = intval($key);
		$res = update_post_meta($id, '_sf_description', $value["value"]);
		 error_log("update RESULT " . print_r($res, true));
	}
}

function saveCustomFields($customfield_values)
{
	foreach ($customfield_values as $file_id => $value) {
		foreach ($value as $cf_id => $field_value) {
			$res = update_post_meta($file_id, '_sf_file_upload_cf_' . $cf_id, $field_value["value"]);
		}
	}
}

function saveCategories($category_valuesfiltered)
{
	foreach ($category_valuesfiltered as $file_id => $value) {
		$termsToRemove = [];
		$termIdsAdded = [];
		foreach ($value as $cf_id => $field_value) {
			$t = gettype($cf_id);
			if ($field_value["origin"] == "on") {
				array_push($termsToRemove, $cf_id);
			} else {
				array_push($termIdsAdded, $cf_id);
			}
		}

		if (count($termsToRemove) !== 0) {
			$setKategoriesResult = wp_remove_object_terms($file_id, $termsToRemove, 'shared-file-category');
		}
		if (count($termIdsAdded) !== 0) {
			$setKategoriesResult = wp_set_post_terms($file_id, $termIdsAdded, 'shared-file-category');
		}
	}
}

function saveTags($tagfiltered)
{
	$alltags = get_terms('shared-file-tag', array('hide_empty' => 0));

	foreach ($tagfiltered as $key => $value) {
		$id = intval($key);

		$oldTags = explode(',', $value["origin"]);
		$newTags = explode(',', $value["value"]);
		$termsToRemove = [];
		foreach ($oldTags as $key1 => $value1) {
			if ($value1 !== null && trim($value1) !== '' && !in_array($value1, $newTags)) {
				array_push($termsToRemove, $value1);
			}
		}

		if (count($termsToRemove) !== 0) {
			wp_remove_object_terms($id, $termsToRemove, 'shared-file-tag');
		}

		$needleField = 'name';
		$termIdsAdded = [];
		foreach ($newTags as $key1 => $value1) {
			if ($value1 !== null && trim($value1) !== '') {
				$valTrim = trim($value1);

				$found = arrayFindObjectElement($alltags, $needleField, $valTrim);
				if ($found) {
					$termId = $found->term_id;
					array_push($termIdsAdded, $found->term_id);
				} else {
					$new_tag_args = [];
					$new_tag = wp_insert_term($valTrim, 'shared-file-tag', $new_tag_args);
					if (isset($new_tag['term_id']) && $term_id = intval($new_tag['term_id'])) {
						array_push($termIdsAdded, $term_id);
					}
				}
			}
		}
		if (count($termIdsAdded) !== 0) {
			$setTagsResult = wp_set_post_terms($id, $termIdsAdded, 'shared-file-tag');
		}
	}
}

?>