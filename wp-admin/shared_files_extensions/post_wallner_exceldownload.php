<?php

function downloadExcelData($data, $fileName, $nurKategorienAnzeigen)
{
	header("Content-Type: text/csv");
	header("Content-Disposition: attachment; filename=\"$fileName\"");
	
	if ($data["headrow"] && $data["keys"]) {
		$outerArrayKeys = array_keys(($data));
		$dataArrayKeys = $data["keys"];
		$headRow = $data["headrow"];
		$headRowKat = $data["headrowKat"];

		if($nurKategorienAnzeigen == true) {
			addHeadRow($headRowKat);
		} else {
			addHeadRow($headRow);
		}

		asort($outerArrayKeys);
		foreach ($outerArrayKeys as $outerKey) {
			if ($outerKey != "keys" && $outerKey != "headrow" && $outerKey != "headrowKat" && $outerKey != "args" && $outerKey != "total") {
				$dataRowArray = $data[$outerKey];
				addDataRow($dataRowArray, $dataArrayKeys, $nurKategorienAnzeigen);
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

function addDataRow($dataRowArray, $dataArrayKeys, $nurKategorienAnzeigen)
{
	foreach ($dataArrayKeys as $dataKey) {
		$element = null;
		if (is_array($dataKey)) {
			$firstKey = array_key_first($dataKey);
			$secondKey = $dataKey[$firstKey];
			
			if ($firstKey == "custom_field" && $nurKategorienAnzeigen == true) {
					continue;
			}

			$element = $dataRowArray[$firstKey][$secondKey];
		} else {
			if ($nurKategorienAnzeigen == true && ($dataKey == "description" || $dataKey == "tags")) {
				continue;
			}

			$element = $dataRowArray[$dataKey];
		}

		// encoding ensures german "Umlaute"
		echo mb_convert_encoding($element, "Windows-1252") . ";";
	}

	echo "\n";
}

?>