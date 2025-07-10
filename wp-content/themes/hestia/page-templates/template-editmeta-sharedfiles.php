<?php
/**
 * Template Name: Template Shared Files Edit
 *
 * The template for the full-width page.
 *
 * @package Hestia
 * @since Hestia 1.0
 */

// start attention: This code has before get_header section!!!
include "C:\\xampp\\htdocs\\dsvpage\\wp-admin\\post_wallner_exceldownload.php";
include "C:\\xampp\\htdocs\\dsvpage\\wp-admin\\post_wallner_query_data.php";
include "C:\\xampp\\htdocs\\dsvpage\\wp-admin\\post_wallner_helper_functions.php";
include "C:\\xampp\\htdocs\\dsvpage\\wp-admin\\post_wallner_zipfilecreation.php";

$posts_per_page = 3;
$paged = 1;
$s = get_option('shared_files_settings');

$tag_slug = 'post_tag';
if (isset($s['tag_slug']) && $s['tag_slug']) {
	$tag_slug = sanitize_title($s['tag_slug']);
}


// error_log("TEMPLATE START _POST " . print_r($_POST, return: true));
// error_log("TEMPLATE START _GET " . print_r($_GET, true));

if (isset($_GET['_page']) && $_GET['_page']) {
	$paged = (int) $_GET['_page'];
} elseif (get_query_var('paged')) {
	$paged = absint(get_query_var('paged'));
}

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

if (isset($_POST['elementsPerPage'])) {
	$posts_per_page = $_POST["elementsPerPage"];
} else if (isset($_GET['elementsPerPage'])) {
	$posts_per_page = $_GET["elementsPerPage"];
}

$nurKategorienAnzeigen = false;
if (isset($_POST['nurKategorienAnzeigen']) && $_POST['nurKategorienAnzeigen'] == "on") {
	$nurKategorienAnzeigen = true;
} else if (isset($_GET['nurKategorienAnzeigen']) && $_GET['nurKategorienAnzeigen'] == "on") {
	$nurKategorienAnzeigen = true;
}

$excel_export = false;

if (isset($_POST['doExcelExport']) && $_POST["doExcelExport"] == "true") {
	$excel_export = $_POST["doExcelExport"];
}

if ($excel_export == true) {
	$fileName = "notenarchiv_export.csv";
	if (isset($_POST['excelImportFilename'])) {
		$fileName = $_POST["excelImportFilename"] . ".csv";
	}

	$parameters = array(
		"posts_per_page" => $posts_per_page,
		"paged" => 0,
		"wpdb" => $wpdb,
		"search" => $search,
		"category" => $category,
		"custom_fields_cnt" => $custom_fields_cnt,
		"allcategories" => $allcategories,
		"tag_slug" => $tag_slug,
		"settings" => $s,
		"nurKategorienAnzeigen" => $nurKategorienAnzeigen
	);

	$data = queryData($parameters);
	downloadExcelData($data, $fileName);
	return;
}

$zip_file_creation = false;

if (isset($_POST['createzipFile']) && $_POST["createzipFile"] == "true") {
	$zip_file_creation = $_POST["createzipFile"];
}

if ($zip_file_creation) {
	$fileName = "notenarchiv.zip";
	if (isset($_POST['zipfilename'])) {
		$fileName = $_POST["zipfilename"] . ".zip";
	}
	//TODO: Dynamic path
	$path = "C:\\xampp\\htdocs\\dsvpage\\wp-content\\uploads\\shared-files";

	$parameters = array(
		"posts_per_page" => $posts_per_page,
		"paged" => 0,
		"wpdb" => $wpdb,
		"search" => $search,
		"category" => $category,
		"custom_fields_cnt" => $custom_fields_cnt,
		"allcategories" => $allcategories,
		"tag_slug" => $tag_slug,
		"settings" => $s,
		"nurKategorienAnzeigen" => $nurKategorienAnzeigen
	);

	$data = queryData($parameters);
	$path = str_replace("\\", DIRECTORY_SEPARATOR, $path);

	// Buffer löschen – keine Ausgabe darf ins ZIP!
	ob_end_clean(); 

	createzipfile($data, $path, $fileName);
	return;
}

// end attention: This code has before get_header section!!!

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

<!-- pass variables to javascript -->
<script>
	var paged = <?php echo json_encode($paged) ?>;
	var posts_per_page = <?php echo json_encode($posts_per_page) ?>;
	var custom_fields_cnt = <?php echo json_encode($custom_fields_cnt) ?>;
	var allCategories = <?php echo json_encode($allcategories) ?>;
	var insertSearchText = <?php echo json_encode($search) ?>;
</script>


<?php
// ------------------------------------------------------------------------------------------------------------------------
// Form Suche start
$searchFields = '<form
   id="the-redirect-form" 
   method="post" 
   action="http://localhost:8081/dsvpage/test-sharedfiles-edit" 
   enctype="multipart/form-data">';

// Tabellenspalten
$searchFields .= '<div>';
$searchFields .= '<table style="width: 100%">
    <colgroup>
       <col span="1" style="width: 30%;" />
       <col span="1" style="width: 15%;" />
       <col span="1" style="width: 15%;" />
	   <col span="1" style="width: 15%;" />
       <col span="1" style="width: 10%;" />
       <col span="1" style="width: 15%;" />
    </colgroup><tr><td>';

// Suchfeld
if ($search) {
	$searchFields .= '<input type="text" shared-files-search-files-v2" name="searchField" autofocus placeholder="' . esc_html__('Search files...', 'shared-files') . '" value="' . $search . '" oninput="onInputSearchText()"  />';
} else {
	$searchFields .= '<input type="text" shared-files-search-files-v2" name="searchField" autofocus placeholder="' . esc_html__('Search files...', 'shared-files') . '" oninput="onInputSearchText()" />';
}

$searchFields .= '</td><td>';

// Kategorienauswahl
$categories_order = 'ASC';
$argsCategoryCombo = array(
	'taxonomy' => 'shared-file-category',
	'name' => 'sf_category',
	'show_option_all' => esc_attr__('Choose category', 'shared-files'),
	'hierarchical' => true,
	'class' => 'shared-files-category-select select_v2',
	'echo' => false,
	'value_field' => 'slug',
	'selected' => $category,
	'order' => $categories_order,
	'hide_if_empty' => true

);

$categoryDropdowm = wp_dropdown_categories($argsCategoryCombo);
$endIndex = strpos($categoryDropdowm, ">");
$categoryDropdowm = str_replace("class='shared-files-category-select select_v2'>", 'class="shared-files-category-select select_v2" onchange="onCategoryChange()">', $categoryDropdowm);

// Nur Kategorien bearbeiten
$searchFields .= '<div class="shared-files-category-select-container">';
$searchFields .= $categoryDropdowm;
$searchFields .= '</div></td>';
if ($nurKategorienAnzeigen == true) {
	$searchFields .= '<td><input type="checkbox" name="nurKategorienAnzeigen" id="nurKategorienAnzeigen" checked value="on" style="margin-right: 10px;" onclick="onNurKategorienAnzeigenClick()"/>Nur Kategorien bearbeiten</td>';
} else {
	$searchFields .= '<td><input type="checkbox" name="nurKategorienAnzeigen" id="nurKategorienAnzeigen" style="margin-right: 10px;" onclick="onNurKategorienAnzeigenClick()"/>Nur Kategorien bearbeiten</td>';
}

// Zeilen pro Seite
$searchFields .= '<td style="white-space: nowrap;"><div style="display: flex; align-items: center; gap: 6px; padding-top: 25px;">';
$searchFields .= '<label style="margin-right: 10px;">Zeilen pro Seite:</label><input type="text" name="elementsPerPage" id="elementsPerPage" style="width: 40px;" value="' . $posts_per_page . '" onblur="elementsPerPageChange(this.name, this.value)"/>';
$searchFields .= '</div></td>';

// Download ZIP
$searchFields .= '<td>';
$searchFields .= '<div class="justify-right"><button name="downloadZipBtn" style="float:right" onclick="onZipFileCreationClick()">Download ZIP</button>';
$searchFields .= '<input type="hidden" name="excelImportFilename" id="excelImportFilename"/>';
$searchFields .= '<input type="hidden" name="doExcelExport" id="doExcelExport"/>';
$searchFields .= '</td>';

// Download Excel Liste
$searchFields .= '<td>';
$searchFields .= '<div class="justify-right"><button name="excepExportBtn" style="float:right" onclick="onExcelExportclick()">Excel export</button>';
$searchFields .= '<input type="hidden" name="zipfilename" id="zipfilename"/>';
$searchFields .= '<input type="hidden" name="createzipFile" id="createzipFile"/>';
$searchFields .= '</td>';
$searchFields .= '</div></td></tr></table></div>';
$searchFields .= '</form>';

// ------------------------------------------------------------------------------------------------------------------------
// Form mit Daten
$table = '<form method="post" name="dataForm" id="dataForm" enctype="application/x-www-form-urlencoded" action="http://localhost:8081/dsvpage/wp-admin/post_wallner.php">';
$table .= '<div class="table-scroll-wrapper"><table name="dataTable" style="margin: 10px;">';

$pagination = "";

$parameters = array(
	"posts_per_page" => $posts_per_page,
	"paged" => $paged,
	"wpdb" => $wpdb,
	"search" => $search,
	"category" => $category,
	"custom_fields_cnt" => $custom_fields_cnt,
	"allcategories" => $allcategories,
	"tag_slug" => $tag_slug,
	"settings" => $s,
	"nurKategorienAnzeigen" => $nurKategorienAnzeigen
);

// Datenabfrage
$data = queryData($parameters);

$firstIndexData = $data[0];
if ($data["headrow"] && $data["headrowKat"] && $data["keys"] && is_array($firstIndexData)) {
	$headRow = $data["headrow"];
	if ($nurKategorienAnzeigen == true) {
		$headRow = $data["headrowKat"];
	}

	// Kopfzeile
	$table .= "<tr>";
	foreach ($headRow as $element) {
		$table .= "<td>" . $element . "</td>";
	}
	$table .= "</tr>";

	$outerArrayKeys = array_keys(($data));
	$dataArrayKeys = $data["keys"];

	// Datenzeilen
	$rowIndex = 0;
	foreach ($outerArrayKeys as $outerKey) {
		if ($outerKey != "keys" && $outerKey != "headrow" && $outerKey != "headrowKat" && $outerKey != "args" && $outerKey != "total") {
			$dataRowArray = $data[$outerKey];
			$row = "";
			$file_id = null;
			foreach ($dataArrayKeys as $dataKey) {
				$element = null;
				if (is_array($dataKey)) {
					$firstKey = array_key_first($dataKey);
					$secondKey = $dataKey[$firstKey];
					$element = $dataRowArray[$firstKey][$secondKey];

					if ($firstKey == "custom_field" && $nurKategorienAnzeigen == false) {
						addCustomFieldField($row, $file_id, $secondKey, $element, $inputArray);
					} else if ($firstKey == "category") {
						$catValue = "";
						if ($element == "Ja") {
							$catValue = "on";
						}
						$categoryTerm = arrayFindObjectElement($allcategories, "term_id", $secondKey);

						addCategoryField($row, $file_id, $categoryTerm, $catValue, $checkboxArray);
					}
				} else {
					$element = $dataRowArray[$dataKey];

					if ($dataKey == "file_id") {

						$file_id = $element;
						$row = "";
						$row .= "<tr><td>" . $file_id . "</td>";
					} else if ($dataKey == "title") {
						addTitleField($row, $file_id, $element, $inputArray);
					} else if ($dataKey == "description" && $nurKategorienAnzeigen == false) {
						addDescriptionField($row, $file_id, $element, $inputArray);
					} else if ($dataKey == "tags" && $nurKategorienAnzeigen == false) {
						addTagsField($row, $file_id, $element, $inputArray);
					}
				}
			}

			$row .= "</tr>";

			$tableRows[$outerKey] = $row;
		}
	}

	// add rows to output
	for ($i = 0; $i < count($tableRows); $i++) {
		$row = $tableRows[$i];
		$table .= $row;
	}

	// Submit Button
	$table .= "</table></div>";
	$table .= '<input type="submit" name="submit" disabled="true" value="Speichern"></input>';
	$table .= '</form>';


	// Pagination buttons
	$pagination_active = 1;
	$maxpagesFloat = $data["total"] / $posts_per_page;
	$maxpages = intval($data["total"] / $posts_per_page);
	if ($maxpagesFloat > $maxpages)
		$maxpages++;

	$paginationArgs = array(
		'post_type' => 'shared_file',
		'posts_per_page' => $posts_per_page,
		'paged' => $paged,
		'orderby' => 'name'
	);

	$_GET['_page'] = $paged;
	$paginationQuery = new WP_Query($data["args"]);
	$paginationQuery->max_num_pages = $maxpages;
	$pagination = SharedFilesPublicPagination::getPagination($pagination_active, $paginationQuery, 'default');

	$pargs = "";
	if ($search) {
		$pargs .= '&searchField=' . $search;
	}
	if ($category) {
		$pargs .= '&sf_category=' . $category;
	}
	if ($nurKategorienAnzeigen) {
		$pargs .= '&nurKategorienAnzeigen=on';
	}
	if ($posts_per_page) {
		$pargs .= '&elementsPerPage=' . $posts_per_page;
	}

	if ($pargs):
		$pagination = preg_replace('/(href=\")(.+)(\")/', '${1}${2}' . $pargs . '${3}', $pagination);
	endif;

} else {
	$table .= "<p>Keine Daten gefunden</p>";
}

// Ausgabe der gesamten Seite
echo $searchFields . $table . $pagination;

wp_reset_postdata();
?>


<?php
/* Wordpress doesn't give you access to the <body> tag to add a call
 * to init_trailmap(). This is a workaround to dynamically add that tag.
 */
function add_onload()
{
	?>

	<script type="text/javascript">
		document.getElementsByTagName('body')[0].onload = onloadInternally;

		document.getElementById("the-redirect-form").addEventListener("submit", function (e) {
			setTimeout(() => {
				document.getElementById("doExcelExport").value = false;
				document.getElementById("createzipFile").value = false;
			}, 100); // kurz verzögern
		});
	</script>

	<?php
}

add_action('wp_footer', 'add_onload');
?>

<script>
	/**
	 * Set Caret position in search field
	 */
	function onloadInternally() {
		let searchfield = document.getElementsByName("searchField");
		if (searchfield && searchfield[0] && insertSearchText) {
			searchfield[0].value = insertSearchText;
			if (insertSearchText.length > 0) {
				let caret = insertSearchText.length;
				searchfield[0].setSelectionRange(caret, caret);
			}
		}
	}

	document.getElementById("dataForm").addEventListener("submit", function(e) {
		// console.log("SUBMIT e", e);
		// e.preventDefault();

		const form = e.target;
		
		appendHiddenInput("searchField", form);
		appendHiddenInput("sf_category", form);
		appendHiddenInput("elementsPerPage", form);
		appendHiddenInput("nurKategorienAnzeigen", form);
		console.log("SUBMIT form at the end", form);
		});

	function appendHiddenInput(name, form){
		const hiddenInput = document.createElement("input");
		hiddenInput.type = "hidden";
		hiddenInput.name = "referer_parm_" + name;
		hiddenInput.value = getInputValue(name);;
		form.appendChild(hiddenInput);
	}

	var inputArrayJsOriginal = <?php echo json_encode($inputArray); ?>;
	var checkboxArrayJsOriginal = <?php echo json_encode($checkboxArray); ?>;

	var inputArrayJs = JSON.parse(JSON.stringify(inputArrayJsOriginal))
	var checkboxArrayJs = JSON.parse(JSON.stringify(checkboxArrayJsOriginal))

	var changedData;


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
		var el = document.getElementById(name);

		if (arrayJs) {
			let foundElement = arrayJs.find(el => { if (el[name] !== undefined) return el; })
			arrayJs.find(el => {
				if (el[name]) return el;
			})

			if (foundElement) {
				foundElement[name].value = data;
				let foundElementOrig = arrayJsOriginal.find(el => { if (el[name] !== undefined) return el; })
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
			categoryDropdowm[0]["aria-disabled"] = changesExists == true;
		}
		let nurKategorienAnzeigen = document.getElementsByName("nurKategorienAnzeigen");
		if (nurKategorienAnzeigen.length > 0) {
			nurKategorienAnzeigen[0].disabled = changesExists == true;
		}
		let elementsPerPage = document.getElementsByName("elementsPerPage");
		if (elementsPerPage.length > 0) {
			elementsPerPage[0].disabled = changesExists == true;
		}

		let downloadZipBtn = document.getElementsByName("downloadZipBtn");
		if (downloadZipBtn.length > 0) {
			downloadZipBtn[0].disabled = changesExists == true;
		}

		let excepExportBtn = document.getElementsByName("excepExportBtn");
		if (excepExportBtn.length > 0) {
			excepExportBtn[0].disabled = changesExists == true;
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

		if (inputObject && inputObject.length > 0) {
			if (equals)
				inputObject[0].classList.remove(modifiedClassName);
			else
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

			document.getElementById("the-redirect-form").submit();
		}
	}

	function elementsPerPageChange() {
		var form = document.getElementById('the-redirect-form');
		form.submit();
	}
	
	function onExcelExportclick() {
		let fileName = prompt("Bitte geben Sie den gewünschten Dateinamen ein", "");
		if (fileName != null) {
			document.getElementById("excelImportFilename").value = fileName;
			document.getElementById("doExcelExport").value = true;
			var form = document.getElementById('the-redirect-form');
			form.submit();
		}
	}

	function onZipFileCreationClick() {
		let fileName = prompt("Bitte geben Sie den gewünschten Dateinamen ein", "");
		if (fileName != null) {
			document.getElementById("zipfilename").value = fileName;
			document.getElementById("createzipFile").value = true;
			var form = document.getElementById('the-redirect-form');
			form.submit();
		}
	}

	function onCategoryChange() {
		document.getElementById('the-redirect-form').submit();
	}

	function onNurKategorienAnzeigenClick() {
		document.getElementById('the-redirect-form').submit();
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

	*,
	*::before,
	*::after {
		box-sizing: border-box;
	}

	td div.justify-right {
		/* there is a shorthand, TODO! 🙂 */
		margin: auto 0 auto auto;
	}

	/* Wrapper für die Tabelle */
	.table-scroll-wrapper {
		display: block;
		overflow-x: auto;
		overflow-y: hidden;
		max-width: 100%;
		white-space: nowrap;
		scrollbar-width: auto;
		/* Firefox */
		-ms-overflow-style: auto;
		/* IE 10+ */
	}

	/* Tabelle optional stylen */
	.table-scroll-wrapper table {
		border-collapse: collapse;
		width: 100%;
		/* Mindestbreite für Scroll-Effekt */
	}

	.table-scroll-wrapper table th,
	.table-scroll-wrapper table td {
		padding: 8px 12px;
		border: 1px solid #ddd;
		text-align: left;
	}

	.table-scroll-wrapper input[type="text"] {
		max-width: 100%;
		min-width: 150px;
		box-sizing: border-box;
	}

	button:disabled {
		cursor: not-allowed;
		/* oder z.B. 'default', 'help', 'wait', etc. */
		opacity: 0.6;
		/* optional zur besseren Erkennbarkeit */
	}

	input[type="submit"]:disabled {
		cursor: not-allowed;
		/* oder z.B. 'default', 'help', 'wait', etc. */
		opacity: 0.6;
		/* optional zur besseren Erkennbarkeit */
	}

	select[aria-disabled="true"] {
		pointer-events: none;
		/* verhindert Interaktion */
		opacity: 0.5;
		background-color: #eee;
		cursor: not-allowed;
		/* beliebiger Cursor funktioniert nur in manchen browsern */
	}
</style>


<?php get_footer(); ?>