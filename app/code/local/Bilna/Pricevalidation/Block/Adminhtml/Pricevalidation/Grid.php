<?php
class Bilna_Pricevalidation_Block_Adminhtml_Pricevalidation_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('profileGrid');
		$this->setDefaultSort('profile_id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
		$this->setUseAjax(true);
	}
	protected function _prepareCollection()
	{
		$collection = Mage::getModel('bilna_pricevalidation/form')->getCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection();
  	}
	protected function _prepareColumns()
	{
		$source = Mage::getSingleton('bilna_pricevalidation/source');
		$this->addColumn('profile_id', array(
			'header'    => Mage::helper('bilna_pricevalidation')->__('ID'),
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
		$this->addColumn('started_at', array(
			'header'    => $this->__('Last Run'),
			'align'     => 'left',
			'index'     => 'started_at',
			'type'      => 'datetime',
		));
		$this->addColumn('profile_status', array(
			'header'    => $this->__('Status'),
			'align'     => 'left',
			'width'     => '80px',
			'index'     => 'profile_status',
			'type'      => 'options',
			'options'   => $source->setPath('profile_status')->toOptionHash(),
		));
		$this->addColumn('run_status', array(
			'header'    => $this->__('Activity'),
			'align'     => 'left',
			'width'     => '80px',
			'index'     => 'run_status',
			'type'      => 'options',
			'options'   => $source->setPath('run_status')->toOptionHash(),
		));
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
		return parent::_prepareColumns();
	}
    //Grid with Ajax Request
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array ('_current' => true));
    }
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('profile_id' => $row->getProfileId()));
    }
}
