<?php
/**
 * Description of Bilna_Formbuilder_Block_Adminhtml_Formbuilder_Edit_Tabs_Data
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Formbuilder_Block_Adminhtml_Formbuilder_Edit_Tabs_Data extends Mage_Adminhtml_Block_Widget_Grid {
    protected $tablePrefix = 'bilna_formbuilder_flat_data_';
    protected $formId = null;

    public function __construct() {
	parent::__construct();
	$this->setId('bilna_formbuilder_formbuilder_edit_tabs_data');
        $this->setDefaultSort('created_at');
	$this->setDefaultDir('DESC');
	$this->setSaveParametersInSession(false);
	$this->setUseAjax(true);
        
        $this->formId = (int) $this->getRequest()->getParam('id');
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('bilna_formbuilder/flat')->getCollection();
        $collection->addFilterToMap('created_at', 'main_table.created_at');
        
        $this->setCollection($collection);
        
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $columns = $this->_getColumnsData();
        
        if ($columns && count($columns) > 0) {
            foreach ($columns as $column) {
                $_index = $column['index'];
                $_title = $column['title'];
                
                if ($_index == 'id') {
                    continue;
                }
                
                if ($_index == 'created_at') {
//                    $this->addColumn('created_at', array (
//                        'header' => Mage::helper('sales')->__('Purchased On'),
//                        'index' => 'created_at',
//                        'filter_index' => 'main_table.created_at',
//                        'type' => 'datetime',
//                        'width' => '100px',
//                    ));
                    
                    $this->addColumn($_index, array (
                        'header' => $_title,
                        'type' => 'datetime',
                        'index' => $_index,
                        'filter_index' => 'main_table.' . $_index,
                        'format' => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
                        'width' => '100px',
                    ));
                }
                else {
                    $this->addColumn($_index, array (
                        'header' => $_title,
                        'index' => $_index,
                        'filter_index' => 'main_table.' . $_index,
                    ));
                }
            }
        }
		
        $this->addExportType('*/*/exportCsv', Mage::helper('bilna_formbuilder')->__('CSV'));

        return parent::_prepareColumns();
    }
        
    protected function _getColumnsData() {
        $tableName = Mage::getSingleton('core/resource')->getTableName($this->tablePrefix . $this->formId);
        $fields = Mage::getSingleton('core/resource')->getConnection('core_read')->describeTable($tableName);
        $fieldArr = array ();
        
        if ($fields && count($fields) > 0) {
            $total = count($fields);
            $no = 0;
            $resetNo = false;
            
            foreach ($fields as $row) {
                $columnName = $row['COLUMN_NAME'];
                $lastNo = $no;
                
                if ($columnName == 'created_at') {
                    $no = $total;
                    $resetNo = true;
                }
                
                $fieldArr[$no] = array (
                    'title' => $this->_getColumnTitle($columnName),
                    'index' => $columnName
                );
                
                if ($resetNo) {
                    $no = $lastNo;
                }
                
                $no++;
            }
        }
        
        //sort array by key
        ksort($fieldArr);
        
        return $fieldArr;
    }
    
    protected function _getColumnTitle($name) {
        return str_replace('_', ' ', uc_words($name));
    }
    
    protected function _getColumnIndex($name) {
        return str_replace(' ', '_', strtolower($name));
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/gridData', array ('_current' => 'true'));
    }
}
