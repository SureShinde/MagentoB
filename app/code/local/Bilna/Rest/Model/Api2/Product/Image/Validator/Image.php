<?php
/**
 * Description of Bilna_Rest_Model_Api2_Product_Image_Validator_Image
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Rest_Model_Api2_Product_Image_Validator_Image extends Mage_Api2_Model_Resource_Validator {
    /**
     * Validate data. In case of validation failure return false,
     * getErrors() could be used to retrieve list of validation error messages
     *
     * @param array $data
     * @return bool
     */
    public function isValidData(array $data) {
        if (!isset ($data['file_content']) || !isset ($data['file_mime_type']) || empty ($data['file_content']) || empty ($data['file_mime_type'])) {
            $this->_addError('The image is not specified');
        }

        return !count($this->getErrors());
    }
}