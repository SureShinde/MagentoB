<?php
/**
 * Description of Bilna_Ccp_Model_Resource_Collection
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Ccp_Model_Resource_Ccpmodel_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract{

	protected function _construct()
    {
        $this->_init('ccp/ccpmodel');
    }
}
