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
	error_log("addCategoryField " . $file_id . ": " . print_r($category, true) . ", catvalue " . $catValue . ", inputArr " . print_r($inputArray, true));
	$table .= '<td>';
	$table .= getCheckboxField($file_id, $category->name, '_sf_file_cat_' . $file_id . '_' . $category->term_id, $catValue, $checkboxArray);
	$table .= getCheckboxField($file_id, $category->name, '_sf_file_cat_origin_' . $file_id . '_' . $category->term_id, $catValue, $checkboxArray, true);
	$table .= '</td>';
}
?>

<?php
$posts_per_page = 4;
$paged = 1;

if (isset($_GET['_page']) && $_GET['_page']) {
	$paged = (int) $_GET['_page'];
} elseif (get_query_var('paged')) {
	$paged = absint(get_query_var('paged'));
}

$s = get_option('shared_files_settings');
$custom_fields_cnt = intval($s['custom_fields_cnt']) + 1;

?>

<!-- pass variables to javascript -->
<script>
	var paged = <?php echo json_encode($paged) ?>;
	var posts_per_page = <?php echo json_encode($posts_per_page) ?>;
	var custom_fields_cnt = <?php echo json_encode($custom_fields_cnt) ?>;
</script>


<?php

$searchFields = '<div>';
$searchFields .= '<input type="text" shared-files-search-files-v2" name="searchField" placeholder="' . esc_html__('Search files...', 'shared-files') . '" oninput="onInputSearchText()" >';
$searchFields .= '</div>';

$table = '<form method="post" name="myForm" enctype="application/x-www-form-urlencoded" action="http://localhost:8081/dsvpage/wp-admin/post_wallner.php">';
$table .= '<table name="dataTable" style="margin: 10px;">';
$table .= "<tr><td>Id</td><td>Titel</td><td>Beschreibung</td>";

$args = array(
	'post_type' => 'shared_file',
	'posts_per_page' => $posts_per_page,
	'paged' => $paged,
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
$taxonomy_slug = 'shared-file-category';
$allcategories = get_terms($taxonomy_slug, array('hide_empty' => 0));

foreach ($allcategories as $category) {
	$table .= '<td>' . sanitize_title($category->slug) . '</td>';
}
$table .= "</tr>";

$the_query_terms = new WP_Query($args);

$tag_slug = 'post_tag';
if (isset($s['tag_slug']) && $s['tag_slug']) {
	$tag_slug = sanitize_title($s['tag_slug']);
}

if ($the_query_terms->have_posts()):
	while ($the_query_terms->have_posts()):
		$the_query_terms->the_post();
		$file_id = intval(get_the_id());
		$table .= "<tr><td>" . $file_id . "</td>";
		$title = get_the_title();
		addTitleField($table, $file_id, $title, $inputArray);


		// Custom fields
		$c = get_post_custom($file_id);
		$desc = $c["_sf_description"][0];
		if ($desc !== null && trim($desc) !== '') {
			$desc = str_replace('<p>', '', $desc);
			$desc = str_replace('</p>', '', $desc);
		}

		addDescriptionField($table, $file_id, $desc, $inputArray);

		$custom_fields_cnt = intval($s['custom_fields_cnt']) + 1;
		for ($n = 1; $n < $custom_fields_cnt; $n++) {
			$val = "";
			if (isset($s['file_upload_custom_field_' . $n]) && $cf_title = sanitize_text_field($s['file_upload_custom_field_' . $n])) {
				if (isset($c['_sf_file_upload_cf_' . $n]) && $c['_sf_file_upload_cf_' . $n]) {
					$val = sanitize_text_field($c['_sf_file_upload_cf_' . $n][0]);
				}
			}
			addCustomFieldField($table, $file_id, $n, $val, $inputArray);
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

		addTagsField($table, $file_id, $tagValue, $inputArray);

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

				addCategoryField($table, $file_id, $category, $catValue, $checkboxArray);
			}

		}

		$table .= "</tr>";

	?>
	<?php endwhile;

	$table .= "</table>";
	$table .= '<input type="submit" name="submit" disabled="true" value="SpeichernInput"></input>';
	$table .= '</form>';

	$pagination_active = 1;
	$pagination = SharedFilesPublicPagination::getPagination($pagination_active, $the_query_terms, 'default');


	// error_log("PAGE: " . print_r($pagination, true));
	echo $searchFields . $table . $pagination;

	wp_reset_postdata();
?>
<?php endif; ?>
<!-- Table section end -->

<script>
	var inputArrayJsOriginal = <?php echo json_encode($inputArray); ?>;
	var checkboxArrayJsOriginal = <?php echo json_encode($checkboxArray); ?>;

	var inputArrayJs = JSON.parse(JSON.stringify(inputArrayJsOriginal))
	var checkboxArrayJs = JSON.parse(JSON.stringify(checkboxArrayJsOriginal))

	var changedData;

	console.log("InputJS" + JSON.stringify(inputArrayJs));
	console.log("InputJS", inputArrayJs);

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

	function onInputSearchText() {
		console.log("onInputSearchText PAGED VARIABLE??? ", paged, posts_per_page, custom_fields_cnt);
		var searchText = getInputValue("searchField");
		if (searchText) {
			var cfCount = getInputValue("custom_fields_cnt");
			if (!cfCount)
				cfCount = 20;

			let data = {
				action: "sf_extensions_get_files",
				custom_fields_cnt: cfCount,
				searchField: searchText,
				paged: 1, // on SEARCH Change always page 1!!!
				posts_per_page: posts_per_page

			};
			let ajaxurl = "http://localhost:8081/dsvpage/wp-admin/admin-ajax.php";
			// let ajaxurl = "http://localhost:8081/dsvpage/wp-admin/post_getFiles_wallner.php"
			// ajaxurl = esc_url_raw( admin_url('admin-ajax.php') )
			jQuery.post(ajaxurl, data, (function (a) {
				let result = JSON.parse(a);
				replaceGridData(JSON.parse(a));
			}))
		}
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
			inputArrayJs = [];

			let table = tableEl[0];
			clearTableContents(table);
			fillTableContents(table, data);
		}
	}

	function clearTableContents(table) {
		let children = table.children;
		if (children && children.length == 1) {
			if (children[0] && children[0].children && children[0].children.length > 1) {
				let length = children[0].children.length
				for (let i = length - 1; i > 0; i--) {
					table.deleteRow(i);
				}
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
			console.log("fillTableContents data row", element);
			var tr = document.createElement('tr');
			var tdFileId = document.createElement('td');
			var fileId = document.createTextNode(element.file_id);
			tdFileId.appendChild(fileId);
			tr.appendChild(tdFileId);

			insertTitleField(element.file_id, element.title, tr, inputArrayJs);

			table.appendChild(tr);
		});


		console.log("fillTableContents end ", table);

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

	function insertTitleField(file_id, title, tr, inputArrayJs) {
		var td = document.createElement('td');
		insertInputField(file_id, null, '_sf_file_title_' + file_id, title, td, inputArrayJs, false);
		insertInputField(file_id, null, '_sf_file_title_origin_' + file_id, title, td, inputArrayJs, true);

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