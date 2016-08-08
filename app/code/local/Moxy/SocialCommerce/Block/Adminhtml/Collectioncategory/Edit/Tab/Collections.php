<?php

class Moxy_SocialCommerce_Block_Adminhtml_Collectioncategory_Edit_Tab_Collections extends Mage_Adminhtml_Block_Widget_Grid
{	
	protected $formId;

	public function __construct()
  	{
		parent::__construct();
		$this->setId('collectionsGrid');
		$this->setDefaultSort('id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
		$this->setUseAjax(true);

		$this->formId = $this->getRequest()->getParam('id');
  	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel('wishlist/wishlist')->getCollection();
        $collection->getSelect()
            ->joinLeft(
                array("customercollection" => "moxy_socialcommerce_map_coll_category"),
                "main_table.wishlist_id = customercollection.wishlist_id AND customercollection.collection_category_id = ".$this->formId,
                array("collection_category_id" => "customercollection.collection_category_id")
            )
            ->where("main_table.name IS NOT NULL");

		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
        $this->addColumn("collection_category_id", array(
            "header" => Mage::helper("socialcommerce")->__("In Category"),
            "align" => "right",
            "type" => "options",
            "index" => "collection_category_id",
            "options" => array("Yes", "No"),
            "renderer" => new Moxy_SocialCommerce_Block_Adminhtml_Collectioncategory_Edit_Tab_Renderer_Incategory(),
            "filter_condition_callback" => array($this, '_filterIncategoryConditionCallback')
        ));

        $this->addColumn("wishlist_id", array(
            "header" => Mage::helper("socialcommerce")->__("ID"),
            "align" => "right",
            "width" => "50px",
            "type" => "number",
            "index" => "wishlist_id",
        ));

        $this->addColumn("name", array(
            "header" => Mage::helper("socialcommerce")->__("name"),
            "index" => "name",
        ));
		  
	  	return parent::_prepareColumns();
	}

    protected function _filterIncategoryConditionCallback($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if ($value == '0') { // this is yes

            $collection = Mage::getModel('wishlist/wishlist')->getCollection();
            $collection->getSelect()
                ->joinLeft(
                    array("customercollection" => "moxy_socialcommerce_map_coll_category"),
                    "main_table.wishlist_id = customercollection.wishlist_id and customercollection.collection_category_id = ".$this->formId,
                    array("collection_category_id" => "customercollection.collection_category_id")
                )
                ->where("main_table.name IS NOT NULL AND customercollection.collection_category_id = ".$this->formId);
            $this->setCollection($collection);
        } else { // this is no
            $customercollection_wishlistid = Mage::getModel('socialcommerce/customercollection')
                ->getCollection()
                ->addFieldToFilter('collection_category_id', array('eq' => $this->formId))
                ->getColumnValues('wishlist_id');
            $collection = Mage::getModel('wishlist/wishlist')->getCollection();
            $collection->getSelect()
                ->joinLeft(
                    array("customercollection" => "moxy_socialcommerce_map_coll_category"),
                    "main_table.wishlist_id = customercollection.wishlist_id",
                    array("collection_category_id" => "customercollection.collection_category_id")
                )
                ->where("main_table.name IS NOT NULL AND (customercollection.collection_category_id != ".$this->formId." OR customercollection.collection_category_id IS NULL) AND main_table.wishlist_id NOT IN (".implode(',', $customercollection_wishlistid).")")
                ->group("main_table.wishlist_id");
            $this->setCollection($collection);
        }
        return $this;
    }

  	protected function _prepareMassaction()
  	{
	  	parent::_prepareMassaction();
        $category_id = $this->getRequest()->getParams()['id'];
        $this->setMassactionIdField('wishlist_id');
        $this->getMassactionBlock()->setFormFieldName('wishlist_ids');
        $this->getMassactionBlock()->addItem(
            'add',
            array ('label'=>'Add to category',
                'url'  => $this->getUrl('*/adminhtml_collectioncategory/massAdd/', array('category_id' => $category_id)),
                'confirm' => Mage::helper('socialcommerce')->__('Are you sure?'))
        );

        return $this;
	}

    //Grid with Ajax Request
	public function getGridUrl() 
	{
		return $this->getUrl('*/*/grid', array ('_current' => true));
	}

  	public function getRowUrl($row)
  	{
		return $this->getUrl('*/*/editInput', array('id' => $this->formId));
  	}	 
}
