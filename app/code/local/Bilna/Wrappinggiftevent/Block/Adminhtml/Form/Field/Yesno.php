<?php
/**
 * HTML select element block with customer groups options
 *
 * @category   Mage
 * @package    Mage_CatalogInventory
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Bilna_Wrappinggiftevent_Block_Adminhtml_Form_Field_Yesno extends Mage_Core_Block_Html_Select
{
    /**
     * Yes No cache
     *
     * @var array
     */
    private $_yesno;

    /**
     * Retrieve allowed customer groups
     *
     * @param int $groupId  return name by customer group id
     * @return array|string
     */
    protected function _getYesNo()
    {
        if (is_null($this->_yesno)) {
            //$this->_yesno = array();
            $this->_yesno = Mage::getModel('adminhtml/system_config_source_yesno')->toArray();       
        }
        return $this->_yesno;
    }

    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            foreach ($this->_getYesNo() as $yesNoId => $yesNoLabel) {
                $this->addOption($yesNoId, addslashes($yesNoLabel));
            }
        }
        return parent::_toHtml();
    }
}
