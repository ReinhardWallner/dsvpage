<?php

function modifiedValues($value)
{
	// for Categories (Ceckboxes):
	// if (original == "on") && value NOT Set --> modified
	// if (original == "on") && value on --> NOT modified
	// if(original leer und value NOT SET --> NOT modified)
	// if(original leer und value "on" --> modified)

	return $value["origin"] != $value["value"];
}

function modifiedValuesCheckbox($value)
{
	// for Categories (Ceckboxes):
	// if (original == "on") && value NOT Set --> modified
	// if (original == "on") && value on --> NOT modified
	// if(original leer und value NOT SET --> NOT modified)
	// if(original leer und value "on" --> modified)
	if ($value["origin"] == "on") {
		if ($value && $value["value"] == "on")
			return false;
		return true;
	} else {
		if ($value && $value["value"] == "on")
			return true;

		return false;
	}
}

function getModifiedValuesArray($values)
{
	foreach ($values as $key => $value) {
	}
}

function modifiedValuesArray($values)
{
	$modifiedValues = [];
	$lastfield = null;
	$modifiedValuesExist = false;
	foreach ($values as $file_id => $value) {
		// error_log("modifiedValuesArray FILE ID " . $file_id);
		foreach ($value as $key => $field_value) {
			// error_log("modifiedValuesArray START " . $key . ", " . print_r($field_value, true));
			if ($key == "field") {
				$lastfield = $field_value;
				// error_log("modifiedValuesArray lastArray " . $key . " lf=" . $lastfield . ", " . print_r($lastArr, true));
			} else {
				// error_log("modifiedValuesArray " . $key . ", " . print_r($field_value, true));
				$modified = false;
				if (str_starts_with($$lastfield, "_sf_file_cat"))
					$modified = modifiedValuesCheckbox($field_value);
				else
					$modified = modifiedValues($field_value);

				if ($modified == true) {
					$lastArr[$key] = $field_value;
					$modifiedValuesExist = true;
					// error_log("modifiedValuesArray lastArray value" . $key . ", " . print_r($lastArr, true));
				}
			}
		}

		//  error_log("modifiedValuesArray INSERT??? modifiedValuesExist lastfield " . $modifiedValuesExist . ", " . print_r($lastfield, true));
		if ($modifiedValuesExist == true && $lastfield) {
			// array_push($modifiedValues, $lastArr);
			$key = strval($file_id);
			$modifiedValues[$key] = $lastArr;
			// error_log("modifiedValuesArray INSERTED " . $key . " -> " . print_r($lastArr, true) . " | " . print_r($modifiedValues, true));
			// error_log("modifiedValuesArray INSERTED modifiedValues" . $lastfield . ", " . print_r($modifiedValues, true));
		}

		$lastArr = [];
		$modifiedValuesExist = false;
	}

	error_log("modifiedValuesArray RETURN " . print_r($modifiedValues, true));
	return $modifiedValues;
}

function arrayFindObjectElement($objArray, $needleField, $needleValue)
{
	foreach ($objArray as $key => $value) {
		if ($value->$needleField === $needleValue)
			return $value;
	}

	return null;
}

function addSingleIdValuesToArray($vars, $key, &$array)
{
	$el = $vars[$key];
	$exp = explode("_", $key);
	$file_id = $exp[count($exp) - 1];
	$array[$file_id]["field"] = $key;
	if (str_contains($key, 'origin')) {
		$array[$file_id]["origin"] = $el;
	} else {
		$array[$file_id]["value"] = $el;
	}

	return $file_id;
}

function addDoubleIdValuesToArray($vars, $key, &$array)
{
	$el = $vars[$key];
	$exp = explode("_", $key);
	$file_id = $exp[count($exp) - 2];
	$array[$file_id]["field"] = $key;
	$cf_id = $exp[count($exp) - 1];
	if (str_contains($key, 'origin')) {
		$array[$file_id][$cf_id]["origin"] = $el;
	} else {
		$array[$file_id][$cf_id]["value"] = $el;
	}

	return $file_id;
}

?>