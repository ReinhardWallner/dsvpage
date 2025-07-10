<?php

function downloadExcelData($data, $fileName)
{
	header("Content-Type: text/csv");
	header("Content-Disposition: attachment; filename=\"$fileName\"");

	if ($data["headrow"] && $data["keys"]) {
		$outerArrayKeys = array_keys(($data));
		$dataArrayKeys = $data["keys"];
		$headRow = $data["headrow"];

		addHeadRow($headRow);
		asort($outerArrayKeys);
		foreach ($outerArrayKeys as $outerKey) {
			if ($outerKey != "keys" && $outerKey != "headrow" && $outerKey != "headrowKat" && $outerKey != "args" && $outerKey != "total") {
				$dataRowArray = $data[$outerKey];
				addDataRow($dataRowArray, $dataArrayKeys);
			}
		}
	}
}

function addHeadRow($headRow)
{
	foreach ($headRow as $element) {
		echo mb_convert_encoding($element, "Windows-1252") . ";";
	}

	echo "\n";
}

function addDataRow($dataRowArray, $dataArrayKeys)
{
	foreach ($dataArrayKeys as $dataKey) {
		$element = null;
		if (is_array($dataKey)) {
			$firstKey = array_key_first($dataKey);
			$secondKey = $dataKey[$firstKey];
			$element = $dataRowArray[$firstKey][$secondKey];
		} else {
			$element = $dataRowArray[$dataKey];
		}

		// encoding ensures german "Umlaute"
		echo mb_convert_encoding($element, "Windows-1252") . ";";
	}

	echo "\n";
}

?>