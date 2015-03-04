<?php

namespace Wpierre\Scafo\ScafoBundle\OCRService;

//use tesseract_ocr_for_php\tesseract_ocr;
//require_once (__DIR__.'/../../../../../vendor/tesseract-ocr-for-php/tesseract_ocr/tesseract_ocr.php');
//require_once ('TesseractOCR.php');

class OCRService {
	
	public function OCRizePage($filename){
		$text = TesseractOCR::recognize($filename);
		return $text;
	}
}

?>