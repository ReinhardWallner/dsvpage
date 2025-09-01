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
		//  error_log("update RESULT " . print_r($res, true));
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
	// error_log("saveCategories input ". print_r($category_valuesfiltered, true));
	 
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

		//  error_log("saveCategories termsToRemove ". print_r($termsToRemove, true));
		//  error_log("saveCategories termIdsAdded ". print_r($termIdsAdded, true));
		if (count($termsToRemove) !== 0) {
			$setKategoriesResult = wp_remove_object_terms($file_id, $termsToRemove, 'shared-file-category');
		}
		if (count($termIdsAdded) !== 0) {
			$setKategoriesResult = wp_set_post_terms($file_id, $termIdsAdded, 'shared-file-category', true);
		}
	}
}

function replaceCategories($categorySlug, $fileIds){
	// error_log("replaceCategories ". print_r($categorySlug, true) . ", " . print_r($fileIds, true));

	$term = get_term_by('slug', $categorySlug, 'shared-file-category');
	// error_log("replaceCategories categ ". print_r($term, true));

	$files = get_posts([
		'post_type' => 'shared_file', // Custom Post Type
		'posts_per_page' => -1,       // Alle Files
			'tax_query' => [
			[
				'taxonomy' => 'shared-file-category',
				'field'    => 'slug', // Alternativ: 'term_id'
				'terms'    => $categorySlug,
			],
		],
	]);
	
	foreach ($files as $file) {
		// error_log('ID: ' . $file->ID . ' - Titel: ' . $file->post_title . '<br>');
		if($fileIds != null && in_array($file->ID, $fileIds)){
			// error_log('ID: ' . $file->ID . ' is contained. nothting to do');
		} else{
			// error_log('ID: ' . $file->ID . ' is not contained --> To remove termid=' . $term->term_id);
			wp_remove_object_terms($file->ID, [$term->term_id], 'shared-file-category');
		}
	}

	if($fileIds != null){
		foreach ($fileIds as $fileId) {
			if(array_filter($files, fn($file) => (int)$file->ID === (int)$fileId)){
				// error_log('fileId: ' . $fileId . ' is contained. nothting to do');
			} else{
				// error_log('fileId: ' . $fileId . ' is not contained --> To ADD ' . $term->term_id);
				$res = wp_set_post_terms((int)$fileId, [(int)$term->term_id], 'shared-file-category', true);
				// error_log('fileId: ' . $fileId . ' added result ' . print_r($res, true));
			}
		}
	}
}

function createNewCategory($newCategoryName, $parentSlug){
	// Prüfen, ob Kategorie schon existiert
	if (!term_exists($newCategoryName, 'shared-file-category')) {
		if($parentSlug){
			$parent = get_term_by( 'slug', $parentSlug, 'shared-file-category' );
			$result = wp_insert_term(
				$newCategoryName, 		// Name
				'shared-file-category',  // Taxonomie
				array( 'parent' => $parent->term_id )
			);
		} else{
			$result = wp_insert_term(
				$newCategoryName, 		// Name
				'shared-file-category'  // Taxonomie
			);
		}
		if (!is_wp_error($result)) {
			// $result enthält 'term_id' und 'term_taxonomy_id'
			$term_id = $result['term_id'];
	
			// Term-Daten abfragen, um z.B. den Slug zu bekommen
			$term = get_term($term_id, 'shared-file-category');
			if (!is_wp_error($term)) {
				return $term->slug;
			}
		}
	}	
}

function deleteCategory($categoryName){
	error_log("deleteCategory name " . print_r($categoryName, true));
	$term = get_term_by('slug', $categoryName, 'shared-file-category');
	error_log("deleteCategory term" . print_r($categoryName, true));
	if (!$term || is_wp_error($term)) {
        return false;
    }
	error_log("deleteCategory term" . print_r($term, true));
	return wp_delete_term($term->term_id, 'shared-file-category');
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