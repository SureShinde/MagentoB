<?php
/**
 * Adminhtml Wrapping Gift Event "Wrap Types" field
 *
 * @category   Bilna
 * @package    Bilna_Wrappinggiftevent
 * @author     Bilna Development Team <development@bilna.com>
 */
class Bilna_Wrappinggiftevent_Block_Adminhtml_Form_Field_Wrapping extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    /**
     * @var Mage_CatalogInventory_Block_Adminhtml_Form_Field_Customergroup
     */
    protected $_groupRenderer;

    /**
     * Retrieve group column renderer
     *
     * @return Mage_CatalogInventory_Block_Adminhtml_Form_Field_Customergroup
     */
    protected function _getGroupRenderer()
    {
        if (!$this->_groupRenderer) {
            $this->_groupRenderer = $this->getLayout()->createBlock(
                'wrappinggiftevent/adminhtml_form_field_yesno', '',
                array('is_render_to_js_template' => true)
            );
            $this->_groupRenderer->setClass('enable_yesno_select');
            $this->_groupRenderer->setExtraParams('style="width:120px"');
        }
        return $this->_groupRenderer;
    }

    /**
     * Prepare to render
     */
    protected function _prepareToRender()
    {
        $this->addColumn('wrapping_name', array(
            'label' => Mage::helper('wrappinggiftevent')->__('Wrap Name'),
            'style' => 'width:200px',
        ));
        $this->addColumn('is_enable', array(
            'label' => Mage::helper('wrappinggiftevent')->__('Enable'),
            'renderer' => $this->_getGroupRenderer()
        ));
        $this->addColumn('wrapping_desc', array(
            'label' => Mage::helper('wrappinggiftevent')->__('Description'),
            'style' => 'width:200px',
        ));
        $this->addColumn('wrapping_price', array(
            'label' => Mage::helper('wrappinggiftevent')->__('Price'),
            'type'  => 'image',
            'style' => 'width:100px',
        ));
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('wrappinggiftevent')->__('Add Wrap Type');
    }

    /**
     * Prepare existing row data object
     *
     * @param Varien_Object
     */
    protected function _prepareArrayRow(Varien_Object $row)
    {
        $row->setData(
            'option_extra_attr_' . $this->_getGroupRenderer()->calcOptionHash($row->getData('customer_group_id')),
            'selected="selected"'
        );
    }
}
