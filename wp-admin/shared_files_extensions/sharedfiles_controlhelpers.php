<?php
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

function getCheckboxField($file_id, $cat_name, $name, $value, &$checkboxArray, $isReadonlyUser, $hidden = false)
{
	$obj = new stdClass();
	$obj->file_id = $file_id;
	$obj->cat_name = $cat_name;
	$obj->value = $value;
	if($value)
		$obj->checked = true;
	else
		$obj->checked = false;
	
	array_push($checkboxArray, [$name => $obj]);

	if ($hidden) {
		if ($value)
			return '<input type="hidden" name="' . $name . '" id="' . $name . '" checked value="' . $value . '" />';
		else
			return '<input type="hidden" name="' . $name . '" id="' . $name . '" value="' . $value . '" "/>';
	} else {
		if($isReadonlyUser){
			if ($value)
				return '<input type="checkbox" name="' . $name . '" id="' . $name . '" disabled checked value="' . $value . '" onchange="checkboxOnChange(this.name, this.checked)"/>';
			else
				return '<input type="checkbox" name="' . $name . '" id="' . $name . '" disabled value="' . $value . '" onchange="checkboxOnChange(this.name, this.checked)"/>';
		}
		else{
			if ($value)
				return '<input type="checkbox" name="' . $name . '" id="' . $name . '" checked value="' . $value . '" onchange="checkboxOnChange(this.name, this.checked)"/>';
			else
				return '<input type="checkbox" name="' . $name . '" id="' . $name . '" value="' . $value . '" onchange="checkboxOnChange(this.name, this.checked)"/>';
		}
	}

}

function addTitleField(&$table, $file_id, $title, &$inputArray, $isReadonlyUser)
{
	$table .= '<div class="cell">';
	if($isReadonlyUser == true) {
		$table .= $title;
	}
	else {
		$table .= getInputField($file_id, null, '_sf_file_title_' . $file_id, $title, $inputArray);
		$table .= getInputField($file_id, null, '_sf_file_title_origin_' . $file_id, $title, $inputArray, true);
	}

	$table .= '</div>';
}

function addDescriptionField(&$table, $file_id, $desc, &$inputArray, $isReadonlyUser)
{
	$table .= '<div class="cell">';
	if($isReadonlyUser == true) {
		$table .= $desc;
	}
	else {
		$table .= getInputField($file_id, null, '_sf_file_description_' . $file_id, $desc, $inputArray);
		$table .= getInputField($file_id, null, '_sf_file_description_origin_' . $file_id, $desc, $inputArray, true);
	}

	$table .= '</div>';
}

function addCustomFieldField(&$table, $file_id, $n, $val, &$inputArray, $isReadonlyUser): void
{
	$table .= '<div class="cell">';
	if($isReadonlyUser == true) {
		$table .= $val;
	}
	else {	
		$table .= getInputField($file_id, $n, '_sf_file_cf_' . $file_id . '_' . $n, $val, $inputArray);
		$table .= getInputField($file_id, $n, '_sf_file_cf_origin_' . $file_id . '_' . $n, $val, $inputArray, true);
	}

	$table .= '</div>';
}

function addTagsField(&$table, $file_id, $tagValue, &$inputArray, $isReadonlyUser): void
{
	$table .= '<div class="cell">';
	if($isReadonlyUser == true) {
		$table .= $tagValue;
	}
	else {		
		$table .= getInputField($file_id, null, '_sf_file_tags_' . $file_id, $tagValue, $inputArray);
		$table .= getInputField($file_id, null, '_sf_file_tags_origin_' . $file_id, $tagValue, $inputArray, true);
	}

	$table .= '</div>';
}

function addCategoryField(&$table, $file_id, &$category, $catValue, &$checkboxArray, $isReadonlyUser): void
{
	// error_log("addCategoryField " . $file_id . ": " . print_r($category, true) . ", catvalue " . $catValue . ", inputArr " . print_r($inputArray, true));
	$table .= '<div class="cell">';
	$table .= getCheckboxField($file_id, $category->name, '_sf_file_cat_' . $file_id . '_' . $category->term_id, $catValue, $checkboxArray, $isReadonlyUser);
	$table .= getCheckboxField($file_id, $category->name, '_sf_file_cat_origin_' . $file_id . '_' . $category->term_id, $catValue, $checkboxArray, $isReadonlyUser, true);

	$table .= '</div>';
}
?>