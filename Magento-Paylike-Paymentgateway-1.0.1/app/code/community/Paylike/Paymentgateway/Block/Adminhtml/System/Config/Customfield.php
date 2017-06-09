<?php
class Paylike_Paymentgateway_Block_Adminhtml_System_Config_Customfield extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    protected function _getHeaderCommentHtml($element)
    {
        $html = '<div class="comment">Hi there'
            . ' <a target="_blank" href="#">Worked</a></div>';

        return $html;
    }

    /*protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $html = parent::_getElementHtml($element);
        $html .= '<h2>Hi there</h2>';
        return $html;
    }*/

}
?>