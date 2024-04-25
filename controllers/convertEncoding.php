<?php 

	class convertEncoding
	{
		function convertEncode($value) {
				$detectedEncoding = mb_detect_encoding($value, "UTF-8, ISO-8859-1, Windows-1252", true);
				
				if ($detectedEncoding && $detectedEncoding === "UTF-8") {
					return iconv("UTF-8", "ISO-8859-1", $value);
				}

				return $value;
			}

		
			
	}
?>