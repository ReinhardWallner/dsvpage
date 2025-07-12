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