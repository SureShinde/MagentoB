<?php
class RocketWeb_Netsuite_Model_Adminhtml_System_Config_Source_Importableentities {
    public function toOptionArray()
    {
    	$options = array();
    	$entities = Mage::getConfig()->getNode('rocketweb_netsuite/import_entities')->asArray();
    	foreach ($entities as $path => $name) {
    		$options[] = array('value' => $path, 'label' => $name);
    	}

    	return $options;
    }
}