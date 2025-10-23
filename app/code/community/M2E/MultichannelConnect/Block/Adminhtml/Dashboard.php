<?php

class M2E_MultichannelConnect_Block_Adminhtml_Dashboard extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_headerText = $this->__('M2E Multichannel Connect');
        $this->setTemplate('MultichannelConnect/dashboard.phtml');
    }

    public function getIframeUrl()
    {
        /** @var M2E_MultichannelConnect_Model_Module $module */
        $module = Mage::getSingleton('MultichannelConnect/Module');

        return $module->getM2eCloudUrl();
    }
}
