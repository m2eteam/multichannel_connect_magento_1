<?php

class M2E_MultichannelConnect_Model_Api2_Order_Rewrite_Order_V1 extends Mage_Sales_Model_Api2_Order_Rest_Admin_V1
{
    /**
     * Retrieve collection instance for orders list
     *
     * @return Mage_Sales_Model_Resource_Order_Collection
     */
    protected function _getCollectionForRetrieve()
    {
        $collection = parent::_getCollectionForRetrieve();
        if ($this->getRequest()->getParam('store')) {
            $store = $this->_getStore();
            $collection->addFieldToFilter('store_id', $store->getId());
        }

        return $collection;
    }
}
