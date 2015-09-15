<?php
/**
 * Description of Bilna_Rest_Model_Api2_Cms_Staticarea_Rest
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Rest_Model_Api2_Cms_Staticarea_Rest extends Bilna_Rest_Model_Api2_Cms_Staticarea {
    protected function _getStaticareaContents($_staticarea) {
        $_result = array ();
        $_collection = Mage::getModel('staticarea/contents')->getCollection()
            ->addFieldToFilter('staticarea_id', $_staticarea->getId())
            ->addFieldToFilter('status', 1)
            ->addFieldToFilter('DATE(active_from)', array ('from' => $this->_getCurrentDate()))
            ->addFieldToFilter('DATE(active_to)', array ('to' => $this->_getCurrentDate()));
        
        if ($_collection->getSize() > 0) {
            foreach ($_collection->load() as $_block) {
                $_result[] = $this->_renderStaticarea($_staticarea, $_block);
                
                if ($_staticarea->getType() == 'single') {
                    break;
                }
            }
        }
        
        return $_result;
    }
    
    protected function _getCurrentDate() {
        return Mage::getModel('core/date')->date('Y-m-d');
    }

    protected function _renderStaticarea($_staticarea, $_block) {
        return sprintf("<a href=\"%s\" alt=\"%s\" target=\"%s\">%s</a>", $_block->getUrl(), $_staticarea->getAreaName(), $_block->getUrlAction(), $this->_getHtmlCode($_block));
    }

    protected function _getHtmlCode($_block) {
        $_block->afterLoad();
        
        if ($_block) {
            $_helper = Mage::helper('cms');
            $_processor = $_helper->getBlockTemplateProcessor();
            $_html = $_processor->filter($_block->getContent());
            
            return $_html;
        }
        
        return null;
    }
}
