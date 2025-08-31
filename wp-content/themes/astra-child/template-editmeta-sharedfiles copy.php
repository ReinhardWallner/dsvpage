<?php
/**
 * Template Name: CPT Update
 *
 * The template for the full-width page.
 *
 * @package Astra
 * @since Astra 1.0
 */

 
if(!is_user_logged_in()){
	$pagename = get_query_var('pagename');
	error_log("Nicht berechtigt! " . print_r($pagename, true));
	// TODO REDIRECT
	return;
}

if ( ! function_exists( 'get_home_path' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/file.php' );
}

$user = wp_get_current_user();
$isReadonlyUser = false;
if ( !in_array( 'administrator', (array) $user->roles ) && !in_array( 'editor', (array) $user->roles ) && !in_array( 'author', (array) $user->roles ) )
{	
	$isReadonlyUser = true;
}

 $homepath = get_home_path();
 $homepath = str_replace("/", DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, $homepath);
 $sharedfilefolder = $homepath . "wp-admin" . DIRECTORY_SEPARATOR  . "shared_files_extensions" . DIRECTORY_SEPARATOR ;


include  $sharedfilefolder . "sharedfiles_exceldownload.php";
include  $sharedfilefolder . "sharedfiles_querydata.php";
include  $sharedfilefolder . "sharedfiles_helperfunctions.php";
include  $sharedfilefolder . "sharedfiles_zipfiledownload.php";
include  $sharedfilefolder . "sharedfiles_controlhelpers.php";

 // start attention: This code has before get_header section!!!

$posts_per_page = 20;
$paged = 1;
$s = get_option('shared_files_settings');

$tag_slug = 'post_tag';
if (isset($s['tag_slug']) && $s['tag_slug']) {
	$tag_slug = sanitize_title($s['tag_slug']);
}


error_log("TEMPLATE START _POST " . print_r($_POST, return: true));
error_log("TEMPLATE START _GET " . print_r($_GET, true));

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

if (isset($_POST['elementsPerPage']) && $_POST['elementsPerPage'] != null) 
{
 	if(is_int($_POST['elementsPerPage']))
 		$posts_per_page = $_POST["elementsPerPage"];
	if(ctype_digit($_POST["elementsPerPage"]))
 		$posts_per_page = intval($_POST["elementsPerPage"]);
 } else if (isset($_GET['elementsPerPage']) && $_GET['elementsPerPage'] != null) {
 	if(is_int($_GET['elementsPerPage']))
 		$posts_per_page = $_GET["elementsPerPage"];
 	if(ctype_digit($_GET["elementsPerPage"]))
 		$posts_per_page = intval($_GET["elementsPerPage"]);
 }

$nurKategorienAnzeigen = false;
if (isset($_POST['nurKategorienAnzeigen']) && $_POST['nurKategorienAnzeigen'] == "on") {
	$nurKategorienAnzeigen = true;
} else if (isset($_GET['nurKategorienAnzeigen']) && $_GET['nurKategorienAnzeigen'] == "on") {
	$nurKategorienAnzeigen = true;
}

$onlyModifySingleField = null;
if (isset($_POST['onlyModifySingleField']) != null) {
	$onlyModifySingleField = $_POST['onlyModifySingleField'];
} else if (isset($_GET['onlyModifySingleField']) != null ) {
	$onlyModifySingleField = $_POST['onlyModifySingleField'];
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
		"nurKategorienAnzeigen" => $nurKategorienAnzeigen,
		"onlyModifySingleField"  => $onlyModifySingleField
	);

	$data = queryData($parameters);
	downloadExcelData($data, $fileName, $nurKategorienAnzeigen, $onlyModifySingleField);
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

	$uldir = wp_upload_dir();
	$path = str_replace("/", DIRECTORY_SEPARATOR, $uldir["basedir"]);
	$path = str_replace("\\", DIRECTORY_SEPARATOR, $path) . DIRECTORY_SEPARATOR . "shared-files";

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

// /**
//  * Don't display page header if header layout is set as classic blog.
//  */
// do_action('hestia_before_single_page_wrapper');
// hestia_no_content_get_header();

// do_action( 'hestia_page_builder_blank_before_content' );

?>

<?php

$inputArray = array();
$checkboxArray = array();

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
$home = home_url( $wp->request );

// ------------------------------------------------------------------------------------------------------------------------
// Form Suche start
$searchFields = '<form
   id="the-redirect-form" 
   method="post" 
   action="' . $home . '" 
   enctype="multipart/form-data">';

$searchFields .= '<div class="search-row-top">
  <div class="search-field">';

// Suchfeld
if ($search) {
	$searchFields .= '<input type="text" shared-files-search-files-v2 class="search-input" name="searchField" autofocus placeholder="' . esc_html__('Search files...', 'shared-files') . '" value="' . $search . '" oninput="onInputSearchText()"  />';
} else {
	$searchFields .= '<input type="text" shared-files-search-files-v2 class="search-input" name="searchField" autofocus placeholder="' . esc_html__('Search files...', 'shared-files') . '" oninput="onInputSearchText()" />';
}
$searchFields .= '</div>';

// error_log("CATEGORY IN UPD " . print_r($category, true));
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

$searchFields .= '<div class="category-select">';
$searchFields .= $categoryDropdowm;
$searchFields .= '</div>';
$searchFields .= '</div>';

$searchFields .= '<div class="search-row-bottom">';
$searchFields .= '<div class="option-checkbox">';

// Nur Kategorien bearbeiten
$nurKategorienText = esc_html__('Modify categories only', 'astra-child');

if ($isReadonlyUser == true){
	$nurKategorienText = esc_html__('Show only categories', 'astra-child');
}

if ($nurKategorienAnzeigen == true) {
	$searchFields .= '<label for="nurKategorienAnzeigen"><input type="checkbox" name="nurKategorienAnzeigen" id="nurKategorienAnzeigen" checked value="on" onclick="onNurKategorienAnzeigenClick()"/><span style="word-break: break-word;">' . $nurKategorienText . '</span></label>';
} else {
	$searchFields .= '<label for="nurKategorienAnzeigen"><input type="checkbox" name="nurKategorienAnzeigen" id="nurKategorienAnzeigen" onclick="onNurKategorienAnzeigenClick()"/><span style="word-break: break-word;">' . $nurKategorienText . '</span></label>';
}

$searchFields .= '</div>';

$searchFields .= '<div class="option-select">';
$onlyModifySingleFieldText = esc_html__('Modify single field:', 'astra-child');
if ($isReadonlyUser){
	$onlyModifySingleFieldText = esc_html__('Show single field:', 'astra-child');
}
$searchFields .= '<label>' . $onlyModifySingleFieldText . '</label>';
$searchFields .= '<select name="onlyModifySingleField" id="onlyModifySingleField" class="shared-files-category-select select_v2" onchange="onSelectSingleFieldChange()">';
$searchFields .= '<option class="level-0" id="notselected" value="notselected">' . esc_html__('Select single field to modify', 'astra-child') . '</option>';
if($onlyModifySingleField != null && $onlyModifySingleField == "description"){
	$searchFields .= '<option class="level-0" id="description" value="description" selected>' . esc_html__('Description', 'shared-files') . '</option>';
} else {
	$searchFields .= '<option class="level-0" id="description" value="description">' . esc_html__('Description', 'shared-files') . '</option>';
}

for ($n = 1; $n < $custom_fields_cnt; $n++) {
	$key = 'file_upload_custom_field_' . $n;
	if (isset($s[$key]) && $cf_title = sanitize_text_field($s[$key])) {
		if($onlyModifySingleField != null && $onlyModifySingleField == $key){
			$searchFields .= '<option class="level-0" id="' . $key . '" value="' . $key . '" selected>' . $cf_title . '</option>';
		} else {
			$searchFields .= '<option class="level-0" id="' . $key . '" value="' . $key . '">' . $cf_title . '</option>';
		}
	}
}
if($onlyModifySingleField != null && $onlyModifySingleField == "tags"){
	$searchFields .= '<option class="level-0" id="tags" value="tags" selected>' . esc_html__('Tags', 'shared-files') . '</option>';
} else {
	$searchFields .= '<option class="level-0" id="tags" value="tags">' . esc_html__('Tags', 'shared-files') . '</option>';
}
$searchFields .= '</select></div>';

// error_log("s: " . print_r($s,true));
//error_log("posts_per_page: " . print_r($posts_per_page,true));
// Zeilen pro Seite


// Download ZIP
$searchFields .= '<div class="elements-group">';
$searchFields .= '<div class="reset-button"><button type="button" id="reloadCurrentPage" title="' . esc_html__('Discard changes', 'astra-child') . '" disabled="true" class="button">' . esc_html__('Discard changes', 'astra-child') . '</button></div>';
$searchFields .= '<label style="margin-right: 10px; margin-left: 20px;">' . esc_html__('Rows per page', 'astra-child') . '</label><input type="text" name="elementsPerPage" id="elementsPerPage" style="width: 70px;" value="' . $posts_per_page . '" onblur="elementsPerPageChange(this.name, this.value)"/>';
$searchFields .= '<div class="button-wrapper">';
$searchFields .= '<button name="downloadZipBtn" style="float:right" onclick="onZipFileCreationClick()">' . esc_html__('Download ZIP', 'astra-child') . '</button>';
$searchFields .= '<input type="hidden" name="excelImportFilename" id="excelImportFilename"/>';
$searchFields .= '<input type="hidden" name="doExcelExport" id="doExcelExport"/>';

// Download Excel Liste
$searchFields .= '<button name="excepExportBtn" style="float:right" onclick="onExcelExportclick()">' . esc_html__('Excel Export', 'astra-child') . '</button>';
$searchFields .= '<input type="hidden" name="zipfilename" id="zipfilename"/>';
$searchFields .= '<input type="hidden" name="createzipFile" id="createzipFile"/>';
$searchFields .= '</div></div>';
$searchFields .= '</div>';
$searchFields .= '</form>';

// ------------------------------------------------------------------------------------------------------------------------
// Form mit Daten
$adminUrl = get_admin_url();
$postUrl = $adminUrl . 'shared_files_extensions/sharedfiles_post.php';

$table = '<form method="post" name="dataForm" id="dataForm" enctype="application/x-www-form-urlencoded" action="' . $postUrl . '">';
$table .= '<div class="table-scroll-wrapper">';

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
	"nurKategorienAnzeigen" => $nurKategorienAnzeigen,
	"onlyModifySingleField"  => $onlyModifySingleField
);

// error_log("searchString: " . $search);
// Datenabfrage
$data = queryData($parameters);
//  error_log("data result " . print_r($data, true));

if ($data["headrow"] && $data["headrowKat"] && $data["keys"]) {
	$headRow = $data["headrow"];
	if ($nurKategorienAnzeigen == true) {
		$headRow = $data["headrowKat"];
	}
	if($onlyModifySingleField != null && $onlyModifySingleField != "notselected"){
		$headRow = $data["headRowSingleFields"];
	}

	// Kopfzeile
	$table .= '<div class="row header">';
	foreach ($headRow as $element) {
		$table .= '<div class="cell">' . $element . '</div>';
	}
	$table .= "</div>";

	$outerArrayKeys = array_keys(($data));
	$dataArrayKeys = $data["keys"];

	// Datenzeilen
	$rowIndex = 0;
	foreach ($outerArrayKeys as $outerKey) {
		if ($outerKey != "keys" && $outerKey != "headrow" && 
			$outerKey != "headrowKat" && $outerKey != "headRowSingleFields" && 
			$outerKey != "args" && $outerKey != "total") {
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
						if(($onlyModifySingleField == null || $onlyModifySingleField == "notselected") ||
							($onlyModifySingleField != null && 
							str_starts_with($onlyModifySingleField, "file_upload_custom_field_") &&
							str_ends_with($onlyModifySingleField, $secondKey))) {
							addCustomFieldField($row, $file_id, $secondKey, $element, $inputArray, $isReadonlyUser);
						}
					} else if ($firstKey == "category" &&
						($onlyModifySingleField == null || $onlyModifySingleField == "notselected")) {
						$catValue = "";
						if ($element == "Ja") {
							$catValue = "on";
						}
						$categoryTerm = arrayFindObjectElement($allcategories, "term_id", $secondKey);

						addCategoryField($row, $file_id, $categoryTerm, $catValue, $checkboxArray, $isReadonlyUser);
					}
				} else {
					$element = $dataRowArray[$dataKey];

					if ($dataKey == "file_id") {

						$file_id = $element;
						$row = "";
						$row .= '<div class="row">
						<div class="cell">' . $file_id . '</div>';
					} else if ($dataKey == "title") {
						addTitleField($row, $file_id, $element, $inputArray, $isReadonlyUser);
					} else if ($dataKey == "description" && $nurKategorienAnzeigen == false) {
						if(($onlyModifySingleField == null || $onlyModifySingleField == "notselected") ||
						($onlyModifySingleField != null && 
						str_starts_with($onlyModifySingleField, "description"))) {						
							addDescriptionField($row, $file_id, $element, $inputArray, $isReadonlyUser);
						}
					} else if ($dataKey == "tags" && $nurKategorienAnzeigen == false) {
						if(($onlyModifySingleField == null || $onlyModifySingleField == "notselected") ||
						($onlyModifySingleField != null && 
						str_starts_with($onlyModifySingleField, "tags"))) {	
							addTagsField($row, $file_id, $element, $inputArray, $isReadonlyUser);
						}
					}
				}
			}

			$row .= "</div>";

			$tableRows[$outerKey] = $row;
		}
	}

	// add rows to output (sorting)
	$sortedKeys = array_keys($tableRows);
	sort($sortedKeys, SORT_NUMERIC);
	
	for ($i = 0; $i < count($sortedKeys); $i++) {
		$row =$tableRows[$sortedKeys[$i]];
		$table .= $row;
	}

	// Submit Button
	$table .= "</div>";
	
	if($isReadonlyUser == false) {
		$table .= '<input type="submit" name="submit" disabled="true" value="' . esc_html__('Save', 'shared-files') . '"></input>';
	}

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
	if($onlyModifySingleField != null){
		$pargs .= '&onlyModifySingleField=' . $onlyModifySingleField;
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
echo '<div id="cpt-update-wrapper">';
echo $searchFields . $table . $pagination;
echo '</div>';

echo '<div id="reloadPageModal" class="modal">
<div class="modal-content">
  <label>Änderungen verwerfen und Seite neu laden?</label>
  <div class="modal-actions">
    <button id="reloadPage">Verwerfen</button>
    <button id="cancelReloadPage">Abbrechen</button>
  </div>
</div>
</div>';

// error_log($searchFields . $table . $pagination);

// do_action( 'hestia_page_builder_blank_after_content' );

// hestia_no_content_get_footer();
get_footer();

wp_reset_postdata();
?>

<script>
	/**
	 * Set Caret position in search field
	 */
	function onloadInternally() {
		console.log("onloadInternally", document);
		let searchfield = document.getElementsByName("searchField");
		console.log("onloadInternally", searchfield)
		if (searchfield && searchfield[0] && insertSearchText) {
			searchfield[0].value = insertSearchText;
			if (insertSearchText.length > 0) {
				let caret = insertSearchText.length;
				searchfield[0].setSelectionRange(caret, caret);
			}
		}

		let reloadPageModal = document.getElementById("reloadPageModal");
	}

	document.getElementById("dataForm").addEventListener("submit", function(e) {
		// console.log("SUBMIT e", e);
		// e.preventDefault();
		
		const form = e.target;
		
		appendHiddenInput("searchField", form);
		appendHiddenInput("sf_category", form);
		appendHiddenInput("elementsPerPage", form);
		appendHiddenInput("nurKategorienAnzeigen", form, "on");
	});

	function appendHiddenInput(name, form, ischeckbox, explicitValue){
		const hiddenInput = document.createElement("input");
		hiddenInput.type = "hidden";
		hiddenInput.name = "referer_parm_" + name;
		let value;
		if(explicitValue){
			value = explicitValue;
		}
		else{
			let value = getInputValue(name);
			if(ischeckbox){
				value = getInputValueCheckbox(name);
			}
		}
		hiddenInput.value = value;
		
		if(value && value.length > 0)
		{
			form.appendChild(hiddenInput);
		}
	}

	var inputArrayJsOriginal = <?php echo json_encode($inputArray); ?>;
	var checkboxArrayJsOriginal = <?php echo json_encode($checkboxArray); ?>;

	var inputArrayJs = JSON.parse(JSON.stringify(inputArrayJsOriginal))
	var checkboxArrayJs = JSON.parse(JSON.stringify(checkboxArrayJsOriginal))

	var changedData;


	function inputOnChange(name, data) {
		this.handleInputOnChange(name, data, window.inputArrayJs, window.inputArrayJsOriginal, "modified-input", "value");
		this.computeAnyChangedData();
	}
	function checkboxOnChange(name, data) {
		this.handleInputOnChange(name, data, window.checkboxArrayJs, window.checkboxArrayJsOriginal, "modified-checkbox", "checked")
		var el = document.getElementById(name);
		if (data == true){
			el.value = "on";
			el.checked = true;
		}
		
		this.computeAnyChangedData();
	}

	function handleInputOnChange(name, data, arrayJs, arrayJsOriginal, modifiedClassName, field) {
		var el = document.getElementById(name);

		if (arrayJs) {
			let foundElement = arrayJs.find(el => { if (el[name] !== undefined) return el; })
			arrayJs.find(el => {
				if (el[name]) return el;
			})

			if (foundElement) {
				// console.log("handleInputChange foundElement", name, foundElement[name], foundElement[name].value, data);
				let foundElementOrig = arrayJsOriginal.find(el => { if (el[name] !== undefined) return el; })
				var equ = true;

				if(name.includes("_sf_file_cat")){
					foundElement[name].value = data;
					foundElement[name].checked = data == true;
					equ = foundElementOrig[name]["checked"] == foundElement[name]["checked"];
				}else{
					foundElement[name].value = data;

					equ = foundElementOrig[name][field] == foundElement[name][field];
				}
				// console.log("handleInputChange foundElementOrig", name, foundElementOrig[name], foundElementOrig[name].value);
			
				if (foundElementOrig && equ == false) {
					// console.log("DIFFERENT");
					this.markInputObject(name, false, modifiedClassName)
				} else {
					// console.log("EQUALS")
					this.markInputObject(name, true, modifiedClassName)
				}
			}
		}

		// console.log("modifiedElement", arrayJs);
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
				// console.log("CHANGE:", element);
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

			if ((foundElementOrig[name].value == "on" && element[name].value == false) ||
				(foundElementOrig[name].value != "on" && element[name].value == true)) {
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

	    let reloadCurrentPage = document.getElementById('reloadCurrentPage');
		// console.log("disableCategoryOptions reloadCurrentPage", reloadCurrentPage)
		reloadCurrentPage.disabled = changesExists == false;

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
		let onlyModifySingleField = document.getElementsByName("onlyModifySingleField");
		if (onlyModifySingleField.length > 0) {
			onlyModifySingleField[0].disabled = changesExists == true;
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
				// console.log(element);
				if (changesExists == true) {
					element.classList.add('disabled');
				}
				else {
					element.classList.remove('disabled');
				}
			}
		}


		// console.log("MODIFIED DATA changesExists, changedData, page_numbers", changesExists, changedData, pageNumbers);
	}

	function markInputObject(name, equals, modifiedClassName) {
		let inputObject = document.getElementsByName(name);

		if (inputObject && inputObject.length > 0) {
			//console.log("markInputObject ", name, equals, modifiedClassName)
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

	function getInputValueCheckbox(fieldName) {
		var fields = document.getElementsByName(fieldName);
		if (fields && fields.length == 1) {
			if(fields[0].checked){
				return "on";
			}
		}

		return null;
	}

	document.getElementById("reloadCurrentPage").addEventListener("click", () => {
		reloadPageModal.style.display = "flex";
	});

  document.getElementById("reloadPage").addEventListener("click", () => {
    reloadPageModal.style.display = "none";
     
    var form = document.getElementById("dataForm");

	appendHiddenInput("searchField", form);
	appendHiddenInput("sf_category", form);
	appendHiddenInput("elementsPerPage", form);
	appendHiddenInput("nurKategorienAnzeigen", form, "on");
    appendHiddenInput("reloadPage", form, null, "true")
    console.log("reloadPage before submit", form)
    // Natives submit erzwingen!
    HTMLFormElement.prototype.submit.call(form);
  });

  document.getElementById("cancelReloadPage").addEventListener("click", () => {
    reloadPageModal.style.display = "none";
  });

	function onInputSearchText(newPaged) {
		let pagedToSend = 1;
		if (newPaged) {
			paged = newPaged;
			pagedToSend = newPaged;
			// console.log("onInputSearchText new Page", newPaged)
		}

		var searchText = getInputValue("searchField");
		// console.log("onInputSearchText PAGED VARIABLE??? searchText, paged, posts_per_page, pagedToSend, custom_fields_cnt ", searchText, paged, posts_per_page, pagedToSend, custom_fields_cnt);

		if (searchText != null && searchText != undefined) {
			var cfCount = getInputValue("custom_fields_cnt");
			if (!cfCount)
				cfCount = 20;
				var searchText = getInputValue("searchField");
		var elementsPerPage = getInputValue("elementsPerPage");
				console.log("onInputSearchText form", document.getElementById("the-redirect-form"), searchText, elementsPerPage)
			 document.getElementById("the-redirect-form").submit();
		}
	}

	function elementsPerPageChange() {
		var form = document.getElementById('the-redirect-form');
		// var ele =document.getElementById("elementsPerPage")
		// var ele =document.getElementById("elementsPerPage")
		var searchText = getInputValue("searchField");
		var elementsPerPage = getInputValue("elementsPerPage");
		console.log("elementsPerPageChange form", form, searchText, elementsPerPage)

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
	function onSelectSingleFieldChange(){
		document.getElementById('nurKategorienAnzeigen').checked = false;

		document.getElementById('the-redirect-form').submit();
	}
	function onNurKategorienAnzeigenClick() {
		const onlyModifySingleField =document.getElementById('onlyModifySingleField');

		// Alle Optionen deaktivieren
		for (const option of onlyModifySingleField.options) {
			option.disabled = true;
		}

		document.getElementById('the-redirect-form').submit();
	}

</script>
