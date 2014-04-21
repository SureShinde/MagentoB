<?php
/**
 * Rocket Web Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is available through the world-wide-web at this URL:
 * http://www.rocketweb.com/RW-LICENSE.txt
 *
 * @category   RocketWeb
 * @package    RocketWeb_Netsuite
 * @copyright  Copyright (c) 2013 RocketWeb (http://www.rocketweb.com)
 * @author     Rocket Web Inc.
 * @license    http://www.rocketweb.com/RW-LICENSE.txt
 */
class RocketWeb_Netsuite_Model_Adminhtml_Observer {
    public function addViewInNetsuiteButtons($observer) {

        if(!Mage::helper('rocketweb_netsuite')->isEnabled()) {
            return false;
        }

        $block = $observer->getEvent()->getBlock();

        $netsuiteLinksConfiguration = Mage::getConfig()->getNode('rocketweb_netsuite/netsuite_links')->asArray();
        /*Configuration structure:
        <netsuite_links>
            <unqiue_identifier>
                <block_class>CLASS_OF_THE_BLOCK_THAT_WILL_GET_THE_VIEW_IN_NETSUITE_BUTTON</block_class>
                <magento_object_registry_variable>REGISTRY_VAR_THAT_HOLDS_THE_MAGENTO_OBJECT_MAPPED_TO_NETSUITE</magento_object_registry_variable>
                <netsuite_field_id>DATA_FIELD_THAT_HOLDS_THE_NETSUITE_INTERNAL_ID</netsuite_field_id>
            </unqiue_identifier>
            <unque_identifier2>
                <block_class>CLASS_OF_THE_BLOCK_THAT_WILL_GET_THE_VIEW_IN_NETSUITE_BUTTON</block_class>
                <magento_object_registry_variable>REGISTRY_VAR_THAT_HOLDS_THE_MAGENTO_OBJECT_MAPPED_TO_NETSUITE</magento_object_registry_variable>
                <netsuite_field_id>DATA_FIELD_THAT_HOLDS_THE_NETSUITE_INTERNAL_ID</netsuite_field_id>
                <netsuite_relative_url>NET_SUITE_RELATIVE_URL_WITH_ID_PLACEHOLDER</netsuite_relative_url>
            </unque_identifier2>
            ....
        </netsuite_links>

        Example:
        <netsuite_links>
            <order_view>
                <block_class>Mage_Adminhtml_Block_Sales_Order_View</block_class>
                <magento_object_registry_variable>current_order</magento_object_registry_variable>
                <netsuite_field_id>internal_netsuite_id</netsuite_field_id>
                <netsuite_relative_url><![CDATA[app/accounting/transactions/salesord.nl?id={{ID}}&whence=]]></netsuite_relative_url>
            </order_view>
        </netsuite_links>
        */


        foreach($netsuiteLinksConfiguration as $netsuiteLinksConfigurationItem) {
            if(!trim($netsuiteLinksConfigurationItem['block_class'])) {
                continue;
            }
            if($block instanceof $netsuiteLinksConfigurationItem['block_class']) {
                $magentoObject = Mage::registry($netsuiteLinksConfigurationItem['magento_object_registry_variable']);
                if($magentoObject instanceof Varien_Object) {
                    $netsuiteInternalId = $magentoObject->getData($netsuiteLinksConfigurationItem['netsuite_field_id']);
                    if($netsuiteInternalId) {
                        $viewInNetsuiteUrl = Mage::helper('rocketweb_netsuite/viewinnetsuite')->getViewInNetsuiteUrl($netsuiteLinksConfigurationItem['netsuite_relative_url'],$netsuiteInternalId,$netsuiteLinksConfigurationItem['magento_object_registry_variable']);
                        $block->addButton('view_in_netsuite', array(
                            'label'     => Mage::helper('core/translate')->__('View in Net Suite'),
                            'onclick'   => 'window.open(\'' . $viewInNetsuiteUrl . '\',\'_blank\')',
                            'class'     => 'go'
                        ));
                    }
                }
            }

        }
    }
}