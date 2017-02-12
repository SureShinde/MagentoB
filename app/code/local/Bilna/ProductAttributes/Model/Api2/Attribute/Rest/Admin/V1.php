<?php

/**
 * API2 class for Product Attributes (admin)
 *
 * @category   Bilna
 * @package    Bilna_ProductAttributes
 * @author     Development Team <development@bilna.com>
 */
class Bilna_ProductAttributes_Model_Api2_Attribute_Rest_Admin_V1 extends Bilna_ProductAttributes_Model_Api2_Attribute_Rest
{
    private $dbRead;

    public function _retrieve()
    {
        $this->dbRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $query = $this->dbRead->query(
            "SELECT
                ea.attribute_code,
                ea.frontend_label
            FROM eav_attribute ea
            JOIN catalog_eav_attribute cea ON ea.attribute_id = cea.attribute_id
            WHERE cea.is_filterable > 0
            ORDER BY ea.attribute_id"
        );

        $result = [];
        while ($data = $query->fetch()) {
            $result[$data['attribute_code']] = $data['frontend_label'];
        }

        return ['attribute' => $result];
    }
}
