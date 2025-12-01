<?php

class M2E_MultichannelConnect_Model_Module
{
    const CLOUD_BASE_URL = 'https://m2e.cloud/';
    const CLOUD_PATH_PATTERN = '?magento1_embedded=true&domain=%s&signature=%s';

    const INSTALLED_FLAG_CONFIG_PATH = 'm2e/multichannelconnect/installed';
    const INIT_HOST_CONFIG_PATH = 'm2e/multichannelconnect/init_host';

    public function isModuleConfigured()
    {
        return $this->isSameHost() && Mage::getStoreConfig(self::INSTALLED_FLAG_CONFIG_PATH);
    }

    public function activate()
    {
        /** @var M2E_MultichannelConnect_Model_CloudProcessor $cloudProcessor */
        $cloudProcessor = Mage::getSingleton('MultichannelConnect/CloudProcessor');
        $cloudProcessor->init();

        $this->assignAdminToRole();
        $this->setModuleAsConfigured();
        Mage::getModel('core/config')->cleanCache();

        return true;
    }

    public function getM2eCloudUrl()
    {
        return sprintf(
            $this->getM2eCloudBaseUrl() . self::CLOUD_PATH_PATTERN,
            Mage::helper('MultichannelConnect')->getDomain(),
            $this->getSignature()
        );
    }

    public function getM2eCloudBaseUrl()
    {
        return self::CLOUD_BASE_URL;
    }

    public function resetActivation()
    {
        Mage::getModel('core/config')->saveConfig(self::INSTALLED_FLAG_CONFIG_PATH, 0);
        Mage::getModel('core/config')->saveConfig(self::INIT_HOST_CONFIG_PATH, '');
        Mage::getModel('core/config')->cleanCache();
    }

    /**
     * Returns the version of the module.
     *
     * @return string
     */
    public function getVersion()
    {
        $moduleConfig = Mage::getConfig()->getModuleConfig('M2E_MultichannelConnect');

        return (string)$moduleConfig->version;
    }

    private function getSignature()
    {
        /** @var M2E_MultichannelConnect_Model_IntegrationService $integrationService */
        $integrationService = Mage::getSingleton('MultichannelConnect/IntegrationService');
        $integration = $integrationService->getIntegration();

        return hash_hmac(
            'sha256',
            $integration->getKey(),
            $integration->getSecret()
        );
    }

    private function setModuleAsConfigured()
    {
        Mage::getModel('core/config')->saveConfig(self::INSTALLED_FLAG_CONFIG_PATH, 1);
        Mage::getModel('core/config')->saveConfig(self::INIT_HOST_CONFIG_PATH,
            Mage::helper('MultichannelConnect')->getDomain()
        );
    }

    private function assignAdminToRole()
    {
        /** @var Mage_Admin_Model_User $adminUser */
        $adminUser = Mage::getSingleton('admin/session')->getUser();

        if ($adminUser && $adminUser->getId()) {
            /** @var M2E_MultichannelConnect_Model_IntegrationService $integrationService */
            $integrationService = Mage::getSingleton('MultichannelConnect/IntegrationService');
            $role = $integrationService->getM2ERole();

            /** @var $aclResource Mage_Api2_Model_Resource_Acl_Global_Role */
            $aclResource = Mage::getResourceModel('api2/acl_global_role');
            $aclResource->saveAdminToRoleRelation($adminUser->getId(), $role->getId());
        } else {
            throw new \LogicException('No admin user is currently logged in');
        }
    }

    private function isSameHost()
    {
        $hostDomain = Mage::helper('MultichannelConnect')->getDomain();
        $initDomain = Mage::getStoreConfig(self::INIT_HOST_CONFIG_PATH);

        return $hostDomain === $initDomain;
    }
}
