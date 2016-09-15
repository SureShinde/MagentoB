<?php
class Bilna_Common_Model_Partnershiptype extends Mage_Core_Model_Abstract
{
    /**
     * Function to get all partnership type data
     * @return array
     */
    public function getActivePartnershipType()
    {
        $attributeId = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', 'partnership_type');
        $attribute = Mage::getModel('catalog/resource_eav_attribute')->load($attributeId);
        $attributeOptions = $attribute->getSource()->getAllOptions();
        $partnershipData = array();
        foreach ($attributeOptions as $key=>$value) {
            if(is_array($value)) {
                if(!empty($value['label'])) {
                    $partnershipData[$value['value']] = $value['label'];
                }
            }
        }
        return $partnershipData;
    }
    public function toOptionArray()
    {
        $partnership = $this->getActivePartnershipType();
        $listPartnership = array();
        foreach ($partnership as $partnershipCode => $partnershipText) {
            $listPartnership[$partnershipCode] = array(
                'label' => $partnershipText,
                'value' => $partnershipCode
            );
        }
        return $listPartnership;
    }
}
