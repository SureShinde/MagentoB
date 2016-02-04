<?php



class AW_Affiliate_Block_Adminhtml_Category_Edit_Tab_Categoriesgrid
    extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Categories
{
    private $_afpVO = null;

    protected function _beforeToHtml()
    {
        $this->setTemplate('catalog/product/edit/categories.phtml');
        return $this;
    }

    /*public function getProduct()
    {
echo 'havcahusvasvbkvacs';        
        if (is_null($this->_afpVO)) {
            $this->_afpVO = new Varien_Object();
        }
        if (!$this->_afpVO->getCategoryIds()) {
            
            $_data = Mage::getSingleton('adminhtml/session')->getData(AW_Affiliate_Helper_Data::FORM_DATA_KEY);
            if (!is_object($_data)) {
                $_data = new Varien_Object($_data);
            }
            if ($_data->getCategoryIds()) {
                $this->_afpVO->setCategoryIds(@explode(',', $_data->getCategoryIds()));
            } else {
                $this->_afpVO->setCategoryIds(array());
                $_automationData = $_data->getAutomationData();
                if ($_automationData && isset($_automationData['categories'])) {
                    $this->_afpVO->setCategoryIds(@explode(',', $_automationData['categories']));
                }
            }
echo 'yyyyyyyyyyyy';            
        }
        return $this->_afpVO;
    }*/
    public function isReadonly()
        {
            return false;
        }

    protected function getCategoryIds()
    {
        $categories_ids_array = array();
         
        /*$collection = Mage::getModel('catalog/category')->getCollection()
            ->setStoreId(1)
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('is_active'); 
//$collection->printLogQuery(true); 
        $values=array();

        foreach ($collection as $row){
           // $values[] = array(
           //      'label' => Mage::helper('reports')->__($row['name']),
           //      'value' => $row['entity_id']
           //  );
            $categories_ids_array[]=$row['entity_id'];
        }*/
//print_r($categories_ids_array);        
        return $categories_ids_array;
    }

    public function getIdsString()
    {
        return implode(',', $this->getCategoryIds());
    }
}
