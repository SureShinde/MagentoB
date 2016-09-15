<?php

class Bilna_Formbuilder_Block_Adminhtml_Formbuilder_Edit_Form_Renderer_Fieldset_Customtype 
extends Varien_Data_Form_Element_Abstract
{
    protected $_element;

    public function getElementHtml()
    {

        $niject = '';
        $collection = Mage::registry("grid_value");
        usort($collection, function($a, $b) {
            return $a['order'] < $b['order'] ? -1 : 1;
        });  
        $count = 0;
        foreach ($collection as $key => $value) {
            $inject .= 'arrayRow.add({
                "label":"'.Mage::helper('core')->escapeHtml($value['label']).'",
                "value":"'.Mage::helper('core')->escapeHtml($value['value']).'",
                "order":"'.$value['order'].'",
                "row_counter":arrayRow.row_counter
            }, "headings");';
            $count++;
        }
        //str_replace('\\','\\\\',Mage::helper('core')->escapeHtml($value['message']))
        //ChromePhp::log($inject);

        $html = '
        <div class="grid">
            <table id="attribute-options-table" class="borders" cellspacing="0" cellpadding="0">
                <tbody>
                    <tr class="headings" id="headings">
                        <th>Label</th>
                        <th>Value</th>
                        <th>Order</th>
                        <th style="width:100px;text-align:left;">
                            <button id="add_new_option_button" title="Add Option" type="button" class="scalable add"><span><span><span>Add Option</span></span></span></button>
                        </th>
                    </tr>
                </tbody>
            </table>
        </div>
        <script type="text/javascript">//<![CDATA[
            var arrayRow = {
                template : new Template(
                \'<tr id="row-#{row_counter}">\'+
                \'<td><input name="value[#{row_counter}][label]" value="#{label}" class="input-text required-option" type="text" style="width:50px;text-align:center;"></td>\'+
                \'<td><input name="value[#{row_counter}][value]" value="#{value}" class="input-text required-option" type="text" style="width:50px;text-align:center;"></td>\'+
                \'<td><input name="value[#{row_counter}][order]" value="#{order}" class="input-text required-option" type="text" style="width:50px;text-align:center;"></td>\'+
                \'<td class="a-left" id="delete_button_container_option_#{row_counter}"><input type="hidden" class="delete-flag" value=""/><button onclick="arrayRow.del(#{row_counter});" title="Delete" type="button" class="scalable delete delete-option"><span><span><span>Delete</span></span></span></button></td>\'+
                \'</tr>\'),
                row_counter : 0,
                add : function(templateData, insertAfterId)
                {
                    Element.insert($(insertAfterId), {after: this.template.evaluate(templateData)});
                    arrayRow.row_counter++;
                },
                del : function(rowId)
                {
                    $("row-"+rowId).remove();
                    this.row_counter--;
                }
            };

            '. $inject .'

            $(\'add_new_option_button\').on(\'click\', \'button\', function(){
                arrayRow.add({"label":"","value":"",row_counter:arrayRow.row_counter}, "headings");
            });

            function changeValue(selectElement){
                var elem = ["dropdown", "multiple", "checkbox", "radio"];
                if (elem.includes(selectElement)) {
                    $("grid_value").show();
                    $("text_value").hide();
                } else if(selectElement=="date") {
                    $("grid_value","text_value").invoke("hide");
                } else {
                    $("text_value").show();
                    $("grid_value").hide();
                }
                
                if(selectElement=="textarea") {
                    $("formbuilder_dbtype").value = "text";
                    $("dbtype_length_field").hide();
                } else {
                    $("formbuilder_dbtype").value = "varchar";
                    $("dbtype_length_field").show();
                }
            }
            var type = $("formbuilder_type").value;
            changeValue(type);
            //]]></script>
            ';
            return $html;
        }

    }