<?php
/**
 * Description of GenerateFormBuilder
 *
 * @author Bilna Development Team <development@bilna.com>
 */

require_once dirname(__FILE__) . '/../abstract.php';

class GenerateFormBuilder extends Mage_Shell_Abstract {
    protected $read = null;
    protected $write = null;
    protected $lastId = null;
    
    protected $tablePrefix = 'bilna_formbuilder_flat_data_';

    public function run() {
        $this->read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $this->write = Mage::getSingleton('core/resource')->getConnection('core_write');
        
        $this->runGenerateTable();
        $this->runImportData();
    }
    
    protected function logProgress($message) {
        if ($this->getArg('verbose')) {
            echo $message . "\n";
        }
    }
    
    /**
     * Generate table bilna_formbuilder_flat_data_*
     */
    protected function runGenerateTable() {
        $this->write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $formData = $this->getFormData();
        
        if ($formData) {
            foreach ($formData as $row) {
                $formId = $row->getId();
                $formInput = $this->getFormInput($formId);
                
                if ($formInput) {
                    if (!$this->generateTable($formId, $formInput)) {
                        $this->logProgress('failed to generate table ' . $this->tablePrefix . $formId);
                        continue;
                    }
                }
            }
        }
    }

    protected function getFormData() {
        $collection = Mage::getModel('bilna_formbuilder/form')->getCollection();
        $collection->addFilter('status', 1)
            ->addFieldToSelect('id')
            ->addFieldToSelect('title')
            ->setOrder('id', 'ASC');
        
        if ($collection && $collection->getSize() > 0) {
            return $collection;
        }
        
        return false;
    }
    
    protected function getFormInput($formId) {
        $collection = Mage::getModel('bilna_formbuilder/input')->getCollection();
        $collection->addFilter('form_id', $formId)
            ->setOrder('main_table.order', 'ASC');
        
        if ($collection && $collection->getSize() > 0) {
            return $collection;
        }
        
        return false;
    }
    
    protected function getColumnExistingTable($formId) {
        $fields = $this->read->describeTable($this->tablePrefix . $formId);
        $fieldArr = array ();
        
        if ($fields) {
            foreach ($fields as $row) {
                $fieldArr[] = $row['COLUMN_NAME'];
            }
        }
        
        return $fieldArr;
    }
    
    protected function getColumnUpdate($columnExists, $columnNewest) {
        $result = array ();
        
        foreach ($columnNewest as $field) {
            $type = $field->getType();
            $name = $field->getName();
            $group = $field->getGroup();
            $dbtype = $field->getDbtype();
            $required = $field->getRequired();
            $comment = $field->getTitle();
            
            $newField = true;
            
            foreach ($columnExists as $column) {
                if ($column == $name) {
                    $newField = false;
                    continue;
                }
            }
            
            if ($newField) {
                $result[] = array (
                    'type' => $type,
                    'name' => $name,
                    'group' => $group,
                    'dbtype' => $dbtype,
                    'required' => $required,
                    'comment' => $comment,
                );
            }
        }
        
        return $result;
    }

    protected function generateTable($formId, $data) {
        //check table is exists
        if ($this->read->isTableExists($this->tablePrefix . $formId)) {
            $columnExists = $this->getColumnExistingTable($formId);
            $columnArr = $this->getColumnUpdate($columnExists, $data);
            $sql = '';
            
            //ALTER TABLE `bilna_formbuilder_input` ADD COLUMN `dbtype` VARCHAR(30) NULL AFTER `type`;
            
            if ($columnArr && count($columnArr > 0)) {
                $sql = sprintf('ALTER TABLE `%s%s` ', $this->tablePrefix, $formId);
                $first = true;
                $lastGroup = '';
                
                foreach ($columnArr as $column) {
                    if ($lastGroup == $column['group']) {
                        continue;
                    }

                    if (!$column['dbtype']) {
                        return false;
                    }
                
                    if (!$first) {
                        $sql .= ", ";
                    }
                    
                    $sql .= sprintf("ADD COLUMN `%s` %s DEFAULT NULL COMMENT '%s'", $column['name'], $column['dbtype'], $column['comment']);
                    $first = false;
                    $lastGroup = $column['group'];
                }
                
                $sql .= ';';
            }
            
            if ($sql) {
                $success = 'successfully update structure table ' . $this->tablePrefix . $formId;
            }
            else {
                $success = 'table ' . $this->tablePrefix . $formId . ', nothing has changed';
            }
        }
        else {
            $lastGroup = '';
            $sql = "CREATE TABLE IF NOT EXISTS `" . $this->tablePrefix . $formId . "` (";
            $sql .= "`id` int(11) NOT NULL AUTO_INCREMENT, ";

            foreach ($data as $row) {
                $type = $row->getType();
                $name = $row->getName();
                $group = $row->getGroup();
                $dbtype = $row->getDbtype();
                $required = $row->getRequired();
                $comment = $row->getTitle();

                if ($lastGroup == $group) {
                    continue;
                }

                if (!$dbtype) {
                    return false;
                }

                // parsing sql required
                $sqlRequired = 'DEFAULT NULL';

                $sql .= sprintf("`%s` %s %s COMMENT '%s', ", $name, $dbtype, $sqlRequired, $comment);
                $lastGroup = $group;
            }

            $sql .= "`created_at` datetime DEFAULT NULL, ";
            $sql .= "PRIMARY KEY (`id`)";
            $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=latin1;";
            
            $success = 'successfully create table ' . $this->tablePrefix . $formId;
        }
        
        try {
            if ($sql) {
                $this->write->query($sql);
            }
            
            $this->logProgress($success);
            
            return true;
        }
        catch (Exception $e) {
            $this->logProgress($e->getMessage());
            
            return false;
        }
    }
    
    /**
     * Import Data from table bilna_formbuilder_data to bilna_formbuilder_flat_data_*
     */
    protected function runImportData() {
        $formData = $this->getFormData();
        
        if ($formData) {
            foreach ($formData as $row) {
                $formId = $row->getId();
                
                //if (in_array($formId, array(1,2))) continue;
                
                $importData = $this->getImportData($formId);
                
                if ($importData) {
                    if (!$this->importData($formId, $importData)) {
                        $this->logProgress('failed to import data to table ' . $this->tablePrefix . $formId);
                        continue;
                    }
                }
            }
        }
    }

    protected function getImportData($formId) {
        $lastIdUpdated = $this->getLastIdUpdated($formId);
        
        $collection = Mage::getModel('bilna_formbuilder/data')->getCollection();
        $collection->addFilter('form_id', $formId)
            ->addFieldToFilter('id', array ('gt' => $lastIdUpdated))
            ->setOrder('record_id', 'ASC');
            //->getSelect()
            //->limit(6);
        
        if ($collection && $collection->getSize() > 0) {
            return $collection;
        }
        
        return false;
    }
    
    protected function getLastIdUpdated($formId) {
        $sql = sprintf("SELECT last_id FROM `bilna_formbuilder_lastupdate` WHERE table_name = '%s%d' LIMIT 1", $this->tablePrefix, $formId);
        $query = $this->read->fetchCol($sql);
        $result = 0;
        
        if ($query) {
            $result = $query[0];
        }
        
        return $result;
    }

    protected function importData($formId, $importData) {
        $sql = '';
        $first = true;
        $newline = false;
        $lastRecordId = null;
        $separateSql = false;
        $saveLastUpdate = false;
        
        foreach ($importData as $row) {
            if ($lastRecordId != $row->getRecordId()) {
                if ($separateSql) {
                    $sql .= ";";
                }
                
                $first = true;
            }
            
            if ($first) {
                $sql .= sprintf("INSERT INTO `%s%d` SET ", $this->tablePrefix, $formId);
                $sql .= sprintf("`created_at` = '%s', ", $row->getCreateDate());
                $first = false;
            }
            else {
                $sql .= ", ";
            }
            
            $sql .= sprintf("`%s` = '%s'", $row->getType(), $row->getValue());
            $lastRecordId = $row->getRecordId();
            $separateSql = true;
            $this->lastId = $row->getId();
        }
        
        $sqlArr = explode(';', $sql);
        
        foreach ($sqlArr as $sqlRec) {
            try {
                $this->write->query($sqlRec);
                $this->logProgress($sqlRec);
                $saveLastUpdate = true;
            }
            catch (Exception $e) {
                $this->logProgress($e->getMessage());
                $saveLastUpdate = false;
            }
        }
        
        if ($saveLastUpdate) {
            $this->saveLastUpdated($formId);
        }
        
        return true;
    }
    
    protected function getFieldName($table) {
        $tableName = Mage::getSingleton('core/resource')->getTableName($table);
        $fields = $this->read->describeTable($tableName);
        $fieldArr = array ();
        
        if ($fields) {
            foreach ($fields as $row) {
                $columnName = $row['COLUMN_NAME'];
                
                if ($columnName != 'id') {
                    $fieldArr[] = $row['COLUMN_NAME'];
                }
            }
        }
        
        return $fieldArr;
    }
    
    /**
     * Save last updated
     */
    protected function saveLastUpdated($formId) {
        $sql = sprintf("INSERT INTO `bilna_formbuilder_lastupdate` (`table_name`, `last_id`) VALUES('%s%d', %d) ON DUPLICATE KEY UPDATE last_id = %d", $this->tablePrefix, $formId ,$this->lastId, $this->lastId);
        
        try {
            $this->write->query($sql);
            $this->logProgress($sql);
        }
        catch (Exception $e) {
            $this->logProgress($e->getMessage());
        }
    }
}

$shell = new GenerateFormBuilder();
$shell->run();
