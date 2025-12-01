<?php

class M2E_MultichannelConnect_Adminhtml_WelcomeController
    extends M2E_MultichannelConnect_Controller_Adminhtml_AbstractController
{
    public function indexAction()
    {
        $module = $this->getModule();
        if ($module->isModuleConfigured()) {
            $this->_redirect('m2e_multichannel/adminhtml_dashboard/index');
        } else {
            $this->loadLayout()
                ->_setActiveMenu(self::MENU_ID);
            $this->initM2eIntegration();
            $this->renderLayout();
        }
    }

    private function initM2eIntegration()
    {
        /** @var M2E_MultichannelConnect_Model_IntegrationService $integrationService */
        $integrationService = Mage::getSingleton('MultichannelConnect/IntegrationService');
        if (!$integrationService->integrationExists()) {
            $role = $integrationService->integrationCreate();
        } else {
            $role = $integrationService->getM2ERole();
        }

        $roleBlock = $this->getLayout()->getBlock('adminhtml.m2e.role.resources');
        $roleBlock->setRole($role);
    }
}
