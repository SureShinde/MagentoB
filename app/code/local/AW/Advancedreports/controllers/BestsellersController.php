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

    class AW_Advancedreports_BestsellersController extends AW_Advancedreports_Controller_Action
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('report/advancedreports/bestsellers');
    }

    public function indexAction()
    {   
        $this->loadLayout()
            ->_setActiveMenu('report/advancedreports/bestsellers')
            ->_setSetupTitle( Mage::helper('advancedreports')->__('Bestsellers') )
            ->_addBreadcrumb(Mage::helper('advancedreports')->__('Advanced'), Mage::helper('advancedreports')->__('Advanced'))
            ->_addBreadcrumb(Mage::helper('advancedreports')->__('Bestsellers'), Mage::helper('advancedreports')->__('Bestsellers'))
            ->_addContent( $this->getLayout()->createBlock('advancedreports/advanced_bestsellers') )
            ->renderLayout();
    }

    public function exportOrderedCsvAction()
    {
        $fileName   = 'bestsellers.csv';
        $content    = $this->getLayout()->createBlock('advancedreports/advanced_bestsellers_grid')->setIsExport(true)
            ->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);        
    }

    public function exportOrderedExcelAction()
    {
        $fileName   = 'bestsellers.xml';
        $content    = $this->getLayout()->createBlock('advancedreports/advanced_bestsellers_grid')->setIsExport(true)
            ->getExcel($fileName);

        $this->_prepareDownloadResponse($fileName, $content);
    }



}
