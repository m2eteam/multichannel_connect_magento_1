<?php

class M2E_MultichannelConnect_Adminhtml_DashboardController
    extends M2E_MultichannelConnect_Controller_Adminhtml_AbstractController
{
    public function indexAction()
    {
        $module = $this->getModule();
        if ($module->isModuleConfigured()) {
            $this->loadLayout()
                ->_setActiveMenu(self::MENU_ID);
            $this->_addContent(
                $this->getLayout()->createBlock('MultichannelConnect/adminhtml_dashboard')
            );
            $this->renderLayout();
        } else {
            $this->_redirect('m2e_multichannel/adminhtml_welcome/index');
        }
    }
}
