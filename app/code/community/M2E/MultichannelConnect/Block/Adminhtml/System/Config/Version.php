<?php

class M2E_MultichannelConnect_Block_Adminhtml_System_Config_Version
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);

        /** @var M2E_MultichannelConnect_Model_Module $module */
        $module = Mage::getSingleton('MultichannelConnect/Module');
        $version = $module->getVersion();

        return '<div class="control-value">' . $this->escapeHtml($version) . '</div>';
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }
}
