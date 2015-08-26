<?php

class Bilna_Pricevalidation_Block_Adminhtml_Pricevalidation_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
	parent::__construct();
	//$this->setId('bilna_pricevalidation_pricevalidation_grid');
	//$this->setId('bilna_pricevalidation_pricevalidation_grid');
	$this->setId('profileGrid');
	$this->setDefaultSort('profile_id');
	$this->setDefaultDir('ASC');
	$this->setSaveParametersInSession(true);
	$this->setUseAjax(true);
  }

	/*protected function _prepareLayout()
	{
		parent::_prepareLayout();
		$pager = $this->getLayout()->createBlock('page/html_pager')->setCollection($this->getDatasets());
		$this->setChild('pager', $pager);
		$this->getDatasets()->load();
		return $this;
	}*/

  protected function _prepareCollection()
  {
	$collection = Mage::getModel('bilna_pricevalidation/form')->getCollection();
	//$collection->printLogQuery(true); die;
	$this->setCollection($collection);
	return parent::_prepareCollection();
  }

	protected function _prepareColumns()
	{
		//$hlp = Mage::helper('bilna_pricevalidation');
		$source = Mage::getSingleton('bilna_pricevalidation/source');

		$this->addColumn('profile_id', array(
			'header'    => Mage::helper('bilna_pricevalidation')->__('ID'),
			//'header'    => $this->__('ID'),
			'align'     =>'right',
			'width'     => '50px',
			'index'     => 'profile_id',
			'type'      => 'number',
		));

		$this->addColumn('title', array(
			'header'    => $this->__('Title'),
			'align'     =>'left',
			'index'     => 'title',
		));

		/*
        $this->addColumn('content', array(
            'header'    => Mage::helper('urapidflow')->__('Item Content'),
            'width'     => '150px',
            'index'     => 'content',
        ));
        */


		$this->addColumn('started_at', array(
			'header'    => $this->__('Last Run'),
			'align'     => 'left',
			'width'     => '130px',
			'index'     => 'started_at',
			'type'      => 'datetime',
		));

		$this->addColumn('rows_processed', array(
			'header'    => $this->__('Rows'),
			'align'     => 'left',
			'width'     => '60px',
			'filter'    => false,
			'index'     => 'rows_processed',
		));

		$this->addColumn('rows_errors', array(
			'header'    => $this->__('Errors'),
			'align'     => 'left',
			'width'     => '60px',
			'filter'    => false,
			'index'     => 'rows_errors',
		));
		/*
                $this->addColumn('scheduled_at', array(
                    'header'    => $this->__('Next Schedule'),
                    'align'     => 'left',
                    'width'     => '130px',
                    'index'     => 'scheduled_at',
                    'type'      => 'datetime',
                ));
        */
		$this->addColumn('profile_status', array(
			'header'    => $this->__('Status'),
			'align'     => 'left',
			'width'     => '80px',
			'index'     => 'profile_status',
			'type'      => 'options',
			'options'   => $source->setPath('profile_status')->toOptionHash(),
			//'renderer'  => 'urapidflow/adminhtml_profile_grid_status',
			//'renderer'  => 'bilna_pricevalidation/adminhtml_pricevalidation_grid_status',
		));

		$this->addColumn('run_status', array(
			'header'    => $this->__('Activity'),
			'align'     => 'left',
			'width'     => '80px',
			'index'     => 'run_status',
			'type'      => 'options',
			'options'   => $source->setPath('run_status')->toOptionHash(),
			//'renderer'  => 'urapidflow/adminhtml_profile_grid_status',
			//'renderer'  => 'bilna_pricevalidation/adminhtml_pricevalidation_grid_status',
		));
		/*
                $this->addColumn('invoke_status', array(
                    'header'    => $this->__('Invoke Status'),
                    'align'     => 'left',
                    'width'     => '80px',
                    'index'     => 'invoke_status',
                    'type'      => 'options',
                    'options'   => $source->setPath('invoke_status')->toOptionHash(),
                    'renderer'  => 'urapidflow/adminhtml_profile_grid_status',
                ));
        */
		$this->addColumn('data_type', array(
			'header'    => $this->__('Data Type'),
			'align'     => 'left',
			'index'     => 'data_type',
			'type'      => 'options',
			'options'   => $source->setPath('data_type')->toOptionHash(),
		));

		$this->addColumn('profile_type', array(
			'header'    => $this->__('Profile Type'),
			'align'     => 'left',
			'index'     => 'profile_type',
			'type'      => 'options',
			'options'   => $source->setPath('profile_type')->toOptionHash(),
		));

		/*
                $this->addColumn('action', array(
                    'header'    =>  $this->__('Action'),
                    'width'     => '100',
                    'type'      => 'action',
                    'getter'    => 'getId',
                    'actions'   => array(
                        array(
                            'caption'   => $this->__('Edit'),
                            'url'       => array('base'=> '* /* /edit'),
                            'field'     => 'id'
                        )
                    ),
                    'filter'    => false,
                    'sortable'  => false,
                    'index'     => 'stores',
                    'is_system' => true,
                ));
        */
		//$this->addExportType('*/*/exportCsv', Mage::helper('urapidflow')->__('CSV'));
		//$this->addExportType('*/*/exportXml', Mage::helper('urapidflow')->__('XML'));

		return parent::_prepareColumns();
	}
  
	/*private function getComboForm() {
		$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
		$sql        = "select title from bilna_formbuilder_form";
		$rows       = $connection->fetchAll($sql);
		$result 		= array ();
				
		foreach ($rows as $key=>$row) {
			$result[$row['title']] = $row['title'];
		}
		
		return $result;
		}*/

  protected function _prepareMassaction()
  {
//    $this->setMassactionIdField('profile_id');
//    $this->getMassactionBlock()->setFormFieldName('pricevalidation');
//
//    $this->getMassactionBlock()->addItem('delete',
//      array(
//        'label'		=> Mage::helper('bilna_pricevalidation')->__('Delete'),
//        'url' 		=> $this->getUrl('*/*/massDelete'),
//        'confirm' => Mage::helper('bilna_pricevalidation')->__('Are you sure?')
//      ));
	}

  //Grid with Ajax Request
  public function getGridUrl() 
	{
    return $this->getUrl('*/*/grid', array ('_current' => true));
  }

  public function getRowUrl($row)
  {//var_dump($row->getProfileId());die;
    return $this->getUrl('*/*/edit', array('profile_id' => $row->getProfileId()));
  }	  
}
