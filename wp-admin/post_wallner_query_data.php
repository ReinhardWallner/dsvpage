<?php

function queryData($parameters)
{
	// error_log("queryData: parameters" . print_r($parameters, true));

	$select = "SELECT distinct p.id, p.post_title
FROM `wp_posts` p
left join `wp_postmeta` m on p.id=m.post_id and (m.meta_key like '%_sf_file_upload_cf%' or m.meta_key like '%_sf_description%')
left join `wp_term_relationships` trel on trel.object_id=p.id
left join `wp_terms` t on trel.term_taxonomy_id=t.term_id
left join `wp_term_taxonomy` tax on tax.term_id=t.term_id and (tax.taxonomy='shared-file-tag' or tax.taxonomy='shared-file-category')
where post_type='shared_file'";
	$filter = "";

	if ($parameters["search"]) {
		$filter = "and 
	(p.post_title like '%{$parameters["search"]}%' 
	or t.name like '%{$parameters["search"]}%'
	or (m.meta_key='_sf_description' and m.meta_value like '%{$parameters["search"]}%')
	or (m.meta_key like '_sf_file_upload_cf%' and m.meta_value like '%{$parameters["search"]}%')
	)";
	}
	if ($parameters["category"]) {
		$cat = $parameters["category"];
		$filter .= "and (tax.taxonomy='shared-file-category' and t.name like '%{$cat}%')";
	}

	$limit = $parameters["posts_per_page"];
	$offset = ($parameters["paged"] - 1) * $parameters["posts_per_page"];

	$queryCount = "Select count(*) as total from (" . $select . "\n" . $filter . ") qu";
	$query = $select . "\n" . $filter;

	$paged = $parameters["paged"];
	if ($paged > 0) {
		$pageparams = "order by p.post_title
limit {$limit} offset {$offset}";
	} else {
		$pageparams = "order by p.post_title";
	}
	$query .= "\n" . $pageparams;


	//  error_log("editmeta SELECT COUNT: " . print_r($queryCount, true));
	$wpdb = $parameters["wpdb"];
	$queryCountResult = $wpdb->get_results($queryCount);
	$total = array_column($queryCountResult, 'total')[0];
	// error_log("editmeta queryCountResult: " . print_r($queryCountResult, true));
	//error_log("editmeta queryCountResult 2: " . print_r($total, true));

	// error_log("editmeta SELECT STATEMENT: " . print_r($query, true));
	$queryResult = $wpdb->get_results($query);
	//error_log("editmeta queryResult: " . print_r($queryResult, true));
	$ids = array_column($queryResult, 'id');
	// error_log("editmeta queryResult: " . print_r($ids, true));


	$data = array();
	$data["total"] = $total;

	$headRow = array();
	array_push($headRow, "Id");
	array_push($headRow, "Titel");
	array_push($headRow, "Beschreibung");
	$keys = array(0 => "file_id", 1 => "title", 2 => "description");

	$custom_fields_cnt = $parameters["custom_fields_cnt"];
	$s = $parameters["settings"];

	// Custom Fields Header
	for ($n = 1; $n < $custom_fields_cnt; $n++) {
		if (isset($s['file_upload_custom_field_' . $n]) && $cf_title = sanitize_text_field($s['file_upload_custom_field_' . $n])) {
			array_push($headRow, $cf_title);
			array_push($keys, array("custom_field" => $n));
		}
	}

	// Tags Header
	array_push($headRow, "Tags");
	// $keys["tags"] = "Tags";
	array_push($keys, "tags");

	// Categories Header
	$allcategories = $parameters["allcategories"];
	foreach ($allcategories as $category) {
		array_push($headRow, sanitize_title($category->slug));
		array_push($keys, array("category" => $category->term_id));
	}
	// array_push($data, $headRow);
	$data["headrow"] = $headRow;
	$data["keys"] = $keys;

	// error_log("allcategories: " . print_r($allcategories, true));
	if ($total > 0) {
		$args = array(
			'post_type' => 'shared_file',
			'post__in' => $ids
		);

		$the_query_terms = new WP_Query($args);

		if ($the_query_terms->have_posts()):
			while ($the_query_terms->have_posts()):
				$the_query_terms->the_post();
				$file_id = intval(get_the_id());
				$row = array();
				// array_push($row, $file_id);
				$row["file_id"] = $file_id;
				$title = get_the_title();
				// array_push($row, $title);
				$row["title"] = $title;

				// Custom fields
				$c = get_post_custom($file_id);
				$desc = $c["_sf_description"][0];
				if ($desc !== null && trim($desc) !== '') {
					$desc = str_replace('<p>', '', $desc);
					$desc = str_replace('</p>', '', $desc);
				}
				// array_push($row, $desc);
				$row["description"] = $desc;

				$custom_fields_cntint = intval($parameters['custom_fields_cnt']);
				for ($n = 1; $n < $custom_fields_cntint; $n++) {
					$val = "";
					if (isset($s['file_upload_custom_field_' . $n]) && $cf_title = sanitize_text_field($s['file_upload_custom_field_' . $n])) {
						if (isset($c['_sf_file_upload_cf_' . $n]) && $c['_sf_file_upload_cf_' . $n]) {
							$val = sanitize_text_field($c['_sf_file_upload_cf_' . $n][0]);
						}
					}
					// array_push($row, $val);
					$row["custom_field"][$n] = $val;
					// error_log("custom_field: " . $n . ", " . $val . print_r($row, true));
				}

				// Tags
				$tags = get_the_terms($file_id, $parameters["tag_slug"]);
				$tagValue = "";
				if ($tags) {
					foreach ($tags as $tag) {
						$tagValue .= sanitize_text_field($tag->name) . ', ';
					}
				}
				if (strlen($tagValue) > 0) {
					$tagValue = substr($tagValue, 0, strlen($tagValue) - 2);
				}

				$row["tags"] = $tagValue;

				// Categories
				if (is_array($allcategories)) {
					$categories = get_the_terms($file_id, 'shared-file-category');
					// error_log("categories: for file id " . $file_id . ", " . print_r($categories, true));

					foreach ($allcategories as $category) {
						$catName = sanitize_title($category->name);
						$term_id = $category->term_id;
						$exists = false;
						if (is_array($categories)) {
							foreach ($categories as $myCategory) {
								$myCatName = sanitize_title($myCategory->name);
								if ($myCatName == $catName) {
									$exists = true;
									break;
								}
							}
						}
						$catValue = "";
						if ($exists) {
							$catValue = "Ja";
						}

						$row["category"][$term_id] = $catValue;
						array_push($keys, array("category" => $term_id));
						// error_log("    Category: for fileid " . $file_id . ", " . $catName . ": " . print_r($catValue, true));
					}

				}

				// array_push($data, $row);
				$array_index = array_search($file_id, $ids);
				$data[$array_index] = $row;
			?>
			<?php endwhile;

			// add rows to output

		else:
			array_push($data, "<p>Keine Daten gefunden</p>");
		endif;

		$data["args"] = $args;
	} else {
		array_push($data, "<p>Keine Daten gefunden</p>");
	}

	return $data;
}

?>