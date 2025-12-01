<?php

class M2E_MultichannelConnect_Model_Resource_Product_Image
{
    /**
     * @param array $productIds
     * @param int $storeId
     * @return array
     */
    public function getProductImages($productIds, $storeId)
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
}
