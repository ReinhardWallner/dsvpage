<?php

function downloadExcelData($data, $fileName)
{
	// $fileName = "codexworld_export_data-" . date('Ymd') . ".csv";

	header("Content-Type: text/csv");
	header("Content-Disposition: attachment; filename=\"$fileName\"");

	if ($data["headrow"] && $data["keys"]) {
		// error_log("downloadExcelData " . print_r($data, true));
		$outerArrayKeys = array_keys(($data));
		$dataArrayKeys = $data["keys"];
		$headRow = $data["headrow"];

		addHeadRow($headRow);
		asort($outerArrayKeys);
		// error_log("outerArrayKeys 2 " . print_r($outerArrayKeys, true));
		foreach ($outerArrayKeys as $outerKey) {
			if ($outerKey != "keys" && $outerKey != "headrow") {
				$dataRowArray = $data[$outerKey];
				addDataRow($dataRowArray, $dataArrayKeys);
			}
		}
	}
}

function addHeadRow($headRow)
{
	foreach ($headRow as $element) {
		// error_log("head element " . print_r($element, true));
		echo mb_convert_encoding($element, "Windows-1252") . ";";
	}

	echo "\n";
}

function addDataRow($dataRowArray, $dataArrayKeys)
{
	// error_log("ROW ELEMENT " . print_r($dataRowArray, true));
	foreach ($dataArrayKeys as $dataKey) {
		// error_log("    element key " . print_r($dataKey, true));
		$element = null;
		if (is_array($dataKey)) {
			$firstKey = array_key_first($dataKey);
			$secondKey = $dataKey[$firstKey];
			$element = $dataRowArray[$firstKey][$secondKey];
		} else {
			$element = $dataRowArray[$dataKey];
		}

		echo mb_convert_encoding($element, "Windows-1252") . ";";
		// error_log("    element " . print_r($element, true));
	}

	echo "\n";
}

function filterData(&$str)
{
	$str = preg_replace("/\t/", "\\t", $str);
	$str = preg_replace("/\r?\n/", "\\n", $str);
	if (strstr($str, '"'))
		$str = '"' . str_replace('"', '""', $str) . '"';
}



?>