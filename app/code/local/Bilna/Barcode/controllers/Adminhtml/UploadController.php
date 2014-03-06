<?php
class Bilna_Barcode_Adminhtml_UploadController extends Mage_Adminhtml_Controller_Action
{
	public function indexAction()
    {
		$this->loadLayout();
		$this->_title($this->__("Barcode Upload"));
		$this->renderLayout();
    }

    public function saveAction(){
    	try{
        $post = $this->getRequest()->getPost();
    	
    	if(isset($post)){
    		$csv = file_get_contents($_FILES['file']);
    		if ( isset($_FILES["file"])) {
    			move_uploaded_file($_FILES["file"]["tmp_name"], "files/barcode/barcode.csv");
    		}
    		
    		//Create a PDF
    		$pdf				= new Zend_Pdf();
    		//Setting
    		$csvFile			= "files/barcode/barcode.csv";
    		$minQty				= 0;
    		// 		$backorderMinQty	= 0;
    		
    		$row = 0;
    		if (!empty ($csvFile)) {
    			$csv = trim(file_get_contents($csvFile));
    			if (!empty ($csv)) {
    				$exceptions = array ();
    				$csvLines = explode("\r", $csv);
    		
    				foreach ($csvLines as $k => $csvLine) {
    					if($k>0){
    						$data = $this->_getCsvValues($csvLine);
    		
    						//check word max 25 char
    						$sku		= str_split(strtoupper($data[0]), $split_length = 25);
    						$barcode	= $data[1];
    						//check word max 25 char
    						$name		= str_split(strtoupper($data[2]), $split_length = 25);
    						$qty		= (int)$data[3];
    						//                 		$backorders	= (int)$data[4];
    		
    						//qty conditions
    						if($qty < $minQty) $qty = $minQty;
    						//                 		if(($backorders == 1) && $qty < $backorderMinQty) $qty = $backorderMinQty;
    		
    						//loop item qty
    						for ($i = 0; $i < $qty; $i++) {
    							if(($row % 3) == 0){
    								$page = new Zend_Pdf_Page("306:51:");
    		
    								$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
    								$page->setFont($font, 5);
    							}
    		
    							//Draw sku in pdf
    							$page->drawText($sku[0], ((($row%3)*101)+8), 10);
    							//Draw name in pdf
    							$page->drawText($name[0], ((($row%3)*101)+8), ($page->getHeight()-9));
    							$page->drawText($name[1], ((($row%3)*101)+8), ($page->getHeight()-15));
    							 
    							// Only the text to draw is required
    							$barcodeOptions = array('text' => $barcode,
    									'withQuietZones' => true,
    									'withChecksum' => true,
    									'withChecksumInText' => true,
    									'stretchText' => false);
    							 
    							// No required options
    							$rendererOptions = array('withChecksum' => true,
    									'withQuietZones' => true,
    									'withChecksumInText' => true,
    									'stretchText' => false);
    							 
    							// Draw the barcode in a new image,
    							$imageResource = Zend_Barcode::draw(
    									'Code128', 'image', $barcodeOptions, $rendererOptions
    							);
    							imagejpeg($imageResource, 'files/barcode/barcode.jpg', 100);
    							 
    							// Free up memory
    							imagedestroy($imageResource);
    							 
    							//Draw image in pdf
    							$image = Zend_Pdf_Image::imageWithPath('files/barcode/barcode.jpg');
    							$page->drawImage($image, ((($row%3)*101)+5), 15, ((($row%3)*101)+90), 35);
    		
    							//delete temp image
    							unlink('files/barcode/barcode.jpg');
    		
    							if(($row % 3) == 0){
    								$pdf->pages[] = $page;
    							}
    								
    							$row++;
    						}
    					}
    				}
    			}
    		}
    		$pdf->save('files/barcode/barcode.pdf');
    		
    		unlink($csvFile);
    		
    		header("Content-disposition: attachment; filename=barcode.pdf");
    		header("Content-type: application/pdf");
    		readfile("files/barcode/barcode.pdf");
    	}
    	
    	}catch(Exception $e){
    		Zend_Debug::dump($e);die;
    	}
	}

    private function _getCsvValues($string, $separator = ",") {
        $elements = explode($separator, trim($string));
        
        for ($i = 0; $i < count($elements); $i++) {
            $nquotes = substr_count($elements[$i], '"');
            
            if ($nquotes %2 == 1) {
                for ($j = $i+1; $j < count($elements); $j++) {
                    if (substr_count($elements[$j], '"') > 0) {
                        // Put the quoted string's pieces back together again
                        array_splice($elements, $i, $j-$i+1, implode($separator, array_slice($elements, $i, $j-$i+1)));
                        break;
                    }
                }
            }
            
            if ($nquotes > 0) {
                // Remove first and last quotes, then merge pairs of quotes
                $qstr =& $elements[$i];
                $qstr = substr_replace($qstr, '', strpos($qstr, '"'), 1);
                $qstr = substr_replace($qstr, '', strrpos($qstr, '"'), 1);
                $qstr = str_replace('""', '"', $qstr);
            }
            
            $elements[$i] = trim($elements[$i]);
        }
        
        return $elements;
    }
}