<?php

/**
 * API2 class for affiliate (admin)
 *
 * @category   AW
 * @package    AW_Affiliate
 * @author     Development Team <development@bilna.com>
 */
class AW_Affiliate_Model_Api2_Campaigndetail_Rest_Admin_V1 extends AW_Affiliate_Model_Api2_Campaigndetail_Rest
{   
    protected function _retrieve()
    {
    	$campaignId = $this->getRequest()->getParam('id'); //campaign-id
        $campaignCollection = array();
        
    	$campaignDetail = $this->_getCampaignCollectionByCampaignId($campaignId);
            
        if($campaignDetail->getId())
        {
            $campaignCollection = $campaignDetail->getData();
            $campaignCollection['image_url'] = Mage::getBaseUrl('media').$campaignDetail->getImageName();
            return array(
                'campaign_detail' => $campaignCollection, 
                'width_campaign_field' => $this->_getWidthCampaignField(), 
                'category_to_generate' => $this->_getCategoryOfProductsField()
            );
        }
    }

    private function _getCampaignCollectionByCampaignId($campaignId = null)
    {
        $campaign = Mage::getModel('awaffiliate/campaign')->load($campaignId);
        $campaign = $campaign
            ->setOrder('active_to ', 'DESC');
        return $campaign;
    }
    
    private function _getWidthCampaignField()
    {
        $input = array(
            'label' => 'Select Dimension',
            'values'     => array(
                '299x250' => 'Medium Rectangel (299 x 250)',
                '728x90' => 'Leaderboard (728 x 90)',
                '468x60' => 'Full Banner (468 x 60)',
                '320x50' => 'Mobile Banner (320 x 50)',
                '160x600' => 'Wide Skycraper (160 x 600)',
                '120x600' => 'Skycraper (120 x 600)',
                '299x600' => 'Hafl Page (299 x 600)'
            ),
            'html_id' => 'width-to-generate',
            'name' => 'width_to_generate',
            'no_span' => true, 
            'value' =>  '728x90'
        );
        
        return $input;
    }

    private function _getNumberOfProductsField()
    {
        $input = array(
            'label'     => 'Number of Products',
            'html_id'   => 'numofproducts-to-generate',
            'name'      => 'numofproducts_to_generate',
            'no_span'   => true, 
            'value'     => '6'
        );
        
        return $input;
    }

    private function _getCategoryOfProductsField()
    {
        $input = array(
            'values'     => array(
                array('label'=> 'Select by bestseller', 'value' => '1'),
                array('label'=> 'Select by new products', 'value' => '2'),
                array('label'=> 'Select by category', 'value' => '3')
            ),
            'html_id'   => 'category-to-generate',
            'name'      => 'category_to_generate',
            'no_span'   => true, 
            'value'     => '2'
        );
        
        return $input;
    }
    
    /*
    
    private function _getCategoriesField()
    {
        $input = array(
            'label' => $this->__('Select Products Category'),
            'values'     => $this->_getCategories(),
            'html_id'   => 'categories-to-generate',
            'name'      => 'categories_to_generate',
            'no_span'   => true
        );

        return $input;
    }

    private function _getCategories()
    {
        $collection = Mage::getModel('catalog/category')->getCollection()
            ->setStoreId(1)
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('is_active'); 
        $collection->getSelect()
            ->joinInner(
                array( 'awaffiliate_cat' => Mage::getSingleton('core/resource')->getTableName('awaffiliate/categories') ),
                "main_table.entity_id = awaffiliate_cat.category_id",
                array(
                    "category_id" => "awaffiliate_cat.category_id"
                )
            );

        $values=array();

        foreach ($collection as $row){
           $values[] = array(
                'label' => Mage::helper('reports')->__($row['name']),
                'value' => $row['entity_id']
            );
        }

        return $values;
    }
     * 
     */
}