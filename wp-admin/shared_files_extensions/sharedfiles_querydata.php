<?php

function queryData($parameters)
{
	$nurKategorienAnzeigen = $parameters["nurKategorienAnzeigen"];
	$onlyModifySingleField = null;
	if(array_key_exists("onlyModifySingleField", $parameters))
		$onlyModifySingleField = $parameters["onlyModifySingleField"];

	$select = "SELECT distinct p.id, p.post_title
FROM `wp_posts` p
left join `wp_postmeta` m_cf on p.id=m_cf.post_id and m_cf.meta_key like '%_sf_file_upload_cf%' 
left join `wp_postmeta` m_desc on p.id=m_desc.post_id and m_desc.meta_key like '%_sf_description%' 
left join `wp_term_relationships` trel_tag on trel_tag.object_id=p.id
left join `wp_terms` t_tag on trel_tag.term_taxonomy_id=t_tag.term_id
left join `wp_term_taxonomy` tax_tag on tax_tag.term_id=t_tag.term_id and tax_tag.taxonomy='shared-file-tag'
left join `wp_term_relationships` trel_cat on trel_cat.object_id=p.id
left join `wp_terms` t_cat on trel_cat.term_taxonomy_id=t_cat.term_id
left join `wp_term_taxonomy` tax_cat on tax_cat.term_id=t_cat.term_id and tax_cat.taxonomy='shared-file-category'
where p.post_type='shared_file'";
	$filter = "";

	if ($parameters["search"]) {
		if ($nurKategorienAnzeigen == true) {
			$filter = "and 
			(p.post_title like '%{$parameters["search"]}%')";
		} else if($onlyModifySingleField != null && $onlyModifySingleField != "notselected") {
			if(str_starts_with($onlyModifySingleField, "file_upload_custom_field_")){
				$rest = str_replace("file_upload_custom_field_", "_sf_file_upload_cf_", $onlyModifySingleField);
				$filter = "and 
				(p.post_title like '%{$parameters["search"]}%' 
				or (m_cf.meta_key='{$rest}' and m_cf.meta_value like '%{$parameters["search"]}%')
				)";			
			} else if(str_starts_with($onlyModifySingleField, 'description')){
				$filter = "and 
				(p.post_title like '%{$parameters["search"]}%' 
				or m_desc.meta_value like '%{$parameters["search"]}%'
				)";	
			} else if(str_starts_with($onlyModifySingleField, 'tags')){
				$filter = "and 
				(p.post_title like '%{$parameters["search"]}%' 
				or (t_tag.name like '%{$parameters["search"]}%' and tax_tag.taxonomy='shared-file-tag')
				)";	
			}				
		} else {
			$filter = "and 
			(p.post_title like '%{$parameters["search"]}%' 
			or (t_tag.name like '%{$parameters["search"]}%' and tax_tag.taxonomy='shared-file-tag')
			or m_desc.meta_value like '%{$parameters["search"]}%'
			or m_cf.meta_value like '%{$parameters["search"]}%'
			)";			
		}
	}

	if ($parameters["category"]) {
		$cat = $parameters["category"];
		$filter .= "and (t_cat.slug like '%{$cat}%' and tax_cat.taxonomy='shared-file-category')";
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
	
	$wpdb = $parameters["wpdb"];
	$queryCountResult = $wpdb->get_results($queryCount);
	$total = array_column($queryCountResult, 'total')[0];
	// error_log("query " . $query);

	$queryResult = $wpdb->get_results($query);
	$ids = array_column($queryResult, 'id');
	// error_log("ids " . print_r($ids, true));
	
	$data = array();
	$data["total"] = $total;

	$headRow = array();
	$headRowKat = array();
	$headRowSingleFields = array();
	array_push($headRow, "Id");
	array_push($headRowKat, "Id");
	array_push($headRowSingleFields, "Id");
	array_push($headRow, esc_html__('Title', 'shared-files'));
	array_push($headRowKat, esc_html__('Title', 'shared-files'));
	array_push($headRowSingleFields, esc_html__('Title', 'shared-files'));
	array_push($headRow, esc_html__('Description', 'shared-files'));
		
	$keys = array(0 => "file_id", 1 => "title", 2 => "description");

	$custom_fields_cnt = $parameters["custom_fields_cnt"];
	$s = $parameters["settings"];

	// Custom Fields Header
	for ($n = 1; $n < $custom_fields_cnt; $n++) {
		if (isset($s['file_upload_custom_field_' . $n]) && $cf_title = sanitize_text_field($s['file_upload_custom_field_' . $n])) {
			array_push($headRow, $cf_title);
			array_push($keys, array("custom_field" => $n));

			if($onlyModifySingleField != null && $onlyModifySingleField != "notselected" &&
				str_starts_with($onlyModifySingleField, "file_upload_custom_field_") &&
				str_ends_with($onlyModifySingleField, $n)) {
				array_push($headRowSingleFields, $cf_title);
			}
		}
	}

	// Tags Header
	array_push($headRow, esc_html__('Tags', 'shared-files'));
	array_push($keys, "tags");

	// Categories Header
	$allcategories = $parameters["allcategories"];
	foreach ($allcategories as $category) {
		array_push($headRow, $category->name);
		array_push($headRowKat, $category->name);
		array_push($keys, array("category" => $category->term_id));
	}

	if($onlyModifySingleField != null && $onlyModifySingleField != "notselected") {
		if(str_starts_with($onlyModifySingleField, 'description')){
			array_push($headRowSingleFields, esc_html__('Description', 'shared-files'));
		} else if(str_starts_with($onlyModifySingleField, 'tags')){
			array_push($headRowSingleFields, esc_html__('Tags', 'shared-files'));
		}	
	}

	$data["headrow"] = $headRow;
	$data["headrowKat"] = $headRowKat;
	$data["headRowSingleFields"] = $headRowSingleFields;
	
	$data["keys"] = $keys;
	//error_log("data " . print_r($data, true));
	
	if ($total > 0) {
		$args = array(
			'post_type' => 'shared_file',
			'post__in' => $ids,
			'posts_per_page' => -1 // unbegrenzt
		);

		$the_query_terms = new WP_Query($args);

		if ($the_query_terms->have_posts()):
			while ($the_query_terms->have_posts()):
				$the_query_terms->the_post();
				$file_id = intval(get_the_id());
	
				$row = array();
				$row["file_id"] = $file_id;
				$title = get_the_title();
				$row["title"] = $title;

				// Custom fields
				$c = get_post_custom($file_id);
				$desc = $c["_sf_description"][0];
				if ($desc !== null && trim($desc) !== '') {
					$desc = str_replace('<p>', '', $desc);
					$desc = str_replace('</p>', '', $desc);
				}
				$row["description"] = $desc;

				$row["filename"] = $c["_sf_filename"][0];

				$custom_fields_cntint = intval($parameters['custom_fields_cnt']);
				for ($n = 1; $n < $custom_fields_cntint; $n++) {
					$val = "";
					if (isset($s['file_upload_custom_field_' . $n]) && $cf_title = sanitize_text_field($s['file_upload_custom_field_' . $n])) {
						if (isset($c['_sf_file_upload_cf_' . $n]) && $c['_sf_file_upload_cf_' . $n]) {
							$val = sanitize_text_field($c['_sf_file_upload_cf_' . $n][0]);
						}
					}
					$row["custom_field"][$n] = $val;
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
					}

				}

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