<?php

function saveTitles($tfiltered): void
{
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
}

function saveDescriptions($desfiltered)
{
	foreach ($desfiltered as $key => $value) {
		error_log("update description " . print_r($key, true));
		error_log("update value " . print_r($value, true));
		$id = intval($key);
		error_log("id, val: " . print_r($id, true) . " --> " . print_r($value["value"], true));

		$res = update_post_meta($id, '_sf_description', $value["value"]);
		error_log("update RESULT " . print_r($res, true));
	}
}

function saveCustomFields($customfield_values)
{
	foreach ($customfield_values as $file_id => $value) {
		// error_log("saveCustomFields fileid " . $file_id);
		foreach ($value as $cf_id => $field_value) {
			// error_log("saveCustomFields cf_id " . $cf_id . ", " . print_r($field_value, true));
			$res = update_post_meta($file_id, '_sf_file_upload_cf_' . $cf_id, $field_value["value"]);
			// error_log("update RESULT " . print_r($res, true));
		}
	}
}

function saveCategories($category_valuesfiltered)
{
	foreach ($category_valuesfiltered as $file_id => $value) {
		 error_log("saveCustomFields fileid " . $file_id);
		foreach ($value as $cf_id => $field_value) {
			 error_log("saveCustomFields cf_id " . $cf_id . ", " . print_r($field_value, true));
			// $res = update_post_meta($file_id, '_sf_file_upload_cf_' . $cf_id, $field_value["value"]);
			//  error_log("update RESULT " . print_r($res, true));
		}
	}
}

function saveTags($tagfiltered)
{
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
}

?>