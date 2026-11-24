<?php

class M2E_MultichannelConnect_Model_Api2_StoreView_Rest_Admin_V1 extends Mage_Api2_Model_Resource
{
    /**
     * @return array
     */
    protected function _retrieveCollection()
    {
        $result = array();

        $stores = Mage::app()->getStores(true);

        foreach ($stores as $store) {
            $result[] = array(
                'store_id' => (int)$store->getId(),
                'code' => (string)$store->getCode(),
                'name' => (string)$store->getName(),
                'website_id' => (int)$store->getWebsiteId(),
                'store_group_id' => (int)$store->getGroupId(),
                'is_active' => (int)$store->getIsActive(),
                'extension_attributes' => array(),
            );
        }

        return $result;
    }
}
