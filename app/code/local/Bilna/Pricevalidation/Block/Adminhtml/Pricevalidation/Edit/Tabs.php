<?php
class Bilna_Pricevalidation_Block_Adminhtml_Pricevalidation_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('profile_id');
        $this->setDestElementId('edit_form');
        $this->setTitle($this->__('Profile Information'));
    }
    protected function _beforeToHtml()
    {
        $hlp = Mage::helper('bilna_pricevalidation');
        $profile = Mage::registry('profile_data');
        $log = Mage::registry('profile_log_data');
        $a = Mage::registry('ran');
        $this->addTab('main_section', array(
            'label'     => $this->__('Profile Information'),
            'title'     => $this->__('Profile Information'),
            'content'   => $this->getLayout()->createBlock('bilna_pricevalidation/adminhtml_pricevalidation_edit_tab_main')
                ->setProfile($profile)
                ->toHtml(),
        ));
        if (!$profile->getId()) {
            return parent::_beforeToHtml();
        }
        if ($profile->getRunStatus() == 'finished') {
            $this->addTab('status_section', array(
                'active'    => Mage::getSingleton('core/session')->getSessionRun(),
                'label'     => $this->__('Profile Status'),
                'title'     => $this->__('Profile Status'),
                'content'   => $this->getLayout()->createBlock('bilna_pricevalidation/adminhtml_pricevalidation_status')
                    ->setLog($log)
                    ->toHtml(),
            ));
        }
        Mage::getSingleton('core/session')->unsSessionRun();
        $tabs = Mage::getSingleton('bilna_pricevalidation/config')
            ->getProfileTabs($profile->getProfileType(), $profile->getDataType());
        if ($tabs) {
            foreach ($tabs as $key=>$tab) {
                $this->addTab($key.'_section', array(
                    'label'     => $this->__((string)$tab->title),
                    'title'     => $this->__((string)$tab->title),
                    'content'   => $this->getLayout()->createBlock((string)$tab->block)
                        ->setProfile($profile)
                        ->toHtml(),
                ));
            }
        }
        return parent::_beforeToHtml();
    }
}
