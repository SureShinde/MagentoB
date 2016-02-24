<?php

class Bilna_Cod_Model_System_Config_Backend_Shipping_Cod extends Mage_Core_Model_Config_Data
{
    public function _afterSave()
    {
    	$object = $this;
        $csvFile = $_FILES['groups']['tmp_name']['cod']['fields']['import']['value'];
        $csvName = $_FILES['groups']['name']['cod']['fields']['import']['value'];
        $session = Mage::getSingleton('adminhtml/session');
        $dataStored = false;
		
        if (!empty ($csvFile)) {
            $csv = trim(file_get_contents($csvFile));
            
            if (!empty ($csv)) {
                $exceptions = array ();
                $csvLines = explode("\n", $csv);
                $csvLine = array_shift($csvLines);
                $csvLine = $this->_getCsvValues($csvLine);
                
                if (count($csvLine) < 4) {
                    $exceptions[0] = Mage::helper('shipping')->__('Invalid Payment Base Shipping File Format');
                }

                $idArr = array();
                //REMOVE THE RECORD
                $resource = Mage::getSingleton('core/resource');
                $writeConnection = $resource->getConnection('core_write');

                $query = "DELETE FROM payment_base_shipping";
                 
                $writeConnection->query($query);
                
                foreach ($csvLines as $k => $csvLine) {
                    $csvLine = $this->_getCsvValues($csvLine);
                    
                    if (count($csvLine) !== 4) {
                        $exceptions[0] = Mage::helper('shipping')->__('Invalid Payment Base Shipping File Format');
                    } else {
                    	//CHECK THE ROW FORMAT & DUPLICATION
                    	$csvLine[0] = (int)$csvLine[0];
                    	$csvLine[2] = ($csvLine[2] == "cod")?"cod":"shipping";
                    	if(is_null($csvLine[0])){
                    		$exceptions[0] = Mage::helper('shipping')->__('Invalid Payment Base Shipping File Format');
                    	}
                    	
                    	if (!empty ($exceptions)) {
                    		throw new Exception( "\n" . implode("\n", $exceptions) );
                    	}
                    	
                    	if(!in_array($csvLine[0],$idArr)){
                    		$idArr[] = $csvLine[0];
                    		
                    		//SAVE THE ITEM
							$query = "insert into payment_base_shipping (id, delivery, flow, exclude_payment)
												values (:id, :delivery, :flow, :exclude_payment)";
							$binds = array(
									'id'				=> $csvLine[0],
									'delivery'			=> $csvLine[1],
									'flow'				=> $csvLine[2],
									'exclude_payment'	=> $csvLine[3]
							);
						
							$writeConnection->query($query, $binds);
                    	}
                    }
                }
            }
        }
		
		if (!empty ($exceptions)) {
			throw new Exception( "\n" . implode("\n", $exceptions) );
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
