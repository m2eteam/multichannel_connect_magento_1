<?php

class M2E_MultichannelConnect_Helper_Data extends Mage_Core_Helper_Abstract
{
    const CUSTOM_IDENTIFIER = 'M2E_MultichannelConnect';

    public function getDomain()
    {
        $baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB, true);
        $parseUrl = parse_url($baseUrl);

        return isset($parseUrl['host']) ? $parseUrl['host'] : 'localhost';
    }
}
