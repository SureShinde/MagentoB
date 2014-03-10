<?php
/**
 * @package    Bilna Static Area Manager
 **/

class Bilna_Staticarea_Block_Adminhtml_Manage_Edit_Tab_Contents extends Mage_Adminhtml_Block_Widget_Form {
    
    public function __construct() {
        parent::__construct();
        $this->setId('staticarea_contents')
            ->setSaveParametersInSession(true)
            ->setDefaultSort('sort_order', 'ASC')
            ->setUseAjax(true);
    }

    protected function _prepareColumns() {
        /*$this->addColumn('location', array(
            'header' => $this->__('Image preview'),
            'index' => 'location',
            'width' => '150px',
            'sortable' => false,
            'filter' => false,
            'renderer' => 'AW_Islider_Block_Widget_Grid_Column_Renderer_Imagepreview'
        ));*/

        $this->addColumn('status', array(
            'header' => $this->__('Status'),
            'index' => 'status',
            'type' => 'options',
            'options' => Mage::getModel('staticarea/source_status')->toShortOptionArray(),
            'width' => '200px',
            'sortable' => false
        ));

        $this->addColumn('url', array(
            'header' => $this->__('URL'),
            'index' => 'url',
            'sortable' => false
        ));

        $this->addColumn('active_from', array(
            'header' => $this->__('Active From'),
            'index' => 'active_from',
            'type' => 'date',
            'sortable' => false,
            'renderer' => 'AW_Islider_Block_Widget_Grid_Column_Renderer_Date'
        ));

        $this->addColumn('active_to', array(
            'header' => $this->__('Active To'),
            'index' => 'active_to',
            'type' => 'date',
            'sortable' => false,
            'renderer' => 'AW_Islider_Block_Widget_Grid_Column_Renderer_Date'
        ));

        $this->addColumn('sort_order', array(
            'header' => $this->__('Sort Order'),
            'index' => 'sort_order',
            'width' => '150px'
        ));


        return parent::_prepareColumns();
    }

    protected function _prepareCollection() {
        $_collection = Mage::getModel('staticarea/contents')->getCollection();
        //if($this->getRequest()->getParam('id')) {
            //$_collection->addContentFilter($this->getRequest()->getParam('id'));
            $_collection->addContentFilter(2);
        /*} else {
            $_collection->addContentFilter(-1);
        }*/
        $this->setCollection($_collection);
        return parent::_prepareCollection();
    }

    public function getGridUrl() {
        return $this->getUrl('*/adminhtml_image/grid', array('id' => $this->getRequest()->getParam('id')));
    }

    public function getRowUrl($row) {
        return 'javascript:awISAjaxForm.showForm(null, '.$row->getId().');';
    }

}
