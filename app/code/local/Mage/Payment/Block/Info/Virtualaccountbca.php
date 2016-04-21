<?php
/**
 * @author Bilna Development Team
 */

/**
 * Block for Bank Transfer payment generic info
 */
class Mage_Payment_Block_Info_Virtualaccountbca extends Mage_Payment_Block_Info
{
    /**
     * Instructions text
     *
     * @var string
     */
    protected $_instructions;

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('payment/info/virtualaccountbca.phtml');
    }

    /**
     * Get instructions text from order payment
     * (or from config, if instructions are missed in payment)
     *
     * @return string
     */
    public function getInstructions()
    {
        if (is_null($this->_instructions)) {
            $this->_instructions = $this->getInfo()->getAdditionalInformation('instructions');
            if(empty($this->_instructions)) {
                $this->_instructions = $this->getMethod()->getInstructions();
            }
        }
        return $this->_instructions;
    }

    /**
     * Function to get the VA Number that is used to pay the order
     * @author Indra Halim
     * @return string
     */
    public function getVaNumber()
    {
        $info = $this->getInfo();
        return $info->getVaNumber();
    }
}
