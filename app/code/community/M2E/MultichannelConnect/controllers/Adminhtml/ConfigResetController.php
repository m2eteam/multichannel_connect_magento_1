<?php

class M2E_MultichannelConnect_Adminhtml_ConfigResetController
    extends M2E_MultichannelConnect_Controller_Adminhtml_AbstractController
{
    public function resetAction()
    {
        $response = array(
            'success' => true,
            'message' => Mage::helper('MultichannelConnect')->__('Reset complete.'),
        );

        /** @var M2E_MultichannelConnect_Model_Module $module */
        $module = Mage::getSingleton('MultichannelConnect/Module');
        $module->resetActivation();

        /** @var M2E_MultichannelConnect_Model_IntegrationService $integrationService */
        $integrationService = Mage::getSingleton('MultichannelConnect/IntegrationService');
        if (!$integrationService->integrationExists()) {
            try {
                $integrationService->integrationCreate();
            } catch (Exception $e) {
                $response = array(
                    'success' => false,
                    'message' => Mage::helper('MultichannelConnect')->__('Reset failed. Error: ') . $e->getMessage()
                );
            }
        }

        $this->getResponse()->clearHeaders()
            ->setHeader('Content-Type', 'application/json')
            ->setBody(Mage::helper('core')->jsonEncode($response));
    }
}
