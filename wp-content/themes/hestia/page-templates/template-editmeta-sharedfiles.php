<?php
/**
 * Template Name: Template Shared Files Edit
 *
 * The template for the full-width page.
 *
 * @package Hestia
 * @since Hestia 1.0
 */

get_header();

/**
 * Don't display page header if header layout is set as classic blog.
 */
do_action('hestia_before_single_page_wrapper');

?>

<?php

$inputArray = array();
$checkboxArray = array();

function getInputField($file_id, $cf_id, $name, $value, &$inputArray, $hidden = false)
{
	$obj = new stdClass();
	$obj->file_id = $file_id;
	if ($cf_id != null)
		$obj->cf_id = $cf_id;
	$obj->value = $value;
	array_push($inputArray, [$name => $obj]);

	if ($hidden) {
		if ($value)
			return '<input type="hidden" name="' . $name . '" id="' . $name . '" value="' . $value . '" title="' . $value . '"/>';
		else
			return '<input type="hidden" name="' . $name . '" id="' . $name . '"/>';
	} else {
		if ($value)
			return '<input type="text" name="' . $name . '" id="' . $name . '" value="' . $value . '" title="' . $value . '" onchange="inputOnChange(this.name, this.value)" />';
		else
			return '<input type="text" name="' . $name . '" id="' . $name . '" onchange="inputOnChange(this.name, this.value.value)" />';
	}
}

function getCheckboxField($file_id, $cat_name, $name, $value, &$checkboxArray, $hidden = false)
{
	$obj = new stdClass();
	$obj->file_id = $file_id;
	$obj->cat_name = $cat_name;
	$obj->value = $value;
	array_push($checkboxArray, [$name => $obj]);

	if ($hidden) {
		if ($value)
			return '<input type="hidden" name="' . $name . '" id="' . $name . '" checked value="' . $value . '" />';
		else
			return '<input type="hidden" name="' . $name . '" id="' . $name . '" value="' . $value . '" "/>';
	} else {
		if ($value)
			return '<input type="checkbox" name="' . $name . '" id="' . $name . '" checked value="' . $value . '" onchange="checkboxOnChange(this.name, this.value)"/>';
		else
			return '<input type="checkbox" name="' . $name . '" id="' . $name . '" value="' . $value . '" onchange="checkboxOnChange(this.name, this.checked)"/>';
	}

}

function addTitleField(&$table, $file_id, $title, &$inputArray)
{
	$table .= '<td>';
	$table .= getInputField($file_id, null, '_sf_file_title_' . $file_id, $title, $inputArray);
	$table .= getInputField($file_id, null, '_sf_file_title_origin_' . $file_id, $title, $inputArray, true);
	$table .= '</td>';
}

function addDescriptionField(&$table, $file_id, $desc, &$inputArray)
{
	$table .= '<td>';
	$table .= getInputField($file_id, null, '_sf_file_description_' . $file_id, $desc, $inputArray);
	$table .= getInputField($file_id, null, '_sf_file_description_origin_' . $file_id, $desc, $inputArray, true);
	$table .= '</td>';
}

function addCustomFieldField(&$table, $file_id, $n, $val, &$inputArray): void
{
	$table .= '<td>';
	$table .= getInputField($file_id, $n, '_sf_file_cf_' . $file_id . '_' . $n, $val, $inputArray);
	$table .= getInputField($file_id, $n, '_sf_file_cf_origin_' . $file_id . '_' . $n, $val, $inputArray, true);
	$table .= '</td>';
}

function addTagsField(&$table, $file_id, $tagValue, &$inputArray): void
{
	$table .= '<td>';
	$table .= getInputField($file_id, null, '_sf_file_tags_' . $file_id, $tagValue, $inputArray);
	$table .= getInputField($file_id, null, '_sf_file_tags_origin_' . $file_id, $tagValue, $inputArray, true);
	$table .= '</td>';
}

function addCategoryField(&$table, $file_id, &$category, $catValue, &$checkboxArray): void
{
	// error_log("addCategoryField " . $file_id . ": " . print_r($category, true) . ", catvalue " . $catValue . ", inputArr " . print_r($inputArray, true));
	$table .= '<td>';
	$table .= getCheckboxField($file_id, $category->name, '_sf_file_cat_' . $file_id . '_' . $category->term_id, $catValue, $checkboxArray);
	$table .= getCheckboxField($file_id, $category->name, '_sf_file_cat_origin_' . $file_id . '_' . $category->term_id, $catValue, $checkboxArray, true);
	$table .= '</td>';
}

?>

<?php
$posts_per_page = 3;
$paged = 1;

error_log("TEMPLATE START _POST " . print_r($_POST, true));
error_log("TEMPLATE START _GET " . print_r($_GET, true));

if (isset($_GET['_page']) && $_GET['_page']) {
	$paged = (int) $_GET['_page'];
} elseif (get_query_var('paged')) {
	$paged = absint(get_query_var('paged'));
}

$s = get_option('shared_files_settings');
$custom_fields_cnt = intval($s['custom_fields_cnt']) + 1;

$taxonomy_slug = 'shared-file-category';
$allcategories = get_terms($taxonomy_slug, array('hide_empty' => 0));

$search = null;
if (isset($_POST['searchField'])) {
	$search = $_POST["searchField"];
} else if (isset($_GET['searchField'])) {
	$search = $_GET["searchField"];
}
$category = null;
if (isset($_POST['sf_category'])) {
	$category = $_POST["sf_category"];
} else if (isset($_GET['sf_category'])) {
	$category = $_GET["sf_category"];
}

error_log("TEMPLATE search " . print_r($search, true) . ", cat: " . print_r($category, true));
?>

<!-- pass variables to javascript -->
<script>
	var paged = <?php echo json_encode($paged) ?>;
	var posts_per_page = <?php echo json_encode($posts_per_page) ?>;
	var custom_fields_cnt = <?php echo json_encode($custom_fields_cnt) ?>;
	var allCategories = <?php echo json_encode($allcategories) ?>;
	var insertSearchText = <?php echo json_encode($search) ?>;
</script>


<?php
$searchFields = '<form
   id="the-redirect-form" 
   method="post" 
   action="http://localhost:8081/dsvpage/test-sharedfiles-edit" 
   enctype="multipart/form-data">';

$searchFields .= '<div>';
$searchFields .= '<table style="width: 100%">
    <colgroup>
       <col span="1" style="width: 30%;">
       <col span="1" style="width: 70%;">
    </colgroup><tr><td>';

if ($search) {
	$searchFields .= '<input type="text" shared-files-search-files-v2" name="searchField" autofocus placeholder="' . esc_html__('Search files...', 'shared-files') . '" value="' . $search . '" oninput="onInputSearchText()" >';
} else {
	$searchFields .= '<input type="text" shared-files-search-files-v2" name="searchField" autofocus placeholder="' . esc_html__('Search files...', 'shared-files') . '" oninput="onInputSearchText()" >';
}

$searchFields .= '</td><td>';

$categories_order = 'ASC';
$argsCategoryCombo = array(
	'taxonomy' => 'shared-file-category',
	'name' => 'sf_category',
	'show_option_all' => esc_attr__('Choose category', 'shared-files'),
	'hierarchical' => true,
	'class' => 'shared-files-category-select select_v2',
	'echo' => false,
	'value_field' => 'slug',
	// 'exclude'         => $exclude_cat,
	'selected' => $category,

	//   'orderby'         => $categories_orderby,
	'order' => $categories_order,
	'hide_if_empty' => true

);

$categoryDropdowm = wp_dropdown_categories($argsCategoryCombo);
$endIndex = strpos($categoryDropdowm, ">");
$categoryDropdowm = str_replace("class='shared-files-category-select select_v2'>", 'class="shared-files-category-select select_v2" onchange="onCategoryChange()">', $categoryDropdowm);
error_log("TEMPLATE categoryDropdowm " . print_r($categoryDropdowm, true) . ", endindex=" . $endIndex);
$searchFields .= '<div class="shared-files-category-select-container">';
$searchFields .= $categoryDropdowm;
$searchFields .= '</div></td></tr></div>';

$searchFields .= '<input type="hidden" name="custom" value="Something custom">
</form>';

$table = '<form method="post" name="myForm" enctype="application/x-www-form-urlencoded" action="http://localhost:8081/dsvpage/wp-admin/post_wallner.php">';
$table .= '<table name="dataTable" style="margin: 10px;">';
$table .= "<tr><td>Id</td><td>Titel</td><td>Beschreibung</td>";


$tag_slug = 'post_tag';
if (isset($s['tag_slug']) && $s['tag_slug']) {
	$tag_slug = sanitize_title($s['tag_slug']);
}

$args = null;

$select = "SELECT distinct p.id, p.post_title
FROM `wp_posts` p
left join `wp_postmeta` m on p.id=m.post_id and (m.meta_key like '%_sf_file_upload_cf%' or m.meta_key like '%_sf_description%')
left join `wp_term_relationships` trel on trel.object_id=p.id
left join `wp_terms` t on trel.term_taxonomy_id=t.term_id
left join `wp_term_taxonomy` tax on tax.term_id=t.term_id and (tax.taxonomy='shared-file-tag' or tax.taxonomy='shared-file-category')
where post_type='shared_file'";
$filter = "";

if ($search) {
	$filter = "and 
	(p.post_title like '%{$search}%' 
	or t.name like '%{$search}%'
	or (m.meta_key='_sf_description' and m.meta_value like '%{$search}%')
	or (m.meta_key like '_sf_file_upload_cf%' and m.meta_value like '%{$search}%')
	)";
}
if ($category) {
	$filter .= "and (tax.taxonomy='shared-file-category' and t.name like '%{$category}%')";
}

$limit = $posts_per_page;
$offset = ($paged - 1) * $posts_per_page;
//  if($paged > 1)
$pageparams = "order by p.post_title
limit {$limit} offset {$offset}";

$queryCount = "Select count(*) as total from (" . $select . "\n" . $filter . ") qu";
$query = $select . "\n" . $filter . "\n" . $pageparams;

//error_log("editmeta SELECT COUNT: " . print_r($queryCount, true));
$queryCountResult = $wpdb->get_results($queryCount);
$total = array_column($queryCountResult, 'total')[0];
// error_log("editmeta queryCountResult: " . print_r($queryCountResult, true));
error_log("editmeta queryCountResult 2: " . print_r($total, true));

// error_log("editmeta SELECT STATEMENT: " . print_r($query, true));
$queryResult = $wpdb->get_results($query);
// error_log("editmeta queryResult: " . print_r($queryResult, true));
$ids = array_column($queryResult, 'id');
// error_log("editmeta queryResult: " . print_r($ids, true));

$fileIds = [];

$pagination = "";

if ($total > 0) {
	$args = array(
		'post_type' => 'shared_file',
		'post__in' => $ids
	);

	// Custom Fields Header
	for ($n = 1; $n < $custom_fields_cnt; $n++) {
		if (isset($s['file_upload_custom_field_' . $n]) && $cf_title = sanitize_text_field($s['file_upload_custom_field_' . $n])) {
			$table .= "<td>" . $cf_title . "</td>";
		}

	}

	// Tags Header
	$table .= "<td>Tags</td>";

	// Categories Header
	foreach ($allcategories as $category) {
		$table .= '<td>' . sanitize_title($category->slug) . '</td>';
	}
	$table .= "</tr>";
	$tableRows = [];
	error_log("editmeta args for the query" . print_r($args, true));
	$the_query_terms = new WP_Query($args);

	if ($the_query_terms->have_posts()):
		while ($the_query_terms->have_posts()):
			$the_query_terms->the_post();
			$file_id = intval(get_the_id());
			$row = "";
			$row .= "<tr><td>" . $file_id . "</td>";
			$title = get_the_title();
			addTitleField($row, $file_id, $title, $inputArray);

			error_log("FILE ID " . print_r($file_id, true));
			// Custom fields
			$c = get_post_custom($file_id);
			$desc = $c["_sf_description"][0];
			if ($desc !== null && trim($desc) !== '') {
				$desc = str_replace('<p>', '', $desc);
				$desc = str_replace('</p>', '', $desc);
			}

			addDescriptionField($row, $file_id, $desc, $inputArray);

			$custom_fields_cnt = intval($s['custom_fields_cnt']) + 1;
			for ($n = 1; $n < $custom_fields_cnt; $n++) {
				$val = "";
				if (isset($s['file_upload_custom_field_' . $n]) && $cf_title = sanitize_text_field($s['file_upload_custom_field_' . $n])) {
					if (isset($c['_sf_file_upload_cf_' . $n]) && $c['_sf_file_upload_cf_' . $n]) {
						$val = sanitize_text_field($c['_sf_file_upload_cf_' . $n][0]);
					}
				}
				addCustomFieldField($row, $file_id, $n, $val, $inputArray);
			}

			// Tags
			$tags = get_the_terms($file_id, $tag_slug);
			$tagValue = "";
			if ($tags) {
				foreach ($tags as $tag) {
					$tagValue .= sanitize_text_field($tag->name) . ', ';
				}
			}
			if (strlen($tagValue) > 0) {
				$tagValue = substr($tagValue, 0, strlen($tagValue) - 2);
			}

			addTagsField($row, $file_id, $tagValue, $inputArray);

			// Categories
			if (is_array($allcategories)) {
				$categories = get_the_terms($file_id, 'shared-file-category');

				foreach ($allcategories as $category) {
					$catName = sanitize_title($category->name);
					$exists = false;
					if (is_array($categories)) {
						foreach ($categories as $myCategory) {
							$catName = sanitize_title($myCategory->name);
							if ($myCategory->name == $category->name) {
								$exists = true;
								break;
							}
						}
					}
					$catValue = "";
					if ($exists) {
						$catValue = "on";
					}

					addCategoryField($row, $file_id, $category, $catValue, $checkboxArray);
				}

			}

			$row .= "</tr>";

			// insert elements on correct index
			$array_index = array_search($file_id, $ids);
			$tableRows[$array_index] = $row;

		?>
		<?php endwhile;

		// add rows to output
		for ($i = 0; $i < count($tableRows); $i++) {
			$row = $tableRows[$i];
			$table .= $row;
		}

		$table .= "</table>";
		$table .= '<input type="submit" name="submit" disabled="true" value="SpeichernInput"></input>';
		$table .= '</form>';

		$pagination_active = 1;
		$maxpagesFloat = $total / $posts_per_page;
		$maxpages = intval($total / $posts_per_page);
		if ($maxpagesFloat > $maxpages)
			$maxpages++;
		error_log("editmeta maxpages " . print_r($maxpages, true));
		$paginationArgs = array(
			'post_type' => 'shared_file',
			'posts_per_page' => $posts_per_page,
			'paged' => $paged,
			// 's' => $search,
			'orderby' => 'name'
		);

		$_GET['_page'] = $paged;
		$paginationQuery = new WP_Query($args);
		$paginationQuery->max_num_pages = $maxpages;
		$pagination = SharedFilesPublicPagination::getPagination($pagination_active, $paginationQuery, 'default');

		$searchtest = "abc";
		if ($search):
			$pagination = preg_replace('/(href=\")(.+)(\")/', '${1}${2}&searchField=' . $searchtest . '${3}', $pagination);
		endif;
		// error_log("editmeta pagination " . print_r($pagination, true));

	else:
		$table .= "<p>Keine Daten gefunden</p>";
	endif;
} else {
	$table .= "<p>Keine Daten gefunden</p>";
}
?>


<?php
// error_log("searchFields: " . print_r($searchFields, true));
// error_log("table: " . print_r($table, true));
// error_log("pagination: " . print_r($pagination, true));
echo $searchFields . $table . $pagination;

wp_reset_postdata();
?>
<!-- Table section end -->

<?php
/* Wordpress doesn't give you access to the <body> tag to add a call
 * to init_trailmap(). This is a workaround to dynamically add that tag.
 */
function add_onload()
{
	?>

	<script type="text/javascript">
		document.getElementsByTagName('body')[0].onload = onloadInternally;
	</script>

	<?php
}

add_action('wp_footer', 'add_onload');
?>

<script>
	function onloadInternally() {
		let searchfield = document.getElementsByName("searchField");
		console.log("onloadInternally", searchfield, searchfield[0], insertSearchText)
		if (searchfield && searchfield[0] && insertSearchText) {
			console.log("onloadInternally", searchfield, searchfield[0], searchfield[0].value)
			searchfield[0].value = insertSearchText;
			if (insertSearchText.length > 0) {
				let caret = insertSearchText.length;
				searchfield[0].setSelectionRange(caret, caret);
			}
		}
	}

	var inputArrayJsOriginal = <?php echo json_encode($inputArray); ?>;
	var checkboxArrayJsOriginal = <?php echo json_encode($checkboxArray); ?>;

	var inputArrayJs = JSON.parse(JSON.stringify(inputArrayJsOriginal))
	var checkboxArrayJs = JSON.parse(JSON.stringify(checkboxArrayJsOriginal))

	var changedData;

	// console.log("InputJS" + JSON.stringify(inputArrayJs));
	// console.log("InputJS", inputArrayJs);

	function inputOnChange(name, data) {
		this.handleInputOnChange(name, data, window.inputArrayJs, window.inputArrayJsOriginal, "modified-input");
		this.computeAnyChangedData();
	}
	function checkboxOnChange(name, data) {
		this.handleInputOnChange(name, data, window.checkboxArrayJs, window.checkboxArrayJsOriginal, "modified-checkbox")
		var el = document.getElementById(name);
		if (data == true)
			el.value = "on";

		this.computeAnyChangedData();
	}

	function handleInputOnChange(name, data, arrayJs, arrayJsOriginal, modifiedClassName) {
		console.log("inputOnChange", name, data, arrayJs, arrayJsOriginal)

		console.log("handleInputOnChange document ", document)
		var el = document.getElementById(name);
		console.log("handleInputOnChange el " + name, el, el.value)
		// el.value = data;
		var el2 = document.getElementById(name);
		console.log("handleInputOnChange el " + name, el, el.value)


		if (arrayJs) {
			let foundElement = arrayJs.find(el => { if (el[name] !== undefined) return el; })
			// console.log("foundElement ", foundElement)
			arrayJs.find(el => {
				// console.log("IS TO FIND? ", name, el, el[name]);
				if (el[name]) return el;
			})

			if (foundElement) {
				foundElement[name].value = data;
				// console.log("foundElement modified ", foundElement)
				let foundElementOrig = arrayJsOriginal.find(el => { if (el[name] !== undefined) return el; })
				console.log("foundElement compare: ", foundElement, foundElementOrig)
				if (foundElementOrig && foundElementOrig[name].value != foundElement[name].value) {
					console.log("DIFFERENT");
					this.markInputObject(name, false, modifiedClassName)
				} else {
					console.log("EQUALS")
					this.markInputObject(name, true, modifiedClassName)
				}
			}
		}

		console.log("modifiedElement", arrayJs);
	}

	function computeAnyChangedData() {
		let changesExists = false;
		changedData = [];
		for (let i = 0; i < inputArrayJs.length; i++) {
			let element = inputArrayJs[i];
			let name = Object.keys(element)[0];
			let foundElementOrig = inputArrayJsOriginal.find(el => { if (el[name] !== undefined) return el; })
			if (foundElementOrig[name].value != element[name].value) {
				changesExists = true;
				console.log("CHANGE:", element);
				// break;
				changedData.push({
					file_id: element[name].file_id,
					name: name,
					originalValue: foundElementOrig[name].value,
					modifiedValue: element[name].value
				})
			}
		}

		for (let i = 0; i < window.checkboxArrayJs.length; i++) {
			let element = window.checkboxArrayJs[i];
			let name = Object.keys(element)[0];
			let foundElementOrig = window.checkboxArrayJsOriginal.find(el => { if (el[name] !== undefined) return el; })
			if (foundElementOrig[name].value != element[name].value) {
				changesExists = true;
				// break;
				changedData.push({
					name: name,
					file_id: element[name].file_id,
					originalValue: foundElementOrig[name].value,
					modifiedValue: element[name].value
				})
			}
		}

		let saveBtn = document.getElementsByName("submit");
		if (saveBtn.length > 0) {
			saveBtn[0].disabled = changesExists == false;
		}

		let searchField = document.getElementsByName("searchField");
		if (searchField.length > 0) {
			searchField[0].disabled = changesExists == true;
		}
		let categoryDropdowm = document.getElementsByName("sf_category");
		if (categoryDropdowm.length > 0) {
			categoryDropdowm[0].disabled = changesExists == true;
		}

		var pageNumbers = document.getElementsByClassName("page-numbers");
		if (pageNumbers) {
			for (let element of pageNumbers) {
				console.log(element);
				if (changesExists == true) {
					element.classList.add('disabled');
				}
				else {
					element.classList.remove('disabled');
				}
			}
		}


		console.log("MODIFIED DATA changesExists, changedData, page_numbers", changesExists, changedData, pageNumbers);
	}

	function markInputObject(name, equals, modifiedClassName) {
		let inputObject = document.getElementsByName(name);

		console.log('INPUT: equals, inputObject, style, class', equals, inputObject, inputObject[0].style, inputObject[0].class)
		if (inputObject && inputObject.length > 0) {
			if (equals)
				// inputObject[0].className = null;
				inputObject[0].classList.remove(modifiedClassName);
			else
				// inputObject[0].className = modifiedClassName;
				inputObject[0].classList.add(modifiedClassName);
		}
	}

	function getInputValue(fieldName) {
		var fields = document.getElementsByName(fieldName);
		if (fields && fields.length == 1) {
			return fields[0].value;
		}

		return null;
	}

	function onInputSearchText(newPaged) {
		let pagedToSend = 1;
		if (newPaged) {
			paged = newPaged;
			pagedToSend = newPaged;
			console.log("onInputSearchText new Page", newPaged)
		}

		var searchText = getInputValue("searchField");
		console.log("onInputSearchText PAGED VARIABLE??? searchText, paged, posts_per_page, pagedToSend, custom_fields_cnt ", searchText, paged, posts_per_page, pagedToSend, custom_fields_cnt);

		if (searchText != null && searchText != undefined) {
			var cfCount = getInputValue("custom_fields_cnt");
			if (!cfCount)
				cfCount = 20;

			// console.log("onInputSearchText  before sent ajax cfCount", cfCount);
			let data = {
				action: "sf_extensions_get_files",
				custom_fields_cnt: cfCount,
				searchField: searchText,
				paged: pagedToSend, // on SEARCH Change always page 1!!!
				// _page: pagedToSend, // on SEARCH Change always page 1!!!
				posts_per_page: posts_per_page

			};
			//  let ajaxurl = "http://localhost:8081/dsvpage/wp-admin/admin-ajax.php";
			let ajaxurl = "http://localhost:8081/dsvpage/test-sharedfiles-edit";

			// window.location.href = ajaxurl;
			document.getElementById("the-redirect-form").submit();

			// let ajaxurl = "http://localhost:8081/dsvpage/wp-admin/post_getFiles_wallner.php"
			// ajaxurl = esc_url_raw( admin_url('admin-ajax.php') )
			// jQuery.post(ajaxurl, data, (function (a) {
			// 	let result = JSON.parse(a);
			// 	replaceGridData(JSON.parse(a));
			// }))
		}
	}

	function onCategoryChange() {
		document.getElementById('the-redirect-form').submit();
	}

	function replaceGridData(data) {
		//TODO:
		// Remove table rows
		// insert new table rows
		// refresh paging info buttons

		var tableEl = document.getElementsByName("dataTable");
		console.log("replaceGridData data, tableEl, inputArrayJs", data, tableEl, inputArrayJs);
		// remove current child elements
		if (tableEl && tableEl.length > 0) {
			// $inputArray = array();
			// $checkboxArray = array();
			// inputArrayJs = [];

			let table = tableEl[0];
			clearTableContents(table);
			fillTableContents(table, data.data);
			replacePagination(data.pagination);
		}
	}

	function clearTableContents(table) {
		let children = table.children;
		if (children) {
			let length = children.length
			if (children.length == 1 && children[0] && children[0].children && children[0].children.length > 1) {
				length = children[0].children.length;
			}
			for (let i = length - 1; i > 1; i--) {
				table.deleteRow(i);
			}
		}
	}

	function fillTableContents(table, data) {
		console.log("fillTableContents data", data, table);
		// 		_sf_file_title_			_fid
		// _sf_file_description_	_fid
		// _sf_file_cf_			_fid_cfid
		// _sf_file_tags_			_fid
		// _sf_file_cat_			_fid_catid

		// file_id: 40
		// title: "advent ZZZ 122"	
		// description: "Beschreibung ZZZ 1 2d"
		// cf_1: "kompo ZZZ 1"
		// cf_2: "art ZZZ 1"
		// tags: "abcde, asdf, LLLLLL, ÖÖÖÖÖÖ, ZZZ"
		// cat_term_id_3: true
		// cat_term_id_4: false

		data.forEach(element => {
			// console.log("fillTableContents data row element, custom_fields_cnt", element, custom_fields_cnt);
			var tr = document.createElement('tr');
			var tdFileId = document.createElement('td');
			var fileId = document.createTextNode(element.file_id);
			tdFileId.appendChild(fileId);
			tr.appendChild(tdFileId);

			insertTitleField(element.file_id, element.title, tr, inputArrayJs);
			insertDescriptionField(element.file_id, element.description, tr, inputArrayJs);

			for (let i = 1; i < custom_fields_cnt; i++) {
				insertCustomField(element.file_id, i, element["cf_" + i], tr, inputArrayJs)
			}

			insertTagsield(element.file_id, element.tags, tr, inputArrayJs);

			for (let i = 0; i < allCategories.length; i++) {
				// console.log("Category", allCategories[i]);
				let category = allCategories[i];
				// console.log("  element", element, 
				insertCategoryField(element.file_id, category.term_id, element["cat_term_id_" + category.term_id], tr, inputArrayJs)
			}

			// 		$table.= getCheckboxField($file_id, $category -> name, '_sf_file_cat_'.$file_id. '_'.$category -> term_id, $catValue, $checkboxArrayJs);
			// $table.= getCheckboxField($file_id, $category -> name, '_sf_file_cat_origin_'.$file_id. '_'.$category -> term_id, $catValue, $checkboxArrayJs, true);


			table.appendChild(tr);
		});


		console.log("fillTableContents end ", table);

	}

	function replacePagination(pagination) {
		/*
		TODO CURRENTLY: Pagination funktioniert nicht
		remove current href and insert on other position --> fertig
		remove current span and insert on other position(switch positions) --> fertig
		
		Was funktioniert:
		* Darstellung der Buttons in der richtigen Reihenfole-- > erledigt
			* Wegsenden der Pagenummern ? --> erledigt

		Was nicht:
		* Browse files text ist weg-- > erledigt
		Daten, die geliefert werden sind immer Page 1 ???? --> erledigt
			* CurrentPage ist immer 1 warum ? --> erledigt
				*/

		var parents = document.getElementsByClassName("shared-files-pagination-improved");
		//  console.log("replacePagination parents", parents, parents[0]);
		if (parents && parents[0]) {
			let parent = parents[0];
			if (parent != null) {
				parent.innerHTML = '';
			}
			// console.log("replacePagination parent", parent);
			if (pagination) {
				var div = document.createElement('div');
				div.innerHTML = pagination.trim();
				// console.log("replacePagination intermediate div", div);
				let parents = div.getElementsByClassName("shared-files-pagination-improved");
				// console.log("replacePagination paginationParent", parents);
				if (parents && parents.length > 0) {
					var lastInserted = null;
					for (var i = parents[0].children.length - 1; i > 0; i--) {
						var child = parents[0].children[i];
						// console.log("replacePagination child to insert", child, child.toString(), child.text);
						if (child) {
							if (child.nodeName.toLowerCase() == "a") {
								child.setAttribute('href', 'javascript:void(0);')
								child.addEventListener("click", (ele) => {
									if (ele && ele.target && ele.target.text) {
										console.log("BEFORE CLICK ele", ele, ele.target, ele.target.text);
										onInputSearchText(ele.target.text);
									}
								})
							}
							// console.log("replacePagination child to insert 2", child, child.toString(), child.text, child.onclick);
							if (lastInserted)
								parent.insertBefore(child, lastInserted);
							else {
								parent.append(child);
							}
							lastInserted = child;
						}
					}

					// console.log("replacePagination paginationParent AFTER FOR", parents, parents[0]);

				}
			}
		}
	}

	function insertInputField(file_id, cf_id, name, value, container, inputArray, hidden = false) {
		// $obj = new stdClass();
		// $obj -> file_id = $file_id;
		// if ($cf_id != null)
		// 	$obj -> cf_id = $cf_id;
		// $obj -> value = $value;
		// array_push($inputArray, [$name => $obj]);
		// inputArray.push();


		var input = document.createElement("input");

		let type = "text";
		if (hidden) {
			type = "hidden";
		}

		if (value) {
			input.value = value;
			input.title = value;
		}

		if (!hidden && value) {
			input.onchange = "inputOnChange(this.name, this.value)"
		}

		input.type = type;
		input.name = name;
		input.id = name;
		container.appendChild(input); // put it into the DOM
	}

	function insertCheckboxField(file_id, cf_id, name, value, container, checkboxArrayJs, hidden = false) {
		// 			$obj = new stdClass();
		// $obj->file_id = $file_id;
		// $obj->cat_name = $cat_name;
		// $obj->value = $value;
		// array_push($checkboxArray, [$name => $obj]);

		// if ($hidden) {
		// 	if ($value)
		// 		return '<input type="hidden" name="'.$name. '" id="'.$name. '" checked value="'.$value. '" />';
		// else
		// 	return '<input type="hidden" name="'.$name. '" id="'.$name. '" value="'.$value. '" "/>';
		// } else {
		// 	if ($value)
		// 		return '<input type="checkbox" name="'.$name. '" id="'.$name. '" checked value="'.$value. '" onchange="checkboxOnChange(this.name, this.value)"/>';
		// else
		// 	return '<input type="checkbox" name="'.$name. '" id="'.$name. '" value="'.$value. '" onchange="checkboxOnChange(this.name, this.checked)"/>';
		// }

		var input = document.createElement("input");

		let type = "checkbox";
		if (hidden) {
			type = "hidden";
		}

		if (value) {
			input.value = value;
			input.checked = true;
		}

		if (!hidden && value) {
			input.onchange = "checkboxOnChange(this.name, this.value)"
		}

		input.type = type;
		input.name = name;
		input.id = name;

		//console.log("insertCheckboxField file_id, cf_id, name, value", file_id, cf_id, name, value);
		//console.log("insertCheckboxField INPUT", input);
		container.appendChild(input); // put it into the DOM

	}


	function insertTitleField(file_id, title, tr, inputArrayJs) {
		var td = document.createElement('td');
		insertInputField(file_id, null, '_sf_file_title_' + file_id, title, td, inputArrayJs, false);
		insertInputField(file_id, null, '_sf_file_title_origin_' + file_id, title, td, inputArrayJs, true);

		tr.appendChild(td);
	}

	function insertDescriptionField(file_id, description, tr, inputArrayJs) {
		var td = document.createElement('td');
		insertInputField(file_id, null, '_sf_file_description_' + file_id, description, td, inputArrayJs, false);
		insertInputField(file_id, null, '_sf_file_description_origin_' + file_id, description, td, inputArrayJs, true);

		tr.appendChild(td);
	}

	function insertCustomField(file_id, n, value, tr, inputArrayJs) {
		var td = document.createElement('td');
		insertInputField(file_id, n, '_sf_file_cf_' + file_id + '_' + n, value, td, inputArrayJs, false);
		insertInputField(file_id, n, '_sf_file_cf__origin_' + file_id + '_' + n, value, td, inputArrayJs, true);

		tr.appendChild(td);
	}

	function insertTagsield(file_id, tags, tr, inputArrayJs) {
		var td = document.createElement('td');
		insertInputField(file_id, null, '_sf_file_tags_' + file_id, tags, td, inputArrayJs, false);
		insertInputField(file_id, null, '_sf_file_tags__origin_' + file_id, tags, td, inputArrayJs, true);

		tr.appendChild(td);
	}

	function insertCategoryField(file_id, term_id, value, tr, checkboxArrayJs) {
		var td = document.createElement('td');
		insertCheckboxField(file_id, term_id, '_sf_file_cat_' + file_id + '_' + term_id, value, td, checkboxArrayJs, false);
		insertCheckboxField(file_id, term_id, '_sf_file_cat_origin_' + file_id + '_' + term_id, value, td, checkboxArrayJs, true);

		tr.appendChild(td);
	}


</script>

<style>
	.modified-input {
		outline: 3px solid red;
	}

	.modified-checkbox {
		outline: 3px solid red;
	}

	input[type=checkbox] .modified-checkbox {
		accent-color: yellow;
	}

	a.disabled {
		pointer-events: none;
		cursor: default;
	}

	input:disabled {
		background-color: #938a8d;
	}
</style>


<?php get_footer(); ?>