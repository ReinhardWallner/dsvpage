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

	
	// $val = "No";
	// if ($value == true)
	// 	$val = "Yes";

	// if ($hidden) {
	// 	if ($value)
	// 		return '<input type="hidden" name="' . $name . '" id="' . $name . '" checked value=' . $value . ' />';
	// 	else
	// 		return '<input type="hidden" name="' . $name . '" id="' . $name . ' value=' . $value . ' "/>';
	// } else {
	// 	if ($value)
	// 		return '<input type="checkbox" name="' . $name . '" id="' . $name . '" checked value=' . $value . ' onchange="checkboxOnChange(this.name, this.value)"/>';
	// 	else
	// 		return '<input type="checkbox" name="' . $name . '" id="' . $name . ' value=' . $value . ' onchange="checkboxOnChange(this.name, this.checked)"/>';
	// }
	
	
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
?>

<?php
//$table = '<form method="post" enctype="multipart/form-data" action="http://localhost:8081/dsvpage/wp-admin/post_wallner.php">';
$table = '<form method="post" name="myForm" enctype="application/x-www-form-urlencoded" action="http://localhost:8081/dsvpage/wp-admin/post_wallner.php">';
$table .= '<table style="margin: 10px;">';
$table .= "<tr><td>Id</td><td>Titel</td><td>Beschreibung</td>";

$args = array('post_type' => 'shared_file', 'posts_per_page' => 10);

$s = get_option('shared_files_settings');
$tag_slug = 'post_tag';
if (isset($s['tag_slug']) && $s['tag_slug']) {
	$tag_slug = sanitize_title($s['tag_slug']);
}

$taxonomy_slug = 'shared-file-category';

// Custom Fields Header
$s = get_option('shared_files_settings');

$custom_fields_cnt = intval($s['custom_fields_cnt']) + 1;
for ($n = 1; $n < $custom_fields_cnt; $n++) {
	if (isset($s['file_upload_custom_field_' . $n]) && $cf_title = sanitize_text_field($s['file_upload_custom_field_' . $n])) {
		$table .= "<td>" . $cf_title . "</td>";
	}

}

// Tags Header
$table .= "<td>Tags</td>";

// Categories Header
$allcategories = get_terms($taxonomy_slug, array('hide_empty' => 0));

foreach ($allcategories as $category) {
	$table .= '<td>' . sanitize_title($category->slug) . '</td>';
}
$table .= "</tr>";

$the_query_terms = new WP_Query($args);

if ($the_query_terms->have_posts()):
	while ($the_query_terms->have_posts()):
		$the_query_terms->the_post();
		$file_id = intval(get_the_id());
		$table .= "<tr><td>" . $file_id . "</td>";
		$table .= '<td>';
		$table .= getInputField($file_id, null, '_sf_file_title_' . $file_id, get_the_title(), $inputArray);
		$table .= getInputField($file_id, null, '_sf_file_title_origin_' . $file_id, get_the_title(), $inputArray, true);
		$table .= '</td>';


		// Custom fields
		$s = get_option('shared_files_settings');
		$c = get_post_custom($file_id);
		$desc = $c["_sf_description"][0];
		if ($desc !== null && trim($desc) !== '') {
			$desc = str_replace('<p>', '', $desc);
			$desc = str_replace('</p>', '', $desc);
		}

		$table .= '<td>';
		$table .= getInputField($file_id, null, '_sf_file_description_' . $file_id, $desc, $inputArray);
		$table .= getInputField($file_id, null, '_sf_file_description_origin_' . $file_id, $desc, $inputArray, true);
		$table .= '</td>';

		$custom_fields_cnt = intval($s['custom_fields_cnt']) + 1;
		for ($n = 1; $n < $custom_fields_cnt; $n++) {
			$val = "";
			if (isset($s['file_upload_custom_field_' . $n]) && $cf_title = sanitize_text_field($s['file_upload_custom_field_' . $n])) {
				if (isset($c['_sf_file_upload_cf_' . $n]) && $c['_sf_file_upload_cf_' . $n]) {
					$val = sanitize_text_field($c['_sf_file_upload_cf_' . $n][0]);
				}
			}
			$table .= '<td>';
			$table .= getInputField($file_id, $n, '_sf_file_cf_' . $file_id . '_' . $n, $val, $inputArray);
			$table .= getInputField($file_id, $n, '_sf_file_cf_origin_' . $file_id . '_' . $n, $val, $inputArray, true);
			$table .= '</td>';
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

		$table .= '<td>';
		$table .= getInputField($file_id, null, '_sf_file_tags_' . $file_id, $tagValue, $inputArray);
		$table .= getInputField($file_id, null, '_sf_file_tags_origin_' . $file_id, $tagValue, $inputArray, true);
		$table .= '</td>';

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

				$table .= '<td>';
				$table .= getCheckboxField($file_id, $category->name, '_sf_file_cat_' . $file_id . '_' . $category->name, $catValue, $checkboxArray);
				$table .= getCheckboxField($file_id, $category->name, '_sf_file_cat_origin_' . $file_id . '_' . $category->name, $catValue, $checkboxArray, true);
				$table .= '</td>';
			}

		}

		$table .= "</tr>";

	?>
	<?php endwhile;

	$table .= "</table>";
	$table .= '<input type="submit" name="submit" value="SpeichernInput"></input>';
	$table .= '</form>';
	echo $table;

	//	error_log(print_r($table, true));

	//echo '<button type="submit" name="saveBtn" onclick="onSaveClick()" disabled>Speichern</button>';

	// print ("<pre>" . print_r($inputArray, true) . "</pre>");
	// print ("<pre>" . print_r($checkboxArray, true) . "</pre>");

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
		if(data == true)
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
			console.log("foundElement ", foundElement)
			arrayJs.find(el => {
				// console.log("IS TO FIND? ", name, el, el[name]);
				if (el[name]) return el;
			})

			if (foundElement) {
				foundElement[name].value = data;
				console.log("foundElement modified ", foundElement)
				let foundElementOrig = arrayJsOriginal.find(el => { if (el[name] !== undefined) return el; })
				console.log("foundElement compare: " + foundElement[name] + " <--> " + foundElementOrig[name])
				if (foundElementOrig && foundElementOrig[name] != foundElement[name]) {
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

		let saveBtn = document.getElementsByName("saveBtn");
		if (saveBtn.length > 0) {
			saveBtn[0].disabled = changesExists == false;
		}

		console.log("MODIFIED DATA", changedData);
	}

	function markInputObject(name, equals, modifiedClassName) {
		let inputObject = document.getElementsByName(name);

		console.log('INPUT:', inputObject, inputObject[0].style, inputObject[0].class)
		if (inputObject && inputObject.length > 0) {
			if (equals)
				inputObject[0].className = null;
			else
				inputObject[0].className = modifiedClassName;
		}
	}

	async function onSaveClick() {
		var el = document.getElementById("_sf_file_title_40");
		console.log("SAveClick el", el)
		await this.submitData();
	}

	async function submitData() {
		console.log("submitData start");
		const formData = new FormData();
		formData.append("username", "reini");
		/*
		 try {
			  let response = await fetch(
					"http://localhost:8081/dsvpage/wp-admin/admin-ajax.php",
					{
						method: "POST",

						body: formData,
					}
			  );
			  console.log("submitData response", response);
			   if (response && response.status == 200) {

			   }

			  // return response.json();
		 } catch (err) {
			  console.error("submitData err", err);
			  return;
		 }
			*/

		// wp_enqueue_script('jquery');
		console.log("submitData before jquery");
		try {
			/*
			// jQuery('input[type=submit]').click(function (e) {
			console.log("submitData before jquery");
			// e.preventDefault();
			console.log("submitData before jquery");
			jQuery.post("http://localhost:8081/dsvpage/wp-admin/wp-admin/admin-ajax.php"({
				type: "POST",
				url: "http://localhost:8081/dsvpage/wp-admin/wp-admin/admin-ajax.php",
				//data: "action=newbid&id=" + <?php echo $post->ID ?>,
			data: formData,
				success: function (msg) {
					console.log("SUCCESS", msg)
				},
			error: function () {
				console.log("ERROR")
			},
		});
			*/

		console.log("submitData before jquery formData", formData);
		// var data = new URLSearchParams(Array.from(new FormData(formData))).toString();
		var data = JSON.stringify(formData);
		console.log("submitData before jquery data", data);
		jQuery.ajax({
			url: 'http://localhost:8081/dsvpage/wp-admin/wp-admin/admin-ajax.php',
			type: "post",
			data: { "username": "reini" },
			dataType: "json",
			success: function (response) {
				if (response.result) {

				}
				else {

				}
			}
		});

		console.log("submitData after jquery");

		// });
	}
		catch (e) {
		console.log("submitData EXCEPTION", e);
	}
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
</style>


<?php get_footer(); ?>