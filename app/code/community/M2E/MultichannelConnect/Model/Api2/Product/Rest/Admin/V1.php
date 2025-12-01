<?php

class M2E_MultichannelConnect_Model_Api2_Product_Rest_Admin_V1 extends Mage_Catalog_Model_Api2_Product_Rest_Admin_V1
{
    const CATEGORY_IDS_FIELD = 'category_ids';
    const MEDIA_GALLERY_FIELD = 'media_gallery';
    const CONFIGURABLE_PRODUCT_LINKS_FIELD = 'configurable_product_links';
    const CONFIGURABLE_ATTRIBUTES_FIELD = 'configurable_attributes';

    protected function _retrieve()
    {
        $this->setProductIdParam();

        $data = parent::_retrieve();
        $product = $this->_getProduct();
        $data[self::CATEGORY_IDS_FIELD] = $product->getCategoryIds();
        $data[self::MEDIA_GALLERY_FIELD] = array();
        if ($product->getMediaGallery('images')) {
            foreach ($product->getMediaGallery('images') as $image) {
                $data[self::MEDIA_GALLERY_FIELD][] = array(
                    'file' => $image['file'],
                    'disabled' => (bool)$image['disabled']
                );
            }
        }

        if ($product->getTypeId() === Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
            $typeInstance = $product->getTypeInstance(true);
            $configurableAttributes = $typeInstance->getConfigurableAttributesAsArray($product);
            $childIds = $typeInstance->getChildrenIds($product->getId());
            $configurableChild = !empty($childIds[0]) ? array_values($childIds[0]) : null;

            $data[self::CONFIGURABLE_PRODUCT_LINKS_FIELD] = $configurableChild;
            $data[self::CONFIGURABLE_ATTRIBUTES_FIELD] = array_map(function ($item) {
                return $item['attribute_code'];
            }, $configurableAttributes);
        }

        return $data;
    }

    protected function _retrieveCollection()
    {
        $this->prepareFilter();

        $collection = $this->getProductCollection();
        $loadedIds = $collection->getLoadedIds();

        /** @var M2E_MultichannelConnect_Model_Resource_Product_Image $productImagesResource */
        $productImagesResource = Mage::getResourceModel('MultichannelConnect/product_image');
        $mediaGalleryData = $productImagesResource->getProductImages($loadedIds, $this->_getStore()->getId());

        /** @var M2E_MultichannelConnect_Model_Resource_Product_Type_Configurable $configurableListResource */
        $configurableListResource = Mage::getResourceModel('MultichannelConnect/product_type_configurable');
        $configurableListChild = $configurableListResource->getChildrenIds($loadedIds);
        $configurableAttributeList = $configurableListResource->getConfigurableAttributeCodes($loadedIds);

        /** @var Mage_Catalog_Model_Product $product */
        foreach ($collection->getItems() as $product) {
            $this->addMedia($product, $mediaGalleryData);
            $this->addConfigurableData($product, $configurableListChild, $configurableAttributeList);
        }

        return $collection->toArray();
    }

    private function prepareFilter()
    {
        $filters = $this->getRequest()->getFilter();
        if (!$filters) {
            return;
        }

        foreach ($filters as &$value) {
            if (isset($value['in']) && is_string($value['in'])) {
                $value['in'] = explode(',', $value['in']);
            }
        }

        $this->getRequest()->setQuery('filter', $filters);
    }

    private function setProductIdParam()
    {
        $sku = $this->getRequest()->getParam('sku');
        $productId = Mage::getModel('catalog/product')
            ->setStoreId($this->_getStore()->getId())
            ->getIdBySku($sku);

        $this->getRequest()->setParam('id', $productId);
    }

    /**
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    private function getProductCollection()
    {
        /** @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $collection = Mage::getResourceModel('catalog/product_collection');

        $store = $this->_getStore();
        $collection->setStoreId($store->getId());
        $collection->addWebsiteFilter($store->getWebsiteId());
        $collection->addAttributeToSelect(array_keys(
            $this->getAvailableAttributes($this->getUserType(), Mage_Api2_Model_Resource::OPERATION_ATTRIBUTE_READ)
        ));

        $this->_applyCategoryFilter($collection);
        $this->_applyCollectionModifiers($collection);
        $collection->load();
        $collection->addCategoryIds();

        return $collection;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param array $mediaGalleryData
     * @return void
     */
    private function addMedia(Mage_Catalog_Model_Product $product, $mediaGalleryData)
    {
        $media = isset($mediaGalleryData[$product->getId()]) ? $mediaGalleryData[$product->getId()] : array();
        $product->setData(self::MEDIA_GALLERY_FIELD, $media);
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param array $configurableListChild
     * @param array $configurableAttributeList
     * @return void
     */
    private function addConfigurableData(
        Mage_Catalog_Model_Product $product,
        $configurableListChild,
        $configurableAttributeList
    ) {
        $configurableChildIds = isset($configurableListChild[$product->getId()])
            ? $configurableListChild[$product->getId()]
            : null;
        $configurableAttributes = isset($configurableAttributeList[$product->getId()])
            ? $configurableAttributeList[$product->getId()]
            : null;

        $product->setData(self::CONFIGURABLE_PRODUCT_LINKS_FIELD, $configurableChildIds);
        $product->setData(self::CONFIGURABLE_ATTRIBUTES_FIELD, $configurableAttributes);
    }
}
