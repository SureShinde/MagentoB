<?php

class Bilna_Formbuilder_Model_Data extends Mage_Core_Model_Abstract
{	
	protected $formId;

    public function _construct()
    {
        $this->_init('bilna_formbuilder/data');
    }

    /**
     * Create initial table for storing form builder data
     * 
     * @param int $formId
     * @return void
     */
    public function createTable()
    {
    	$this->runQueries("
			CREATE TABLE IF NOT EXISTS `bilna_formbuilder_flat_data_{$this->formId}` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  PRIMARY KEY (`id`)
			);	
		");

		return $this;
    }

    public function dropTable()
    {
        $this->runQueries("
            DROP TABLE IF EXISTS `bilna_formbuilder_flat_data_{$this->formId}`;
        ");
        return $this;
    }

    /**
     * Add new field in form builder data table for storing data from user
     * when the form is already implemented
     *
	 * @param array $data
	 * @return void
	 * @throws Exception
     */
    public function addField(array $data)
    {
    	try {
			$query = "ALTER TABLE `bilna_formbuilder_flat_data_{$this->formId}` ADD COLUMN `{$data['name']}` ";
			$query .= "{$data['type']} ";
			$query .= (int) $data['required'] ? "NOT NULL;": "NULL;";

			if( (int) $data['unique']) {
				$query = array(
					$query,
					$this->addUnique($data['name'])
				);
			}

			$this->runQueries($query);
		} catch (Exception $e) {
			throw $e;
		}

		return $this;
    }

    public function setFormId($id)
    {
    	$this->formId = $id;
    	return $this;
    }

    /**
     * Add unique field if needed
     *
     * @param string $field
     * @param bool $return
     * @return void|string
     */
    protected function addUnique($field, $return = true)
    {	
    	$query = "ALTER TABLE `bilna_formbuilder_flat_data_{$this->formId}` ADD UNIQUE({$field});";
    	if ($return) { 
    		return $query;
    	}
    	$this->runQueries($query);
    	return $this;
    }

    /**
     * Run DDL Queries
     * 
     * @param string|array $query
     * @return void
     */
    protected function runQueries($query)
    {
    	$queries = !is_array($query) ? [$query]: $query;
		$conn = Mage::getSingleton('core/resource')->getConnection('core_write');
		$conn->startSetup();
		foreach ($queries as $query) {
			$conn->query($query);
		}
		$conn->endSetup();
		return $this;
    }

}