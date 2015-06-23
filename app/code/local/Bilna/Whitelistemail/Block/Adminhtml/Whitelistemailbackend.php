<?php
/**
 * Description of Bilna_Whitelistemail_Block_Adminhtml_Whitelistemailbackend
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Whitelistemail_Block_Adminhtml_Whitelistemailbackend extends Mage_Adminhtml_Block_Widget_Grid_Container {
    public function __construct() {
        $this->_blockGroup = 'whitelistemail';
        $this->_controller = 'adminhtml_whitelistemailbackend';
        $this->_headerText = Mage::helper('whitelistemail')->__('Whitelist Email');
        
        parent::__construct();
        
        $this->_removeButton('add');
    }
}
