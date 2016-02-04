<?php

class Bilna_Rules_Model_Observer
{
    /**
     * Adds new conditions
     * @param   Varien_Event_Observer $observer
     */
    public function handleNewConditions($observer)
    {
        $transport = $observer->getAdditional();
        $cond = $transport->getConditions();
        if (!is_array($cond)) {
            $cond = array();
        }

        $types = array(
            'orders' => 'Purchases history',
        );
        foreach ($types as $typeCode => $typeLabel) {
            $condition = Mage::getModel('bilna_rules/rule_condition_' . $typeCode);
            $conditionAttributes = $condition->loadAttributeOptions()->getAttributeOption();

            $attributes = array();
            foreach ($conditionAttributes as $code => $label) {
                $attributes[] = array(
                    'value' => 'bilna_rules/rule_condition_' . $typeCode . '|' . $code,
                    'label' => $label,
                );
            }
            $cond[] = array(
                'value' => $attributes,
                'label' => Mage::helper('bilna_rules')->__($typeLabel),
            );
        }
        $transport->setConditions($cond);

        return $this;
    }

}
