<?php

class Moxy_SocialCommerce_Adminhtml_CustomercollectionController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return true;
    }

    protected function _initAction()
    {
        $this->loadLayout()->_setActiveMenu("socialcommerce/customercollection")->_addBreadcrumb(Mage::helper("adminhtml")->__("Customercollection Manager"),Mage::helper("adminhtml")->__("Customercollection Manager"));
        return $this;
    }

    public function indexAction() 
    {
        $this->_title($this->__("SocialCommerce"));
        $this->_title($this->__("Manager Customercollection"));

        $this->_initAction();
        $this->renderLayout();
    }

    public function editAction()
    {
        $this->_title($this->__("SocialCommerce"));
        $this->_title($this->__("Customercollection"));
        $this->_title($this->__("Edit Item"));

        $id = $this->getRequest()->getParam("id");
        $model = Mage::getModel("socialcommerce/customercollection")->load($id);
        if ($model->getId()) {
            Mage::register("customercollection_data", $model);
            $this->loadLayout();
            $this->_setActiveMenu("socialcommerce/customercollection");
            $this->_addBreadcrumb(Mage::helper("adminhtml")->__("Customercollection Manager"), Mage::helper("adminhtml")->__("Customercollection Manager"));
            $this->_addBreadcrumb(Mage::helper("adminhtml")->__("Customercollection Description"), Mage::helper("adminhtml")->__("Customercollection Description"));
            $this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock("socialcommerce/adminhtml_customercollection_edit"))->_addLeft($this->getLayout()->createBlock("socialcommerce/adminhtml_customercollection_edit_tabs"));
            $this->renderLayout();
        } 
        else {
            Mage::getSingleton("adminhtml/session")->addError(Mage::helper("socialcommerce")->__("Item does not exist."));
                $this->_redirect("*/*/");
        }
    }

    public function saveAction()
    {
        $post_data=$this->getRequest()->getPost();

        if ($post_data) {
            try {
                if (isset($post_data["categories"]) && $post_data["categories"][0] != "") {
                    $combined_categories = implode(",", $post_data["categories"]);
                    $post_data["categories"] = $combined_categories;
                } else {
                    $post_data["categories"] = '';
                }
                $model = Mage::getModel("socialcommerce/customercollection")
                    ->addData($post_data)
                    ->setId($this->getRequest()->getParam("id"))
                    ->save();

                Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Customercollection was successfully saved"));
                Mage::getSingleton("adminhtml/session")->setCustomercollectionData(false);

                if ($this->getRequest()->getParam("back")) {
                    $this->_redirect("*/*/edit", array("id" => $model->getId()));
                    return;
                }
                $this->_redirect("*/*/");
                return;
            } catch (Exception $e) {
                Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
                Mage::getSingleton("adminhtml/session")->setCustomercollectionData($this->getRequest()->getPost());
                $this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
                return;
            }
        }
        $this->_redirect("*/*/");
    }
}

