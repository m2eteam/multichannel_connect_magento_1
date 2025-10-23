<?php

class M2E_MultichannelConnect_Model_Api2_Category_Rest_Admin_V1 extends Mage_Api2_Model_Resource
{
    protected function _retrieveCollection()
    {
        $store = $this->_getStore();
        /** @var Mage_Catalog_Model_Resource_Category_Collection $collection */
        $collection = Mage::getModel('catalog/category')->getCollection()
            ->setStoreId($store->getId())
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('is_active');

        $this->_applyCollectionModifiers($collection);

        return $collection->load()->toArray();
    }
}
