<?php

class M2E_MultichannelConnect_Block_Adminhtml_Welcome extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_headerText = $this->__('Welcome to M2E Multichannel Connect!');
        $this->setTemplate('MultichannelConnect/welcome.phtml');
    }

    /**
     * @return string
     */
    public function getSubmitUrl()
    {
        return $this->getUrl('m2e_multichannel/adminhtml_install/index');
    }
}
