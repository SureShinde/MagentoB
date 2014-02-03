<?php

/**
 * Top menu block
 *
 * @category    Mage
 * @package     Mage_Page
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Alw_Common_Block_Html_Topmenu  extends Mage_Page_Block_Html_Topmenu
{
    /**
     * Top menu data tree
     *
     * @var Varien_Data_Tree_Node
     */
    protected $_menu;

    /**
     * Init top menu tree structure
     */
    public function _construct()
    {
		
        $this->_menu = new Varien_Data_Tree_Node(array(), 'root', new Varien_Data_Tree());
    }

    /**
     * Get top menu html
     *
     * @param string $outermostClass
     * @param string $childrenWrapClass
     * @return string
     */
    public function getHtml($outermostClass = '', $childrenWrapClass = '')
    {
        Mage::dispatchEvent('page_block_html_topmenu_gethtml_before', array(
            'menu' => $this->_menu
        ));

        $this->_menu->setOutermostClass($outermostClass);
        $this->_menu->setChildrenWrapClass($childrenWrapClass);

        $html = $this->_getHtml($this->_menu, $childrenWrapClass);

        Mage::dispatchEvent('page_block_html_topmenu_gethtml_after', array(
            'menu' => $this->_menu,
            'html' => $html
        ));

        return $html;
    }

    /**
     * Recursively generates top menu html from data that is specified in $menuTree
     *
     * @param Varien_Data_Tree_Node $menuTree
     * @param string $childrenWrapClass
     * @return string
     */
    protected function _getHtml(Varien_Data_Tree_Node $menuTree, $childrenWrapClass)
    {
        $html = '';

        $children = $menuTree->getChildren();
        $parentLevel = $menuTree->getLevel();
        $childLevel = is_null($parentLevel) ? 0 : $parentLevel + 1;

        $counter = 1;
        $childrenCount = $children->count();

        $parentPositionClass = $menuTree->getPositionClass();
        $itemPositionClassPrefix = $parentPositionClass ? $parentPositionClass . '-' : 'nav-';

        foreach ($children as $child) {

            $child->setLevel($childLevel);
            $child->setIsFirst($counter == 1);
            $child->setIsLast($counter == $childrenCount);
            $child->setPositionClass($itemPositionClassPrefix . $counter);

            $outermostClassCode = '';
            $outermostClass = $menuTree->getOutermostClass();

            if ($childLevel == 0 && $outermostClass) {
                $outermostClassCode = ' class="' . $outermostClass . '" ';
                $child->setClass($outermostClass);
            }

            $html .= '<li ' . $this->_getRenderedMenuItemAttributes($child) . '>';
			
			$name = $this->__($child->getName());
			preg_match('[&]', $name, $matches, PREG_OFFSET_CAPTURE, 3);
			if(count($matches) == 0 && $child->hasChildren()){
				$name = preg_replace('[\s]', '</br>', $name,1);
			}else{
				$name = str_replace('&', '& </br>', $name );
			}
           if($counter ==$childrenCount){ 
		   $html .= '<a href="' . $child->getUrl() . '" ' . $outermostClassCode . '><span class="special_gift">'
                . $this->__($name) . '</span></a>';
			}
			else if($counter == ($childrenCount-1)){
				$html .= '<a href="' . $child->getUrl() . '" ' . $outermostClassCode . '><span class="gift_set">'
                . $this->__($name) . '</span></a>';
			}
			else{
				$html .= '<a href="' . $child->getUrl() . '" ' . $outermostClassCode . '><span class="'.$childrenCount.'">'
                . $this->__($name) . '</span></a>';
			}

            if ($child->hasChildren()) {
                if (!empty($childrenWrapClass)) {
                    $html .= '<div class="' . $childrenWrapClass . '">';
                }
                $html .= '<ul class="level0 shown-sub">';					
					$html .= '<label class="left-left-block">';
					$html .= '<label class="title-menu-block">CATEGORY</label>';
					$html .= '<label class="space-left-block">';
					$html .= $this->_getHtml($child, $childrenWrapClass);
					$html .= '</label>';
					$html .= '</label>';
					$html .= '<label class="right-right-block">';
					$staticBlock = trim($this->getLayout()->createBlock('cms/block')->setBlockId($child->getId())->toHtml());
					if(!empty($staticBlock)){
						
						$html .= '<span style="background:#f8f2e8; solid #ccc; padding:0px; margin:0 auto; float:right; display:inline-block;">';
						$html .= $staticBlock;
						$html .= '</span>';
					}        
					$html .= '</label>';
				$html .= '</ul>';

                if (!empty($childrenWrapClass)) {
                    $html .= '</div>';
                }
            }
            $html .= '</li>';

            $counter++;
        }

        return $html;
    }

    /**
     * Generates string with all attributes that should be present in menu item element
     *
     * @param Varien_Data_Tree_Node $item
     * @return string
     */
    protected function _getRenderedMenuItemAttributes(Varien_Data_Tree_Node $item)
    {
        $html = '';
        $attributes = $this->_getMenuItemAttributes($item);

        foreach ($attributes as $attributeName => $attributeValue) {
            $html .= ' ' . $attributeName . '="' . str_replace('"', '\"', $attributeValue) . '"';
        }

        return $html;
    }

    /**
     * Returns array of menu item's attributes
     *
     * @param Varien_Data_Tree_Node $item
     * @return array
     */
    protected function _getMenuItemAttributes(Varien_Data_Tree_Node $item)
    {
        $menuItemClasses = $this->_getMenuItemClasses($item);
        $attributes = array(
            'class' => implode(' ', $menuItemClasses)
        );

        return $attributes;
    }

    /**
     * Returns array of menu item's classes
     *
     * @param Varien_Data_Tree_Node $item
     * @return array
     */
    protected function _getMenuItemClasses(Varien_Data_Tree_Node $item)
    {
        $classes = array();

        $classes[] = 'level' . $item->getLevel();
        $classes[] = $item->getPositionClass();

        if ($item->getIsFirst()) {
            $classes[] = 'first';
        }

        if ($item->getIsActive()) {
            $classes[] = 'active';
        }

        if ($item->getIsLast()) {
            $classes[] = 'last';
        }

        if ($item->getClass()) {
            $classes[] = $item->getClass();
        }

        if ($item->hasChildren()) {
            $classes[] = 'parent';
        }

        return $classes;
    }
}
