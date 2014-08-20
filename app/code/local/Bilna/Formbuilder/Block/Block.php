<?php
//Cms block content block
class Bilna_Formbuilder_Block_Block extends Mage_Core_Block_Template
{
	public function __construct()
	{
		parent::__construct();
		$this->setTemplate('formbuilder/form/default.phtml');
	}
    
	protected function _toHtml($status=false)
	{
		$this->block = NULL;
		$this->inputs = NULL;
		$this->blockId = $this->getBlockId();

		if ($this->blockId) {
			$this->block = Mage::getModel('bilna_formbuilder/form')->getCollection();
			$this->block->getSelect();
			$this->block->addFieldToFilter('main_table.id', $this->blockId);
			$this->block = $this->block->getFirstItem();
			//echo $this->block->getTitle();die;
	
			$this->inputs = Mage::getModel('bilna_formbuilder/form')->getCollection();
			$this->inputs->getSelect()->join('bilna_formbuilder_input', 'main_table.id = bilna_formbuilder_input.form_id');
			$this->inputs->addFieldToFilter('main_table.id', $this->blockId)
						->addOrder('bilna_formbuilder_input.order', 'ASC')
						->addOrder('bilna_formbuilder_input.group', 'ASC');
		}

		$html = $this->renderView();
		return $html;
	}

    //Fungsi Date of Birth (DOB)
	public function _getDateDropdown($year_limit = 0){
        $html_output = '    <div id="date_select" >'."\n";
        $html_output .= '        <label for="date_day"></label>'."\n";

        /*days*/
        $html_output .= '           <select name="date_day" id="day_select">'."\n";
            for ($day = 1; $day <= 31; $day++) {
                $html_output .= '               <option>' . $day . '</option>'."\n";
            }
        $html_output .= '           </select>'."\n";

        /*months*/
        $html_output .= '           <select name="date_month" id="month_select" >'."\n";
        $months = array("", "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
            for ($month = 1; $month <= 12; $month++) {
                $html_output .= '               <option value="' . $month . '">' . $months[$month] . '</option>'."\n";
            }
        $html_output .= '           </select>'."\n";

        /*years*/
        $html_output .= '           <select name="date_year" id="year_select">'."\n";
            //for ($year = 1900; $year <= (date("Y") - $year_limit); $year++) //untuk 1900 - 2014
            for ($year = 1970; $year <= 2015; $year++)
            {
                $html_output .= '               <option>' . $year . '</option>'."\n";
            }
        $html_output .= '           </select>'."\n";

        $html_output .= '   </div>'."\n";
    return $html_output;
	}

}