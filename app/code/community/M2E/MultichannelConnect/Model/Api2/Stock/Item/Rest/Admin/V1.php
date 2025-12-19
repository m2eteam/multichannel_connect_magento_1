<?php

class M2E_MultichannelConnect_Model_Api2_Stock_Item_Rest_Admin_V1 extends Mage_CatalogInventory_Model_Api2_Stock_Item_Rest_Admin_V1
{
    protected function _retrieve()
    {
        $sku = $this->getRequest()->getParam('sku');
        $productId = Mage::getModel('catalog/product')->getIdBySku($sku);

        if (!$productId) {
            $this->_critical(self::RESOURCE_REQUEST_DATA_INVALID);
        }

        $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
        $data = $stockItem->getData();
        $data['product_sku'] = $sku;
        $data['store_manage_stock'] = $this->getStoreMangeStockFlag();

        return $data;
    }

    protected function _retrieveCollection()
    {
        $collection = $this->_getCollectionForRetrieve()->load();
        if (
            !M2E_MultichannelConnect_Model_Api2_RequestValidator::isRequestedPageNumberValid(
                $collection,
                $this->getRequest()->getPageNumber()
            )
        ) {
            return array();
        }

        $data = $collection->toArray();

        return isset($data['items']) ? $data['items'] : $data;
    }

    protected function _getCollectionForRetrieve()
    {
        /* @var $collection Mage_CatalogInventory_Model_Resource_Stock_Item_Collection */
        $collection = Mage::getResourceModel('cataloginventory/stock_item_collection');
        $collection->addFieldToFilter('cp_table.type_id', 'simple');
        $store = $this->_getStore();
        $this->_applyCollectionModifiers($collection);
        $configManageStock = $this->getStoreMangeStockFlag();

        $collection->getSelect()
            ->joinInner(
                array('cpw_table' => $collection->getTable('catalog/product_website')),
                'cpw_table.product_id = main_table.product_id',
                array()
            )
            ->where('cpw_table.website_id = ?', (int)$store->getWebsiteId())
            ->columns(array(
                'product_sku' => 'cp_table.sku',
                'store_manage_stock' => new Zend_Db_Expr($configManageStock),
            ));

        return $collection;
    }

    /**
     * @return int
     */
    private function getStoreMangeStockFlag()
    {
        $storeId = $this->_getStore()->getId();

        return (int)Mage::getStoreConfigFlag(
            'cataloginventory/item_options/manage_stock',
            $storeId
        );
    }
}
