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
        $wishlist_model = Mage::getModel("wishlist/wishlist")->load($id);
        $customer_collection_data = Mage::getModel("socialcommerce/customercollection")->getCollection()->addFieldToFilter("wishlist_id", array("eq" => $id))->getColumnValues("collection_category_id");
        $prepared_cust_coll_data = array(
            "id" => $wishlist_model["id"],
            "name" => $wishlist_model["name"],
            "collection_category_id" => $customer_collection_data
        );
        if ($wishlist_model->getId()) {
            Mage::register("customercollection_data", $prepared_cust_coll_data);
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
                $wishlist_id = $this->getRequest()->getParam("id");
                if ($post_data["categories"][0] == "") {
                    $existing_collection_datas = Mage::getModel("socialcommerce/customercollection")->getCollection()->addFieldToFilter("wishlist_id", array("eq" => $wishlist_id))->getColumnValues("map_id");
                    if (count($existing_collection_datas) > 0) {
                        foreach ($existing_collection_datas as $existing_collection_data) {
                            Mage::getModel("socialcommerce/customercollection")->setId($existing_collection_data)->delete();
                        }

                        Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Collection category cleared"));
                        if ($this->getRequest()->getParam("back")) {
                            $this->_redirect("*/*/edit", array("id" => $wishlist_id));
                            return;
                        }
                        $this->_redirect("*/*/");
                        return;
                    } else {
                        Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("No data changed"));
                        if ($this->getRequest()->getParam("back")) {
                            $this->_redirect("*/*/edit", array("id" => $wishlist_id));
                            return;
                        }
                        $this->_redirect("*/*/");
                        return;
                    }
                }

                $cust_coll_model = Mage::getModel("socialcommerce/customercollection");
                $existing_cust_coll_datas = $cust_coll_model->getCollection()->addFieldToFilter("wishlist_id", array("eq" => $wishlist_id))->getData();
//echo count($existing_cust_coll_datas);die;
                if (count($existing_cust_coll_datas) > 0) { // If this collection has been mapped before
                    $new_coll_cat_id = array();
                    $unchanged_coll_cat_id = array();
                    foreach ($existing_cust_coll_datas as $cust_coll_data) {
                        // Remove all previous categories that aren't used anymore
                        if (!in_array($cust_coll_data["collection_category_id"], $post_data["categories"])) {

                            $cust_coll_model->setId($cust_coll_data["map_id"])->delete();
                        } else {
                            $unchanged_coll_cat_id[] = $cust_coll_data["collection_category_id"];
                        }
                    }

                    $new_coll_cat_id = array_diff($post_data["categories"], $unchanged_coll_cat_id);

                    $customer_collection_model = Mage::getModel("socialcommerce/customercollection");
                    foreach ($new_coll_cat_id as $coll_cat_id) {
                        $data = array(
                            "wishlist_id" => $wishlist_id,
                            "collection_category_id" => $coll_cat_id
                        );
                        $customer_collection_model->setData($data)->save();
                        unset($data);
                    }
                } else { // If there is no mapping for this collection before
                    foreach ($post_data["categories"] as $coll_cat) {
                        $data = array(
                            "wishlist_id" => $wishlist_id,
                            "collection_category_id" => $coll_cat
                        );
                        $cust_coll_model->addData($data)->save();
                    }
                }

                Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Customercollection was successfully saved"));
                Mage::getSingleton("adminhtml/session")->setCustomercollectionData(false);

                if ($this->getRequest()->getParam("back")) {
                    $this->_redirect("*/*/edit", array("id" => $wishlist_id));
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

