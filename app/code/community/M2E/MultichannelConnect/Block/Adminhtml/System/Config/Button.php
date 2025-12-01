<?php

class M2E_MultichannelConnect_Block_Adminhtml_System_Config_Button
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $this->setTemplate('MultichannelConnect/system/config/button.phtml');

        return $this->toHtml();
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button');
        $button->setData(array(
            'id'      => 'reset_install_btn',
            'label'   => Mage::helper('MultichannelConnect')->__('Reset'),
            'onclick' => sprintf(
                "M2E_MultichannelConnect_Reset.exec(this, '%s')",
                $this->getAjaxUrl()
            ),
            'class'   => 'scalable',
            'disabled'   => !$this->isAllowedReset()
        ));

        return $button->toHtml();
    }

    public function isAllowedReset()
    {
        /** @var M2E_MultichannelConnect_Model_Module $module */
        $module = Mage::getSingleton('MultichannelConnect/Module');

        return $module->isModuleConfigured();
    }

    private function getAjaxUrl()
    {
        return Mage::helper('adminhtml')->getUrl('m2e_multichannel/adminhtml_configReset/reset');
    }
}
