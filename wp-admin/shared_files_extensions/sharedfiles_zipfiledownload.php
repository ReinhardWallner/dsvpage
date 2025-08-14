<?php

function createzipfile($data, $path, $fileName)
{
	$outerArrayKeys = array_keys(($data));

	// Checking ZIP extension is available
	if (extension_loaded('zip')) {
		$zip = new ZipArchive(); // Load zip library

		$zip_name = $fileName;

		if ($zip->open($zip_name, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
			// Opening zip file to load files
			echo "* Sorry ZIP creation failed at this time";
		}

		foreach ($outerArrayKeys as $outerKey) {
			if ($outerKey != "keys" && $outerKey != "headrow" && 
				$outerKey != "headrowKat" && $outerKey != "headRowSingleFields" && 
				$outerKey != "args" && $outerKey != "total") {
				$dataRowArray = $data[$outerKey];
				$fullPath = realpath($path . DIRECTORY_SEPARATOR . $dataRowArray["filename"]);
				if ($fullPath && file_exists($fullPath)) {
					$zip->addFile($fullPath, basename($dataRowArray["filename"]));
				} else {
					error_log("Datei nicht gefunden oder ungültig: $fullPath");
				}
			}
		}

		$zip->close();

		if (file_exists($zip_name)) {
			// push to download the zip
			header('Content-type: application/zip');
			header('Content-Disposition: attachment; filename="' . basename($zip_name) . '"');
			// header('Content-Length: ' . filesize($zip_name));

			flush();
			readfile($zip_name);
			// remove zip file is exists in temp path
			unlink($zip_name);
			exit;
		}

	} else {
		echo "* You dont have ZIP extension";
	}
}

?>