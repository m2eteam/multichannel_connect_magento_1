<?php

class M2E_MultichannelConnect_Model_CloudProcessor
{
    const CLOUD_API_URL = 'https://m2e.cloud/api/v1/magento1/account/login/';

    public function init()
    {
        /** @var M2E_MultichannelConnect_Model_Connector_Client $httpClient */
        $httpClient = Mage::getModel('MultichannelConnect/Connector_Client');

        /** @var M2E_MultichannelConnect_Model_Module $module */
        $module = Mage::getModel('MultichannelConnect/Module');
        $integration = $module->getIntegration();

        /** @var Mage_Core_Model_Store $store */
        $store = Mage::app()->getWebsite(true)->getDefaultStore();
        $baseUrl = $store->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB, true);

        $data = array(
            'app_name' => 'bundle',
            'general_info' => array(
                'currency' => $store->getCurrentCurrencyCode(),
                'country_code' => Mage::getStoreConfig('general/country/default'),
                'domain' => Mage::helper('MultichannelConnect')->getDomain(),
                'timezone' => Mage::getStoreConfig('general/locale/timezone'),
                'store_view_code' => $store->getCode(),
                'store_view_title' => $store->getName()
            ),
            'user_info' => array(
                'email' => Mage::getStoreConfig('trans_email/ident_general/email'),
                'name' => Mage::getStoreConfig('trans_email/ident_general/name')
            ),
            'urls' => array(
                'frontend_url' => $baseUrl,
                'api_root_url' => $this->getRestApiUrl($baseUrl),
                'admin_url' => $this->getAdminUrl(),
                'root_media_folder_url' => $this->getProductMediaUrl($store)
            ),
            'credentials' => array(
                'consumer_key' => $integration->getKey(),
                'consumer_secret' => $integration->getSecret()
            ),
        );

        $httpClient->post(
            $this->getApiUrl(),
            $data
        );
    }

    public function getApiUrl()
    {
        return self::CLOUD_API_URL;
    }

    private function getRestApiUrl($baseUrl)
    {
        return $baseUrl . 'api/rest/';
    }

    private function getProductMediaUrl(Mage_Core_Model_Store $store)
    {
        return $store->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA, true) . 'catalog/product';
    }

    private function getAdminUrl()
    {
        $backendFrontName = (string)Mage::getConfig()->getNode('admin/routers/adminhtml/args/frontName');

        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK, true) . $backendFrontName . '/';
    }
}
