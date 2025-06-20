<?php
/**
 * TODO.
 *
 * TODO Manage Post actions: post, edit, delete, etc.
 *
 * @package WALLNER
 * @subpackage SharedFileExtension
 */


class SharedFilesExtensionsPublicAjax
{

	public function sf_extensions_get_files()
	{
		// error_log("sf_extensions_get_files");
		$html = "<p>test</p>";
		$html_allowed_tags = [
			'p' => [
			]
		];
		echo wp_kses($html, $html_allowed_tags);
	}
}

function array_push_assoc(&$array, $key, $value)
{
	$array[$key] = $value;
	return $array;
}

function sf_extensions_get_files2()
{
	error_log("sf_extensions_get_files 2 " . print_r($_POST, true));

	$tax_query[] = array(
		'taxonomy' => 'post_tag',
		'field' => 'slug',
		'terms' => $_POST["searchField"],
		'operator' => 'LIKE',
		'include_children' => false
	);

	// TAGS TODO:
	// Alle Tags abfragen, auf Like, filtern auf Tags in 
	$args = array(
		'post_type' => 'shared_file',
		'posts_per_page' => $_POST["posts_per_page"],
		'paged' => $_POST["paged"],
		's' => $_POST["searchField"],
		'tax_query' => $tax_query,
	);
	$custom_fields_cnt = $_POST["custom_fields_cnt"];

	$s = get_option('shared_files_settings');
	// error_log("sf_extensions_get_files s " . print_r($s, true));
	$tag_slug = 'post_tag';
	if (isset($s['tag_slug']) && $s['tag_slug']) {
		$tag_slug = sanitize_title($s['tag_slug']);
	}


	$taxonomy_slug = 'shared-file-category';
	$allcategories = get_terms($taxonomy_slug, array('hide_empty' => 0));

	// $tag_slug = 'post_tag';
	$tags1 = get_terms(array(
		'taxonomy' => 'post_tag',
		'hide_empty' => false,
		'name__like' => $_POST["searchField"]
	));
	// $tags = get_terms($tag_slug);
	error_log("sf_extensions_get_files tags " . print_r($tags1, true));

	error_log("sf_extensions_get_files args for the query " . print_r($args, true));
	$the_query_terms = new WP_Query($args);
	//  error_log("sf_extensions_get_files the_query_terms" . print_r($the_query_terms, true));
	$result = [];
	if ($the_query_terms->have_posts()):
		while ($the_query_terms->have_posts()):
			$the_query_terms->the_post();
			$file_id = intval(get_the_id());
			$title = get_the_title();
			$c = get_post_custom($file_id);

			// error_log("sf_extensions_get_files c " . print_r($c, true));
			$description = $c["_sf_description"][0];
			if ($description !== null && trim($description) !== '') {
				$description = str_replace('<p>', '', $description);
				$description = str_replace('</p>', '', $description);
			}

			$element = array(
				"file_id" => $file_id,
				"title" => $title,
				"description" => $description
			);

			$custom_fields_cnt = intval($s['custom_fields_cnt']) + 1;
			for ($n = 1; $n < $custom_fields_cnt; $n++) {
				$val = "";
				// error_log("sf_extensions_get_files custom_fields_cnt " . $n . "" . print_r($s['file_upload_custom_field_' . $n], true));
				if (isset($s['file_upload_custom_field_' . $n]) && $cf_title = sanitize_text_field($s['file_upload_custom_field_' . $n])) {
					// error_log("sf_extensions_get_files _sf_file_upload_cf_ " . $n . "" . print_r($c['_sf_file_upload_cf_' . $n], true));
					if (isset($c['_sf_file_upload_cf_' . $n]) && $c['_sf_file_upload_cf_' . $n]) {
						$val = sanitize_text_field($c['_sf_file_upload_cf_' . $n][0]);
						array_push_assoc($element, "cf_" . $n, $val);
					}
				}
			}

			$tags = get_the_terms($file_id, $tag_slug);
			error_log("tags for fileId=" . $file_id . ": " . print_r($tags, true));
			$tagValue = "";
			if ($tags) {
				foreach ($tags as $tag) {
					$tagValue .= sanitize_text_field($tag->name) . ', ';
				}
			}
			if (strlen($tagValue) > 0) {
				$tagValue = substr($tagValue, 0, strlen($tagValue) - 2);
				array_push_assoc($element, "tags", $tagValue);
			}

			if (is_array($allcategories)) {
				$categories = get_the_terms($file_id, 'shared-file-category');
				// error_log("categories: " . print_r($categories, true));

				foreach ($allcategories as $category) {
					// error_log("category: " . print_r($category, true));
					$exists = false;
					if (is_array($categories)) {
						foreach ($categories as $myCategory) {
							if ($myCategory->name == $category->name) {
								$exists = true;
								break;
							}
						}
					}
					array_push_assoc($element, "cat_term_id_" . $category->term_id, $exists);
				}
			}

			array_push($result, $element);
		endwhile;
	endif;

	// error_log("sf_extensions_get_files result " . print_r($result, true));

	$pagination_active = 1;
	$_GET['_page'] = $_POST["paged"];
	// $the_query_terms->max_num_pages = 
	$pagination = SharedFilesPublicPagination::getPagination($pagination_active, $the_query_terms, 'default');
	// array_push_assoc($result, "pagination", $pagination);

	$response = array(
		"data" => $result,
		"pagination" => $pagination
	);

	$html = json_encode($response);
	// error_log("sf_extensions_get_files result html " . print_r($html, true));
	echo wp_send_json($html);
	// $html_allowed_tags = [
	// 	'p' => [
	// 	]
	// ];
	// echo wp_kses($html, $html_allowed_tags);
}