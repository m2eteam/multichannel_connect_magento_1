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
            $this->initM2eIntegration($module->getM2ERole());
            $this->renderLayout();
        }
    }

    /**
     * @param Mage_Api2_Model_Acl_Global_Role $role
     *
     * @return void
     */
    private function initM2eIntegration($role)
    {
        $roleBlock = $this->getLayout()->getBlock('adminhtml.m2e.role.resources');
        $roleBlock->setRole($role);
    }
}
