<?php
/**
 * TODO.
 *
 * TODO Manage Post actions: post, edit, delete, etc.
 *
 * @package WALLNER
 * @subpackage SharedFileExtension
 */

/** WordPress Administration Bootstrap */
// TODO: Ist diese Zeile NÖTIG???
require_once dirname(__DIR__) . '/admin.php';

include "sharedfiles_helperfunctions.php";
include "sharedfiles_post_savedetails.php";

$action = !empty($_REQUEST['action']) ? sanitize_text_field($_REQUEST['action']) : '';

/**
 * @global string       $post_type        Global post type.
 * @global WP_Post_Type $post_type_object Global post type object.
 * @global WP_Post      $post             Global post object.
 */
global $post_type, $post_type_object, $post;

if ($post) {
	$post_type = $post->post_type;
	$post_type_object = get_post_type_object($post_type);
}

$sendback_test = wp_get_referer();

$params = arrayFindObjectElementPerKeyContains($_POST, "referer_parm_");
$i = 0;
foreach($params as $key => $value){
	if($i == 0){
		$sendback_test .= "?" . $key . "=" . $value;
	}
	else {
		$sendback_test .= "&" . $key . "=" . $value;
	}
	$i++;
}

error_log("POST _POST " . print_r($_POST, true) . ", sendback_test url " . print_r($sendback_test, true));

if (isset($_POST['post_type']) && $post && $post_type !== $_POST['post_type']) {
	wp_die(__('A post type mismatch has been detected.'), __('Sorry, you are not allowed to edit this item.'), 400);
}


$vars = $_POST;
error_log("POST VALUES " . print_r($vars, true));

if (gettype($vars) == "array") {
	if(!array_key_exists("referer_parm_reloadPage", $vars)) {
		// Read post data
		$title_values = [];
		$desc_values = [];
		$customfield_values = [];
		$tag_values = [];
		$category_values = [];

		$keys = array_keys($vars);
		foreach ($keys as $key) {
			if (str_starts_with($key, "_sf_file_title_")) {
				$file_id = addSingleIdValuesToArray($vars, $key, $title_values);
			}
			if (str_starts_with($key, "_sf_file_description_")) {
				$file_id = addSingleIdValuesToArray($vars, $key, $desc_values);
			}
			if (str_starts_with($key, "_sf_file_cf")) {
				$file_id = addDoubleIdValuesToArray($vars, $key, $customfield_values);
			}
			if (str_starts_with($key, "_sf_file_tags")) {
				$file_id = addSingleIdValuesToArray($vars, $key, $tag_values);
			}
			if (str_starts_with($key, "_sf_file_cat")) {
				$file_id = addDoubleIdValuesToArray($vars, $key, $category_values);
			}
		}


		// error_log("Title values:" . print_r($title_values, true));
		// error_log("Description values:" . print_r($desc_values, true));
		// error_log("Tag values:" . print_r($tag_values, true));
		// error_log("Custom Field values:" . print_r($customfield_values, true));
		// error_log("Category values:" . print_r($category_values, true));

		// Update values
		$tfiltered = array_filter($title_values, "modifiedValues");
		// error_log("titles filtered " . print_r($tfiltered, true));

		saveTitles($tfiltered);

		$desfiltered = array_filter($desc_values, "modifiedValues");
		// error_log("Description values desfiltered:" . print_r($desfiltered, true));
		saveDescriptions($desfiltered);

		$tagsfiltered = array_filter($tag_values, "modifiedValues");
		saveTags($tagsfiltered);

		$customfield_valuesfiltered = modifiedValuesArray($customfield_values);
		saveCustomFields($customfield_valuesfiltered);

		error_log("category_values:" . print_r($category_values, true));
		$category_valuesfiltered = modifiedValuesArray($category_values);
		saveCategories($category_valuesfiltered);
	}
}

/*TODOs:
* Leser/Autoren
* Nicht berechtigte User

* Texte in fremder Sprache
	* TODO: Eintragen in shared-files.pot
	* Eintragen in loco-translate
	Nur Kategorien bearbeiten Modify categories only
	Nur Kategorien anzeigen Show only categories
	Zeilen pro Seite Rows per page
	Download ZIP Download ZIP
	Excel Export Excel Export
	Titel Title
	Beschreibung Description
	Speichern Save

	Choose category (inactive if search filter inserted or changes exist):
	Kategorie wählen (inaktiv wenn Suchfilter ein oder Änderungen):
	Create category Kategorie anlegen
	Delete category Kategorie löschen
	Available titles Verfügbare Einträge
	Mapped titles Zugeordnete Einträge
	Create Neu 
	Delete Löschen

	Modify single field: Einzelnes Feld ändern:
	Show single field: Einzelnes Feld anzeigen:
	please select (optional)  bitte wählen (optional)

	Select single field to modify Einzelnes Feld zur Bearbeitung auswählen


ASTRA mit Styling: Alle Funtionen testen
funktioniert grundsätzlich, einiges Styling auch schon gemacht

TODO 24072025:
* Stylings gut dokumentieren --> erledigt
* Stylings in eigenes css auslagern --> erledigt
* Alle Texte in Englisch und zu übersetzen: POT-File --> erledigt


* Template name refactoring


-----------------------------------------------------------------------------------
6. Tests
7. Doku, welche Files alles erstellt und angepasst werden müssen
	Doku für Autoren
	Doku für Leser
8. Integration

-----------------------------------------------------------------------------------
Weitere Features:
* Verwerfen Button: Seite neu laden
* Zip-File download der Daten für eine Kategorie, max 30 Lieder (ca. 20-30MB) // Fertig
	* Speichergröße beschränken???
	* Nur die aktuelle Seite
	* Alle Seiten in einzelne Files

-----------------------------------------------------------------------------------
* Weiteres Kategorienzuordnungsformular??? 
	--> Auswahl einer Kategorie
	--> Liedersuche, dann Klick um Hinzuzufügen
*/

wp_redirect($sendback_test);