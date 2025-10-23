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

    protected function _getCollectionForRetrieve()
    {
        /* @var $collection Mage_CatalogInventory_Model_Resource_Stock_Item_Collection */
        $collection = Mage::getResourceModel('cataloginventory/stock_item_collection');
        $this->_applyCollectionModifiers($collection);
        $configManageStock = $this->getStoreMangeStockFlag();

        $collection->getSelect()->joinLeft(
            array('cp' => $collection->getTable('catalog/product')),
            'main_table.product_id = cp.entity_id',
            array(
                'product_sku' => 'cp.sku',
                'store_manage_stock' => new Zend_Db_Expr((int)$configManageStock)

            )
        );

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
