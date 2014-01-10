<?php

/**
 * Adminhtml coupons report filter
 *
 * @category   Bilna
 * @package    Bilna_Couponsreport
 * @author     Bilna Development Team <development@bilna.com>
 */

class Bilna_Customreports_Block_Adminhtml_Couponsreportfilter extends Mage_Adminhtml_Block_Widget_Form{

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
        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('reports')->__('Filter')));

        $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);

        $fieldset->addField('report_type', 'select', array(
            'name'      => 'report_type',
            'options'   => array(
                'main_table.created_at'  =>   Mage::helper('reports')->__('Order Created Date'),
                'main_table.updated_at'  =>   Mage::helper('reports')->__('Order Updated Date'),
                'order_status_history.created_at'  =>   Mage::helper('reports')->__('Invoice Created Date')
            ),
            'label'     => Mage::helper('reports')->__('Match Period To'),
        ));


    	$fieldset->addField('from', 'date', array(
            'name'      => 'from',
            'format'    => $dateFormatIso,
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'label'     => Mage::helper('reports')->__('From'),
            'title'     => Mage::helper('reports')->__('From'),
            'required'  => true
        ));

        $fieldset->addField('to', 'date', array(
            'name'      => 'to',
            'format'    => $dateFormatIso,
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'label'     => Mage::helper('reports')->__('To'),
            'title'     => Mage::helper('reports')->__('To'),
            'required'  => true
        ));

        $statuses = Mage::getModel('sales/order_config')->getStatuses();
        $values = array();
        foreach ($statuses as $code => $label) {
            if (false === strpos($code, 'pending')) {
                $values[] = array(
                    'label' => Mage::helper('reports')->__($label),
                    'value' => $code
                );
            }
        }

        $fieldset->addField('show_order_statuses', 'select', array(
            'name'      => 'show_order_statuses',
            'label'     => Mage::helper('reports')->__('Order Status'),
            'options'   => array(
                    '0' => Mage::helper('reports')->__('Any'),
                    '1' => Mage::helper('reports')->__('Specified'),
                ),
            'note'      => Mage::helper('reports')->__('Applies to Any of the Specified Order Statuses'),
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

        $fieldset->addField('price_rule_type', 'select', array(
            'name'    => 'price_rule_type',
            'options' => array(
                Mage::helper('reports')->__('Any'),
                Mage::helper('reports')->__('Specified')
            ),
            'label'   => Mage::helper('reports')->__('Shopping Cart Price Rule'),
        ));

        $rulesList = $this->getUniqRulesNamesList();

        $rulesListOptions = array();

        foreach ($rulesList as $key => $ruleName) {
            $rulesListOptions[] = array(
                'label' => $ruleName,
                'value' => $key,
                'title' => $ruleName
            );
        }

        $fieldset->addField('rules_list', 'multiselect', array(
            'name'      => 'rules_list',
            'values'    => $rulesListOptions,
            'display'   => 'none'
        ), 'price_rule_type');

        $this->setChild('form_after', $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
            ->addFieldMap($htmlIdPrefix . 'price_rule_type', 'price_rule_type')
            ->addFieldMap($htmlIdPrefix . 'rules_list', 'rules_list')
            ->addFieldDependence('rules_list', 'price_rule_type', '1')
        );

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