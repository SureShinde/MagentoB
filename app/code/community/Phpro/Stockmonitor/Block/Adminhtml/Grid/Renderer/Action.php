<?php
class Phpro_Stockmonitor_Block_Adminhtml_Grid_Renderer_Action extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{
	public function render(Varien_Object $row)
        {
        $getData = $row->getData();
        $action_performed = $getData['action_performed'];
        if($this->getColumn()->getIndex() == "stores"){
        $actions = $this->getColumn()->getActions();
        if ( empty($actions) || !is_array($actions) ) {
            return '&nbsp;';
        }
       
        if(sizeof($actions)==1 && !$this->getColumn()->getNoLink()) {
            foreach ($actions as $action) {
                 Mage::log($action);
                if ( is_array($action) ) {
                  
                    if($action_performed == "Product_Update")
                    {
                        $action['caption'] = Mage::helper('sales')->__("View product");
                        $action['url']['base'] = '*/catalog_product/edit';
                        $action['field'] = 'id';
                       $this->getColumn()->setGetter('getProductId');
                       
                    }
                    
					
                    return $this->_toLinkHtml($action, $row);
                }
            }
        }

        $out = '<select class="action-select" onchange="varienGridAction.execute(this);">'
             . '<option value=""></option>';
        $i = 0;
        foreach ($actions as $action){
            $i++;
            if ( is_array($action) ) {
                $out .= $this->_toOptionHtml($action, $row);
            }
        }
        $out .= '</select>';
        
        return $out;
    }
    else
        {
            $getter = $this->getColumn()->getGetter();
            $all = $row->$getter;
            $new = str_replace("_"," ",$action_performed).' '.$all['username'].' ';
            return $new;
        }
        }
        
}
?>