<?php
/**
* aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Advancedreports
 * @version    2.4.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */

    
class AW_Advancedreports_ChartController extends Mage_Adminhtml_Controller_Action
{
    public function ajaxBlockAction()
    {
        $output = '';
        $width  = $this->getRequest()->getParam('width');
        $block  = $this->getRequest()->getParam('block');
        $option = $this->getRequest()->getParam('option');
        $type   = $this->getRequest()->getParam('type');
        $output = $this->getLayout()->createBlock( 'advancedreports/chart' )
                       ->setType( $type )
                       ->setOption( $option )
                       ->setWidth( $width )
                       ->setRouteOption( $block )
                       ->setHeight( Mage::helper('advancedreports')->getChartHeight() )
                       ->toHtml();
        $this->getResponse()->setBody($output);
        return;
    }

    public function tunnelAction()
    {
        if ($h = $this->getRequest()->getParam('h')){
            $params = unserialize(urldecode(base64_decode($h)));           
            $httpClient = new Zend_Http_Client( AW_Advancedreports_Block_Chart::API_URL );
            $response = $httpClient->setParameterGet($params)->request('GET');
            $headers = $response->getHeaders();
            $this->getResponse()
                ->setHeader('Content-type', $headers['Content-type'])
                ->setBody($response->getBody());
            return;
        }
    }

}
