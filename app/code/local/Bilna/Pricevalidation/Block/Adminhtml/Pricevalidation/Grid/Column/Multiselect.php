<?php

/** Multiselect grid column renderer */

class Bilna_Pricevalidation_Block_Adminhtml_Pricevalidation_Grid_Column_Multiselect extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract

{
    public function render(Varien_Object $row)
    {
        $html = '';
        $value = $row->getData($this->getColumn()->getIndex());
        $valueSeparator = $this->getColumn()->getValueSeparator();
        if (!$valueSeparator) $valueSeparator = ',';
        $lineSeparator = $this->getColumn()->getLineSeparator();
        $options = $this->getColumn()->getOptions();

        $values = explode($valueSeparator, $value);

        foreach ($values as $v) {
            $html .= $lineSeparator;
            if (array_key_exists($v, $options)) $html .= $options[$v];
            else $html .= $value;
        }
        // removing first $lineSeparator
        if ($html && $lineSeparator) $html = substr($html, strlen($lineSeparator));

        return $html;
    }

}
