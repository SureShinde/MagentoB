<?php

class AW_Affiliate_Adminhtml_CategoryController extends Mage_Adminhtml_Controller_Action
{

    protected function _initAction()
    {
        $this->_title($this->__("Magento Campaign"))
            ->_title($this->__("Manage Category"));
        $this->loadLayout()
            ->_setActiveMenu('awaffiliate');
        return $this;
    }

    protected function indexAction()
    {
        $this->_initAction()
            ->renderLayout();
    }

    protected function newAction()
    {
        $this->_initAction();
        $this->_title($this->__("New Category"));
        $this->renderLayout();
    }

    public function categoriesAction()
    {   
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('awaffiliate/adminhtml_category_edit_tab_categoriesgrid')->toHtml()
        );
    }

    public function categoriesJsonAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('awaffiliate/adminhtml_category_edit_tab_categoriesgrid')
                ->getCategoryChildrenJson($this->getRequest()->getParam('category'))
        );
     }

    public function saveAction()
    {
        $data = $this->getRequest()->getPost();

        $categories = array_unique(explode(",", $data['category_ids']));
        print_r($categories);
        $collection = Mage::getModel('awaffiliate/categories')->getCollection();
        foreach($collection as $obj){
            $obj->delete();
        }
        foreach($categories as $value){
            if($value != '')
            {
                $model2 = Mage::getModel('awaffiliate/categories');
                $model2->setCategoryId($value);
                $model2->save();
            }
        }
        $this->getResponse()->setRedirect($this->getUrl('*/adminhtml_category'));
    }
}
