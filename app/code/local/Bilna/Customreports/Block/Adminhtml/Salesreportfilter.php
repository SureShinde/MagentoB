<?php

/**
 * Adminhtml coupons report filter
 *
 * @category   Bilna
 * @package    Bilna_Customreports
 * @author     Bilna Development Team <development@bilna.com>
 */

class Bilna_Customreports_Block_Adminhtml_Salesreportfilter extends Mage_Adminhtml_Block_Widget_Form{

	public function __construct()
	{
		parent::__construct();
	}
	
	protected function _prepareForm()
    {
    	$actionUrl = $this->getUrl('*/*/index');
        $form = new Varien_Data_Form(
            array('id' => 'filter_form', 'action' => $actionUrl, 'method' => 'get')
        );
        $htmlIdPrefix = 'coupons_report_';
        $form->setHtmlIdPrefix($htmlIdPrefix);
        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('customreports')->__('Filter')));

        $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);

        $fieldset->addField('report_type', 'select', array(
            'name'      => 'report_type',
            'options'   => array(
                'order_created_at'  =>   Mage::helper('customreports')->__('Order Created Date'),
                'order_updated_at'  =>   Mage::helper('customreports')->__('Order Updated Date'),
                'invoice_created_at'  =>   Mage::helper('customreports')->__('Invoice Created Date')
            ),
            'label'     => Mage::helper('customreports')->__('Match Period To'),
        ));


    	$fieldset->addField('from', 'date', array(
            'name'      => 'from',
            'format'    => $dateFormatIso,
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'label'     => Mage::helper('customreports')->__('From'),
            'title'     => Mage::helper('customreports')->__('From'),
            'required'  => true
        ));

        $fieldset->addField('to', 'date', array(
            'name'      => 'to',
            'format'    => $dateFormatIso,
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'label'     => Mage::helper('customreports')->__('To'),
            'title'     => Mage::helper('customreports')->__('To'),
            'required'  => true
        ));

        $statuses = Mage::getModel('sales/order_config')->getStatuses();
        $values = array();
        foreach ($statuses as $code => $label) {
            if (false === strpos($code, 'pending')) {
                $values[] = array(
                    'label' => Mage::helper('customreports')->__($label),
                    'value' => $code
                );
            }
        }

        $fieldset->addField('show_order_statuses', 'select', array(
            'name'      => 'show_order_statuses',
            'label'     => Mage::helper('customreports')->__('Order Status'),
            'options'   => array(
                    '0' => Mage::helper('customreports')->__('Any'),
                    '1' => Mage::helper('customreports')->__('Specified'),
                ),
            'note'      => Mage::helper('customreports')->__('Applies to Any of the Specified Order Statuses'),
        ), 'to');

        $fieldset->addField('order_statuses', 'multiselect', array(
            'name'      => 'order_statuses',
            'values'    => $values,
            'display'   => 'none'
        ), 'show_order_statuses');

        // define field dependencies
        if ($this->getFieldVisibility('show_order_statuses') && $this->getFieldVisibility('order_statuses')) {
            $this->setChild('form_after', $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
                ->addFieldMap("{$htmlIdPrefix}show_order_statuses", 'show_order_statuses')
                ->addFieldMap("{$htmlIdPrefix}order_statuses", 'order_statuses')
                ->addFieldDependence('order_statuses', 'show_order_statuses', '1')
            );
        }

        $fieldset->addField('include_refunded_items', 'select', array(
            'name'      => 'include_refunded_items',
            'options'   => array(
                'true'  =>   Mage::helper('customreports')->__('Include'),
                'false'  =>   Mage::helper('customreports')->__('Exclude'),
            ),
            'label'     => Mage::helper('customreports')->__('Include refunded items'),
        ));

    	$form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Get all unique Rule Names from Salesrule table
     *
     * @return array
     */
    public function getUniqRulesNamesList()
    {
        $resource       = Mage::getSingleton('core/resource');
        $adapter        = $resource->getConnection('core_read');
        $tableName      = $resource->getTableName('salesrule');
        $select = $adapter->select()
            ->from(
                $tableName,
                new Zend_Db_Expr('DISTINCT name, rule_id')
            )
            ->where('name IS NOT NULL')
            ->where('name <> ""')
            ->order('name ASC');

        $rulesNames = $adapter->fetchAll($select);

        $result = array();

        foreach ($rulesNames as $row) {
            $result[$row['rule_id']] = $row['name'];
        }

        return $result;
    }

    /**
     * Initialize form fileds values
     * Method will be called after prepareForm and can be used for field values initialization
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _initFormValues()
    {
        $filter = $this->getRequest()->getParam('filter');
        $data   = Mage::helper('adminhtml')->prepareFilterString($filter);

        foreach ($data as $key => $value) {
            if (is_array($value) && isset($value[0])) {
                $data[$key] = explode(',', $value[0]);
            }
        }
        $this->getForm()->addValues($data);
   
        return parent::_initFormValues();
    }
}