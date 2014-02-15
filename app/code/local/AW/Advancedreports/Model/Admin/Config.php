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

class AW_Advancedreports_Model_Admin_Config extends Mage_Admin_Model_Config
{
    public function __construct()
    {
        parent::__construct();        
    }

    public function loadAclResources(Mage_Admin_Model_Acl $acl, $resource=null, $parentName=null)
    {                    
        parent::loadAclResources($acl, $resource, $parentName);
        if ( $acl && ($parentName == 'admin/report') && ($resource->getName() == 'advancedreports') ){
            Varien_Profiler::start('aw::advancedreports::load_acl_resources');
            $reports = Mage::getModel('advancedreports/additional_reports');
            if ($reports->getCount())
            {
                foreach ($reports->getReports() as $item){
                    $acl_resource = Mage::getModel('admin/acl_resource', $parentName.'/'.$resource->getName().'/'.$item->getName());
                    if (!$acl->has($acl_resource)){
                        $acl->add($acl_resource, $parentName.'/'.$resource->getName());
                    }
                }
            }
            Varien_Profiler::stop('aw::advancedreports::load_acl_resources');
        }
        return $this;
    }






}


