<?php

class M2E_MultichannelConnect_Model_Api2_Product_Rest_Admin_V1 extends Mage_Catalog_Model_Api2_Product_Rest_Admin_V1
{
    protected function _retrieve()
    {
        $this->setProductIdParam();

        $data = parent::_retrieve();
        $product = $this->_getProduct();
        $data['category_ids'] = $product->getCategoryIds();
        $data['media_gallery'] = array();
        if ($product->getMediaGallery('images')) {
            foreach ($product->getMediaGallery('images') as $image) {
                $data['media_gallery'][] = array(
                    'file' => $image['file'],
                    'disabled' => (bool)$image['disabled']
                );
            }
        }

        return $data;
    }

    protected function _retrieveCollection()
    {
        /** @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $collection = Mage::getResourceModel('catalog/product_collection');
        $store = $this->_getStore();
        $collection->setStoreId($store->getId());
        $collection->addWebsiteFilter($store->getWebsiteId());
        $collection->addAttributeToSelect(array_keys(
            $this->getAvailableAttributes($this->getUserType(), Mage_Api2_Model_Resource::OPERATION_ATTRIBUTE_READ)
        ));

        $this->joinCategoriesData($collection);
        $this->_applyCategoryFilter($collection);
        $this->_applyCollectionModifiers($collection);
        $collection->load();
        $mediaGalleryData = $this->getProductImages($collection->getLoadedIds(), $store->getId());
        /** @var Mage_Catalog_Model_Product $product */
        foreach ($collection->getItems() as $product) {
            $product->setCategoryIds(explode(',', $product->getData('category_ids')));

            $media = isset($mediaGalleryData[$product->getId()]) ? $mediaGalleryData[$product->getId()] : array();
            $product->setData('media_gallery', $media);
        }

        return $collection->toArray();
    }

    /**
     * @param array $productIds
     * @param int $storeId
     * @return array
     */
    private function getProductImages($productIds, $storeId)
    {
        if (empty($productIds)) {
            return array();
        }
        /** @var Mage_Core_Model_Resource $resource */
        $resource = Mage::getSingleton('core/resource');
        $adapter = $resource->getConnection('core_read');

        $select = $adapter->select()
            ->from(
                array('mg' => $resource->getTableName('catalog/product_attribute_media_gallery')),
                array('entity_id', 'value')
            )
            ->joinLeft(
                array('mgv' => $resource->getTableName('catalog/product_attribute_media_gallery_value')),
                'mg.value_id = mgv.value_id AND (mgv.store_id = 0 OR mgv.store_id = ' . $storeId . ')',
                array('disabled')
            )
            ->where('mg.entity_id IN (?)', $productIds)
            ->order(array('mg.entity_id', 'mgv.position ASC'));

        $result = array();

        foreach ($adapter->fetchAll($select) as $row) {
            $result[$row['entity_id']][] = array(
                'file' => $row['value'],
                'disabled' => (bool)$row['disabled'],
            );
        }

        return $result;
    }

    private function joinCategoriesData(Mage_Catalog_Model_Resource_Product_Collection $collection)
    {
        $collection->getSelect()->joinLeft(
            array('ccp' => $collection->getTable('catalog/category_product')),
            'e.entity_id = ccp.product_id',
            array('category_ids' => new Zend_Db_Expr('GROUP_CONCAT(ccp.category_id)'))
        );

        $collection->getSelect()->group('e.entity_id');
    }

    private function setProductIdParam()
    {
        $sku = $this->getRequest()->getParam('sku');
        $productId = Mage::getModel('catalog/product')
            ->setStoreId($this->_getStore()->getId())
            ->getIdBySku($sku);

        $this->getRequest()->setParam('id', $productId);
    }
}
