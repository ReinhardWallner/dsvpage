<?php
/**
 * Template Name: CPT Category Update
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
	error_log("Nicht berechtigt! " . print_r($pagename, true));
	// TODO REDIRECT
	return;
}

$homepath = get_home_path();
$homepath = str_replace("/", DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, $homepath);
$sharedfilefolder = $homepath . "wp-admin" . DIRECTORY_SEPARATOR  . "shared_files_extensions" . DIRECTORY_SEPARATOR ;

include  $sharedfilefolder . "sharedfiles_querydata.php";
include  $sharedfilefolder . "sharedfiles_helperfunctions.php";

$s = get_option('shared_files_settings');
$custom_fields_cnt = intval($s['custom_fields_cnt']) + 1;

// error_log("TEMPLATE CAT START _POST " . print_r($_POST, return: true));
// error_log("TEMPLATE CAT START _GET " . print_r($_GET, true));

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

// end attention: This code has before get_header section!!!

get_header();


$home = home_url( $wp->request );

$searchFields = '<form
   id="the-redirect-form-categories" 
   method="post" 
   action="' . $home . '" 
   enctype="multipart/form-data">';

// Tabellenspalten
$searchFields .= '<div>';
$searchFields .= '<table style="width: 100%">
    <colgroup>
       <col span="1" style="width: 30%;" />
       <col span="1" style="width: 70%;" />
    </colgroup><tr><td>';

// Suchfeld
if ($search) {
	$searchFields .= '<input type="text" name="searchField" autofocus placeholder="' . esc_html__('Search files...', 'shared-files') . '" value="' . $search . '" oninput="onInputSearchText()"  />';
} else {
	$searchFields .= '<input type="text" name="searchField" autofocus placeholder="' . esc_html__('Search files...', 'shared-files') . '" oninput="onInputSearchText()" />';
}

$searchFields .= '</td><td>';
error_log("CATEGORY IN CAT UPD " . print_r($category, true));
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
  'hide_if_empty' => false, // nur wenn keine Begriffe existieren
  'hide_empty' => false     // zeigt auch leere Begriffe an!
);

$categoryDropdowm = wp_dropdown_categories($argsCategoryCombo);
$endIndex = strpos($categoryDropdowm, ">");
$categoryDropdowm = str_replace("class='shared-files-category-select select_v2'>", 'class="shared-files-category-select select_v2" onchange="onCategoryChange()">', $categoryDropdowm);

// Nur Kategorien bearbeiten
$searchFields .= '<div class="shared-files-category-select-container">';
$searchFields .= '<label for="sf_category">' . __('Kategorie wählen (inaktiv wenn Suchfilter ein oder Änderungen):', 'shared-files') . '</label>';
$searchFields .= '<div class="select-with-button">';
$searchFields .= $categoryDropdowm;
$searchFields .= ' <button type="button" id="addNewCategory" title="Kategorie hinzufügen" class="button">Neu</button>';
$searchFields .= '</div>';
$searchFields .= '</div></td></tr></table></div></form>';

$adminUrl = get_admin_url();
$postUrl = $adminUrl . 'shared_files_extensions/sharedfiles_category_post.php';

$form = '<form method="post" name="dataForm-categories" id="dataForm-categories" enctype="application/x-www-form-urlencoded" action="' . $postUrl . '">';
$form .= '<div class="table-scroll-wrapper">';

// Datenabfrage
// $parametersAll = array(
// 	"posts_per_page" => $posts_per_page,
// 	"paged" => $paged,
// 	"wpdb" => $wpdb,
// 	"search" =>"",
// 	"category" => "",
// 	"custom_fields_cnt" => $custom_fields_cnt,
// 	"allcategories" => $allcategories,
// 	"tag_slug" => $tag_slug,
// 	"settings" => $s,
// 	"nurKategorienAnzeigen" => false
// );

// $dataAll = queryData($parametersAll);
$parametersSearch = array(
	"posts_per_page" => $posts_per_page,
	"paged" => $paged,
	"wpdb" => $wpdb,
	"search" => "",
	"category" => "",
	"custom_fields_cnt" => $custom_fields_cnt,
	"allcategories" => $allcategories,
	"tag_slug" => $tag_slug,
	"settings" => $s,
	"nurKategorienAnzeigen" => false
);

// Datenabfrage
$dataSearch = queryData($parametersSearch);

// error_log("ALL DATA " . print_r($dataSearch, true));

$rowIndex = 0;

$outerArrayKeys = array_keys(($dataSearch));
$dataArrayKeys = $dataSearch["keys"];

$fileCategoryMapping = [];
$categoryFileMapping = [];
foreach ($allcategories as $category1) {
  // error_log("category " . print_r($category1, true));
  $categoryFileMapping[$category1->slug] = ['catName' => $category1->slug,
                                            'files' => []];
}
// error_log("categoryFileMapping Start " . print_r($categoryFileMapping, true));

// Get titles
$sortedFileList = [];
foreach ($outerArrayKeys as $outerKey) {
    if ($outerKey != "keys" && $outerKey != "headrow" && $outerKey != "headrowKat" && $outerKey != "args" && $outerKey != "total") {
        $dataRowArray = $dataSearch[$outerKey];
        $file_id = null;
        foreach ($dataArrayKeys as $dataKey) {
            $element = null;
            $isa = is_array($dataKey);
            if (is_array($dataKey)) {
                $firstKey = array_key_first($dataKey);
                $secondKey = $dataKey[$firstKey];
                $element = $dataRowArray[$firstKey][$secondKey];

                if ($firstKey == "category") {
                    $categoryTerm = arrayFindObjectElement($allcategories, "term_id", $secondKey);
                    if ($element == "Ja") {
                        // error_log("cat term " . print_r($element, true) . ": " . print_r($categoryTerm, true));
                        $catEl = [
                          'term_id' => $categoryTerm->term_id,
                          'slug' => $categoryTerm->slug];
                        // error_log("cat mapping fileid " . print_r($fileCategoryMapping[$file_id], true));
                        // error_log("cat mapping fileid " . print_r($fileCategoryMapping[$file_id]["categories"], true));
                        array_push($fileCategoryMapping[$file_id]["categories"], $catEl);
                        array_push($categoryFileMapping[$categoryTerm->slug]['files'], $file_id);
                    }
                }
            } else {
                $element = $dataRowArray[$dataKey];
                if ($dataKey == "file_id") {
                  $file_id = $element;
                  $fileCategoryMapping[$file_id] = ["categories" => []];
                  // error_log("fileCategoryMapping " . print_r($fileCategoryMapping, true));
                }else if ($dataKey == "title") {
                    // error_log("title " . print_r($element, true));
                    $sortedFileList[$outerKey] = [ "fileName" => $element,
                                                  "file_id" => $file_id];
                    $fileCategoryMapping[$file_id]["fileName"] = $element;
                }
            }
        }
    }
}
// error_log("categoryFileMapping End " . print_r($categoryFileMapping, true));

echo '<div id="cpt-update-wrapper">';
echo $searchFields;


// sort titles
$allTitles = [];
$categoryTitles = [];
for ($i = 0; $i < count($sortedFileList); $i++) {
    $element = $sortedFileList[$i]["fileName"];
    $fileId = $sortedFileList[$i]["file_id"];
    array_push($allTitles, $element);
    $form .= $fileId . " " . $element . "<br />";
}

$form .= "</div><div class=\"dual-listbox-wrapper\">
<div class=\"dual-listbox\">
  <!-- Linke Liste -->
  <div class=\"dual-list\">
    <label>Verfügbare Einträge</label>
    <select id=\"listLeft\" multiple disabled='true' ondblclick='leftListhandleDoubleClick()'></select>
  </div>

  <!-- Buttons -->
  <div class=\"dual-buttons\">
  <button type='button' class='button button-primary' onclick='moveSelected(\"listLeft\",\"selectedFiles\")'>→</button>
    <button type='button' class='button' onclick='moveSelected(\"selectedFiles\",\"listLeft\")'>←</button>
  </div>

  <!-- Rechte Liste -->
  <div class=\"dual-list\">
    <label>Zugeordnete Einträge</label>
    <select id=\"selectedFiles\" name=\"selectedFiles[]\" multiple disabled='true' ondblclick='rightListhandleDoubleClick()'></select>
  </div>
</div>
</div>";


// error_log("ALL DATA allTitles " . print_r($allTitles, true));

$form .= '<input type="submit" name="submit" disabled="true" value="' . esc_html__('Save', 'shared-files') . '"></input>';

$form .= '</form>';

echo $form;

echo '</div>';

echo '<div id="newCategoryModal" class="modal">
<div class="modal-content">
  <h3>Neue Kategorie anlegen</h3>
  <input type="text" id="newCategoryInput" placeholder="Kategoriename eingeben">
  <div class="modal-actions">
    <button id="saveCategory">Speichern</button>
    <button id="cancelCategory">Abbrechen</button>
  </div>
</div>
</div>';

// error_log("form " . print_r($form, true));


// **************************************************
// create javascript data
$catagoriesArr = [];
foreach ($allcategories as $obj) {
  $catagoriesArr[$obj->term_id] = $obj->name;
}
// error_log("catagoriesArr " . print_r($catagoriesArr, true));

?>

<!-- pass variables to javascript -->
<script>
  	/**
	 * Set Caret position in search field
	 */
	function onloadInternally() {
    if(selectedCategory){
      let select = document.getElementById('sf_category');
      if(select)
      {
        // set values in list entries
        onCategoryChange();
      }
    } else { 
      applyFilter();
    }
	}

let modal = document.getElementById("newCategoryModal");
let input = document.getElementById("newCategoryInput");

document.getElementById("addNewCategory").addEventListener("click", () => {
  modal.style.display = "flex";
  input.value = ""; // Eingabefeld leeren
  input.focus();
});

document.getElementById("saveCategory").addEventListener("click", () => {
  let newCategory = input.value.trim();
  if (newCategory) {
    console.log("Neue Kategorie:", newCategory);
    modal.style.display = "none";
    // alert(`Kategorie "${newCategory}" wurde angelegt!`);

    var form = document.getElementById("dataForm-categories");
    console.log("Neue Kategorie Form", form);
    appendHiddenInput("sf_category_createnew", form, false, newCategory);
    // Natives submit erzwingen!
    HTMLFormElement.prototype.submit.call(form);
  }
});

document.getElementById("cancelCategory").addEventListener("click", () => {
  modal.style.display = "none";
});


  document.getElementById("dataForm-categories").addEventListener("submit", function (e) {
    const selected = document.getElementById("selectedFiles").options;
      // e.preventDefault();
    for (let option of selected) {
      option.selected = true; // Alle Optionen als ausgewählt markieren
    }

    const form = e.target;
    // console.log("SUBMIT dataForm-categories form", form);
		appendHiddenInput("sf_category", form);
    appendHiddenInput("searchField", form);
  });

  function appendHiddenInput(name, form, ischeckbox, explicitValue){
      const hiddenInput = document.createElement("input");
      hiddenInput.type = "hidden";
      hiddenInput.name = "referer_parm_" + name;
      let value;
      if(explicitValue)
        value = explicitValue;
      else
        value = getInputValue(name);
      // if(ischeckbox){
      //   value = getInputValueCheckbox(name);
      // }
      hiddenInput.value = value;
      // console.log("HIDDEN INPUT ", hiddenInput)
      form.appendChild(hiddenInput);
  }

  function getInputValue(fieldName) {
		var fields = document.getElementsByName(fieldName);
		if (fields && fields.length == 1) {
      // console.log("getInputValue", fields[0], allCategories)
			return fields[0].value;
		}

		return null;
	}
    
	var allCategories = <?php echo json_encode($allcategories) ?>;
	var insertSearchText = <?php echo json_encode($search) ?>;
  var selectedCategory = <?php echo json_encode($category) ?>;

  var catagoriesArr1 = <?php echo json_encode($catagoriesArr) ?>;
  var fileCategoryMapping = <?php echo json_encode($fileCategoryMapping) ?>;
  var categoryFileMapping = <?php echo json_encode($categoryFileMapping) ?>;
  var sortedFileList = <?php echo json_encode($sortedFileList) ?>;
  
  // console.log("cat arr", catagoriesArr1);
  // console.log("fileCategoryMapping", fileCategoryMapping);
  // console.log("categoryFileMapping", categoryFileMapping);
  // console.log("sortedFileList", sortedFileList);

  // Funktion zum Verschieben von Optionen
  function moveSelected(fromId, toId) {   
    const from = document.getElementById(fromId);
    const to = document.getElementById(toId);

    Array.from(from.selectedOptions).forEach(option => {
      console.log("moveSelected fromId, toId, option", from, to, option);
      let index = getIndexForFileInsert(option.value, to);

      if (index >= to.options.length) {
        to.add(option); // am Ende hinzufügen
      } else {
        to.insertBefore(option, to.options[index]); // vor dem Element am Index einfügen
      }
    });

    disableCategoryOptions();
  }

  function updateTooltip(btn) {
    if (btn.disabled) {
      btn.title = "Button ist inaktiv, weil ungespeicherte Änderungen existieren."
      btn.style.cursor = "not-allowed";
    } else {
      btn.title = "Kategorie hinzufügen";
      btn.style.cursor = "pointer";
    }
  }

  function leftListhandleDoubleClick() {
    const option = document.getElementById('listLeft');
    if(option.selectedOptions && option.selectedOptions.length == 1) {
      moveSelectedId(listLeft, selectedFiles, option.selectedOptions[0]);
    }
  }

  function rightListhandleDoubleClick() {
    const option = document.getElementById('selectedFiles');
    if(option.selectedOptions && option.selectedOptions.length == 1) {
      moveSelectedId(selectedFiles, listLeft, option.selectedOptions[0])
    }
  }

  function moveSelectedId(fromList, toList, option) {
    let opts = fromList.selectedOptions;
    let index = getIndexForFileInsert(option.value, toList);
    if (index >= toList.options.length) {
      toList.add(option); // am Ende hinzufügen
    } else {
      toList.insertBefore(option, toList.options[index]); // vor dem Element am Index einfügen
    }

    disableCategoryOptions();
  }

  function disableCategoryOptions(){
    let newCategory = document.getElementById('addNewCategory');
    newCategory.disabled = true;
    updateTooltip(newCategory);

    let select = document.getElementById('sf_category');
    select.disabled = true;
  }

  function getIndexForFileInsert(fileId, targetList) {
    //  console.log("getIndexForFileInsert fileId, targetlist, sortedFilelist", fileId, targetList, sortedFileList);
    if(targetList.options.length == 0){
      //  console.log("getIndexForFileInsert return 0 because targetList is empty");
      return 0;
    }

    let foundFileEntry = null;
    let elementsBefore = [];
    const keys = Object.keys(sortedFileList);
    for (const key of keys) {
      let fileEntry = sortedFileList[key];
      if(fileEntry["file_id"] == fileId){
        foundFileEntry = fileEntry;
        break;
      }

      elementsBefore.push(fileEntry);
    }
    //  console.log("getIndexForFileInsert foundFileEntry elementsBefore", foundFileEntry, elementsBefore);
    // find position in target list
    if(elementsBefore.length == 0){
      //  console.log("getIndexForFileInsert return 0 because elements before LIST is empty");
       return 0;
    } else {
      for(let i = elementsBefore.length; i--; i => 0){
        let element = elementsBefore[i];
        //  console.log("    CHECK element " + i + ", " + element["fileName"]);
        for(let j = 0; j < targetList.options.length; j++){
          let option = targetList.options[j];
          //  console.log("         CHECK target value " + option.value + ", " + element["fileName"]);
          if(option.value == element["file_id"]){
            //  console.log("getIndexForFileInsert return " + (j + 1) + " because elements before " + element["fileName"] + " found");
            return j + 1;
          }
        }
      }
    }
    
    //  console.log("getIndexForFileInsert return 0 because elements before not found");
    return 0;
  }

  function onCategoryChange() {
    searchParametersChange();
    applyFilter();
	}

  function onInputSearchText() {
    applyFilter();
  }

  function searchParametersChange(){
    let select = document.getElementById('sf_category');
    let catName = select.selectedOptions.length === 1 ? select.value : undefined;

    let applyParameters = catName != null && catName.length > 0;
    let saveBtn = document.getElementsByName("submit");
     if(catName == null || catName.length == 0) {
      if (saveBtn.length > 0) {
        saveBtn[0].disabled = true;
        listLeft.disabled = true;
        selectedFiles.disabled = true;
      }
      return;
    }

    const selectedFiles = document.getElementById("selectedFiles");
    const listLeft = document.getElementById("listLeft");

    if (saveBtn.length > 0) {
      saveBtn[0].disabled = false;
      listLeft.disabled = false;
      selectedFiles.disabled = false;
    }

   
    while (listLeft.options.length > 0) {
      listLeft.remove(0); // Entfernt immer das erste Element, bis leer
    }
    while (selectedFiles.options.length > 0) {
      selectedFiles.remove(0); // Entfernt immer das erste Element, bis leer
    }

    const keys = Object.keys(sortedFileList);
    for (const key of keys) {
      let fileEntry = sortedFileList[key];
      // console.log("File ID:", fileEntry );
      // contains categories array
      if(fileCategoryMapping){
        let fileCatMapping = fileCategoryMapping[fileEntry["file_id"]];
        // console.log("File fileCatMapping:", fileCatMapping );
        const option = document.createElement("option");
        option.text = fileEntry["fileName"];
        option.value = fileEntry["file_id"];      

        let mappedCategory = fileCatMapping["categories"].find(c => c.slug == catName);
        if(mappedCategory) {
          selectedFiles.add(option);
        } else {
            listLeft.add(option);
        }
      }
    }
  }

  function applyFilter() {
    let select = document.getElementById('sf_category');
    let catName = select.selectedOptions.length === 1 ? select.value : undefined;

    if (!catName) {
      console.log("applyFilter unwirksam, keine Kategorie gewählt");
      return;
    }

   
    let searchFilter = getInputValue("searchField");
    const keys = Object.keys(sortedFileList);
    
    select.disabled = searchFilter && searchFilter.length > 0;  
    
    let selectedFiles = document.getElementById("selectedFiles");
    let selectedValues = Array.from(selectedFiles.options).map(option => parseInt(option.value)); 
    // console.log("SelectedFiles: ", selectedValues); // z.B. [33, 38, 37]

    // Liste leeren
    listLeft.options.length = 0;

    for (const key of keys) {
      let fileEntry = sortedFileList[key];
      let fileId = parseInt(fileEntry["file_id"]);
      let fileName = fileEntry["fileName"];

      let isSelected = selectedValues.includes(fileId);
      //  console.log("File ID: includes?", fileEntry, isSelected, fileName);

      if (!isSelected) {
        const option = document.createElement("option");
        option.text = fileEntry["fileName"];
        option.value = fileId;

        // Optional: Filter nach Suchfeld oder Kategorie aktivieren
        if (!searchFilter || (searchFilter && fileName.toLowerCase().includes(searchFilter.toLowerCase()))) {
          listLeft.add(option);
        }
      }
    }
  }

  //TODO: 
  // Anlegen neuer Kategorien --> erdledigt
  // Ausgewählte Kategorie löschen

  // Alle Files importieren
  // Massentests
</script>