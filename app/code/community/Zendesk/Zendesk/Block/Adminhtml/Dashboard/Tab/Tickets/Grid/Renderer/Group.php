<?php

/**
 * Copyright 2013 Zendesk.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

class Zendesk_Zendesk_Block_Adminhtml_Dashboard_Tab_Tickets_Grid_Renderer_Group extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $groups = Mage::registry('zendesk_groups');
        $value  = (int) $row->getData($this->getColumn()->getIndex());
        
        $found = array_filter($groups, function($group) use($value) {
            return (int) $group['id'] === $value;
        });
        
        if( count($found) ) {
            $group = array_shift($found);
            
            return $group['name'];
        }
        
        return '';
    }

}
