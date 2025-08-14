<?php

function downloadExcelData($data, $fileName, $nurKategorienAnzeigen, $onlyModifySingleField)
{
	header("Content-Type: text/csv");
	header("Content-Disposition: attachment; filename=\"$fileName\"");
	
	if ($data["headrow"] && $data["keys"]) {
		$outerArrayKeys = array_keys(($data));
		$dataArrayKeys = $data["keys"];
		$headRow = $data["headrow"];
		$headRowKat = $data["headrowKat"];
		$headRowSingleFields = $data["headRowSingleFields"];

		if($nurKategorienAnzeigen == true) {
			addHeadRow($headRowKat);
		} else if($onlyModifySingleField != null && $onlyModifySingleField != "notselected") {
			addHeadRow($headRowSingleFields);
		} else {
			addHeadRow($headRow);
		}

		asort($outerArrayKeys);
		foreach ($outerArrayKeys as $outerKey) {
			if ($outerKey != "keys" && $outerKey != "headrow" && 
				$outerKey != "headrowKat"  && $outerKey != "headRowSingleFields" && 
				$outerKey != "args" && $outerKey != "total") {
				$dataRowArray = $data[$outerKey];
				addDataRow($dataRowArray, $dataArrayKeys, $nurKategorienAnzeigen, $onlyModifySingleField);
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

function addDataRow($dataRowArray, $dataArrayKeys, $nurKategorienAnzeigen, $onlyModifySingleField)
{
	foreach ($dataArrayKeys as $dataKey) {
		$element = null;
		if (is_array($dataKey)) {
			$firstKey = array_key_first($dataKey);
			$secondKey = $dataKey[$firstKey];
			
			if ($firstKey == "custom_field" && $nurKategorienAnzeigen == true) {
					continue;
			}

			if(($onlyModifySingleField == null || $onlyModifySingleField == "notselected") ||
				($onlyModifySingleField != null && $firstKey != "category" &&
				str_starts_with($onlyModifySingleField, "file_upload_custom_field_") &&
				str_ends_with($onlyModifySingleField, $secondKey))) {
				$element = $dataRowArray[$firstKey][$secondKey];
			} else {
				continue;
			}
		} else {
			if ($nurKategorienAnzeigen == true && ($dataKey == "description" || $dataKey == "tags")) {
				continue;
			}

			if(($onlyModifySingleField == null || $onlyModifySingleField == "notselected") || 
				$dataKey == "file_id" || $dataKey == "title" ||
				($onlyModifySingleField != null && $dataKey == "description" &&
				str_starts_with($onlyModifySingleField, "description") ||
				($onlyModifySingleField != null && $dataKey == "tags" &&
				str_starts_with($onlyModifySingleField, "tags")))) {	
				$element = $dataRowArray[$dataKey];
			} else {
				continue;
			}
		}

		// encoding ensures german "Umlaute"
		echo mb_convert_encoding($element, "Windows-1252") . ";";
	}

	echo "\n";
}

?>