<?php

class M2E_MultichannelConnect_Model_Api2_Module_Rest_Admin_V1 extends Mage_Api2_Model_Resource
{
    protected function _retrieveCollection()
    {
        /** @var M2E_MultichannelConnect_Model_Module $module */
        $module = Mage::getModel('MultichannelConnect/Module');

        return array(
            'extension_list' => array(
                array(
                    'name' => 'M2E Multichannel Connect',
                    'version' => $module->getVersion(),
                    'is_init_completed' => $module->isModuleConfigured()
                )
            )
        );
    }
}
