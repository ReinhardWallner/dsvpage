<?php

function modifiedValues($value)
{
	return $value["origin"] != $value["value"];
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