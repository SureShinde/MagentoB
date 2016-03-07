<?php
class Bilna_Pricevalidation_Block_Adminhtml_Log_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('log_grid');
        $this->setDefaultSort('started_at');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('bilna_pricevalidation/log')->getCollection()->addFieldToFilter('profile_id', $this->getRequest()->getParam('profile_id'));
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    protected function _prepareColumns()
    {
        $this->addColumn('profile_id', array(
            'header' => Mage::helper('bilna_pricevalidation')->__('Profile ID'),
            'align' => 'right',
            'width' => '50px',
            'filter' => false,
            'index' => 'profile_id',
            'type' => 'number',
        ));
        $this->addColumn('started_at', array(
            'header' => $this->__('Started at'),
            'align' => 'left',
            'width' => '130px',
            'filter' => false,
            'index' => 'started_at',
            'type' => 'datetime',
        ));
        $this->addColumn('finished_at', array(
            'header' => $this->__('Finished at'),
            'align' => 'left',
            'width' => '130px',
            'filter' => false,
            'index' => 'finished_at',
            'type' => 'datetime',
        ));
        $this->addColumn('rows_found', array(
            'header' => $this->__('Rows'),
            'align' => 'left',
            'width' => '60px',
            'filter' => false,
            'index' => 'rows_found',
        ));
        $this->addColumn('rows_errors', array(
            'header' => $this->__('Errors'),
            'align' => 'left',
            'width' => '60px',
            'filter' => false,
            'index' => 'rows_errors',
        ));
        $this->addColumn('user_id', array(
            'header' => $this->__('User ID'),
            'align' => 'left',
            'width' => '80px',
            'filter' => false,
            'index' => 'user_id',
            'renderer' => 'Bilna_Pricevalidation_Block_Adminhtml_Render_Loguser'
        ));
        $this->addColumn('source_file', array(
            'header' => $this->__('Source File'),
            'align' => 'left',
            'width' => '80px',
            'filter' => false,
            'renderer' => 'Bilna_Pricevalidation_Block_Adminhtml_Render_Logfilesource'
        ));
        $this->addColumn('error_file', array(
            'header' => $this->__('Error File'),
            'align' => 'left',
            'width' => '80px',
            'filter' => false,
            'index' => 'error_file',
            'renderer' => 'Bilna_Pricevalidation_Block_Adminhtml_Render_Logfile'
        ));
        return parent::_prepareColumns();
    }
    //Grid with Ajax Request
    public function getGridUrl()
    {
        return $this->getUrl('*/*/gridlog', array('_current' => true));
    }
    public function getRowUrl($row) {
    }
    public function getMainButtonsHtml()
    {
        return '';
    }
}
