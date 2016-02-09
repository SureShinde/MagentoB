<?php

class Moxy_SocialCommerce_Block_Adminhtml_Collectioncover_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

		public function __construct()
		{
				parent::__construct();
				$this->setId("collectioncoverGrid");
				$this->setDefaultSort("cover_id");
				$this->setDefaultDir("DESC");
				$this->setSaveParametersInSession(true);
		}

		protected function _prepareCollection()
		{
				$collection = Mage::getModel("socialcommerce/collectioncover")->getCollection();
				$this->setCollection($collection);
				return parent::_prepareCollection();
		}
		protected function _prepareColumns()
		{
				$this->addColumn("cover_id", array(
				"header" => Mage::helper("socialcommerce")->__("ID"),
				"align" =>"right",
				"width" => "50px",
			    "type" => "number",
				"index" => "cover_id",
				));
                
				$this->addColumn("caption", array(
				"header" => Mage::helper("socialcommerce")->__("image caption"),
				"index" => "caption",
				));

				$this->addColumn("image", array(
				"header" => Mage::helper("socialcommerce")->__("image file"),
				"index" => "image",
				'align'     =>'left',
				'type'      => 'image',
				'height' => '100px',
				'renderer' => 'Moxy_SocialCommerce_Block_Adminhtml_Renderer_Image', 
				'filter'    => false,
				'sortable'  => false,
				));
						$this->addColumn('category_id', array(
						'header' => Mage::helper('socialcommerce')->__('category'),
						'index' => 'category_id',
						'type' => 'options',
						'options'=>Moxy_SocialCommerce_Block_Adminhtml_Collectioncover_Grid::getOptionArray5(),				
						));
						
			$this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV')); 
			$this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel'));

				return parent::_prepareColumns();
		}

		public function getRowUrl($row)
		{
			   return $this->getUrl("*/*/edit", array("id" => $row->getId()));
		}


		
		protected function _prepareMassaction()
		{
			$this->setMassactionIdField('cover_id');
			$this->getMassactionBlock()->setFormFieldName('cover_ids');
			$this->getMassactionBlock()->setUseSelectAll(true);
			$this->getMassactionBlock()->addItem('remove_collectioncover', array(
					 'label'=> Mage::helper('socialcommerce')->__('Remove Collectioncover'),
					 'url'  => $this->getUrl('*/adminhtml_collectioncover/massRemove'),
					 'confirm' => Mage::helper('socialcommerce')->__('Are you sure?')
				));
			return $this;
		}
			
		static public function getOptionArray5()
		{
            $data_array=array(); 
			foreach(Mage::getModel('socialcommerce/collectioncategory')->getCollection() as $category) {
				$data_array[$category->getCategoryId()] = $category->getName();
			}
            return($data_array);
		}
		static public function getValueArray5()
		{
            $data_array=array();
			foreach(Moxy_SocialCommerce_Block_Adminhtml_Collectioncover_Grid::getOptionArray5() as $k=>$v){
               $data_array[]=array('value'=>$k,'label'=>$v);		
			}
            return($data_array);

		}
		

}
