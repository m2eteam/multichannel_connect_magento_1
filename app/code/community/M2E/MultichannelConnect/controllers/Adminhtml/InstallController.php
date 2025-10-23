<?php

class M2E_MultichannelConnect_Adminhtml_InstallController
    extends M2E_MultichannelConnect_Controller_Adminhtml_AbstractController
{
    public function indexAction()
    {
        $module = $this->getModule();
        try {
            $module->activate();
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::getSingleton('core/session')->addError($e->getMessage());

            $this->_redirect('m2e_multichannel/adminhtml_welcome/index');
        }

        $this->_redirect('m2e_multichannel/adminhtml_dashboard/index');
    }
}
