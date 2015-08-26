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
        $profile = Mage::registry('profile_data');//var_dump($profile);die;

        /*if (in_array($profile->getRunStatus(), array('pending', 'running', 'paused'))) {
            $this->addTab('status_section', array(
                'label'     => $this->__('Profile Status'),
                'title'     => $this->__('Profile Status'),
                'content'   => $this->getLayout()->createBlock('urapidflow/adminhtml_profile_edit_tab_status')
                    ->setProfile($profile)
                    ->toHtml(),
            ));
            return parent::_beforeToHtml();
        }*/

        $this->addTab('main_section', array(
            'label'     => $this->__('Profile Information'),
            'title'     => $this->__('Profile Information'),
            //'content'   => $this->getLayout()->createBlock('urapidflow/adminhtml_profile_edit_tab_main')
            'content'   => $this->getLayout()->createBlock('bilna_pricevalidation/adminhtml_pricevalidation_edit_tab_main')
                ->setProfile($profile)
                ->toHtml(),
        ));

        $jsonTab = array(
            'label'     => $this->__('Profile Configuration as JSON'),
            'title'     => $this->__('Profile Configuration as JSON'),
            //'content'   => $this->getLayout()->createBlock('urapidflow/adminhtml_profile_edit_tab_json')
            'content'   => $this->getLayout()->createBlock('bilna_pricevalidation/adminhtml_pricevalidation_edit_tab_json')
                ->setProfile($profile)
                ->toHtml(),
        );

        if (!$profile->getId()) {
            $this->addTab('json_section', $jsonTab);
            return parent::_beforeToHtml();
        }

        if (in_array($profile->getRunStatus(), array('stopped', 'finished'))) {
            $this->addTab('status_section', array(
                'label'     => $this->__('Profile Status'),
                'title'     => $this->__('Profile Status'),
                //'content'   => $this->getLayout()->createBlock('urapidflow/adminhtml_profile_status')
                'content'   => $this->getLayout()->createBlock('bilna_pricevalidation/adminhtml_pricevalidation_status')
                    ->setProfile($profile)
                    ->toHtml(),
            ));
        }
/*
        $this->addTab('schedule_section', array(
            'label'     => $this->__('Schedule Options'),
            'title'     => $this->__('Schedule Options'),
            'content'   => $this->getLayout()->createBlock('urapidflow/adminhtml_profile_edit_tab_schedule')
                ->setProfile($profile)
                ->toHtml(),
        ));
*/
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

        $this->addTab('json_section', $jsonTab);

        return parent::_beforeToHtml();
    }
}
