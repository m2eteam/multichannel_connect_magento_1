<?php

class M2E_MultichannelConnect_Model_Api2_Product_Attributes_Rest_Admin_V1 extends Mage_Api2_Model_Resource
{
    const ATTRIBUTE_CODES_FILTER = 'attribute_code_list';

    public function dispatch()
    {
        parent::dispatch();

        $this->getResponse()->setHeader('Content-Type', 'application/json', true);
        $this->getResponse()->clearHeader('Location');
    }

    /**
     * Get the list of attribute codes from the body and retrieve their details
     *
     * @param array $filteredData
     * @return string
     * @throws Exception
     */
    protected function _create(array $filteredData)
    {
        $entityType = Mage::getModel('eav/entity_type')->loadByCode('catalog_product');
        /** @var Mage_Catalog_Model_Resource_Product_Attribute_Collection $collection */
        $collection = Mage::getResourceModel('catalog/product_attribute_collection')
            ->setEntityTypeFilter($entityType->getId());
        $store = $this->_getStore();
        if (!empty($filteredData[self::ATTRIBUTE_CODES_FILTER])) {
            $collection->addFieldToFilter(
                'attribute_code',
                array('in' => $filteredData[self::ATTRIBUTE_CODES_FILTER])
            );
        }

        $collection->setOrder('attribute_id', 'ASC');
        $this->_applyCollectionModifiers($collection);

        $result = array();

        if (
            M2E_MultichannelConnect_Model_Api2_RequestValidator::isRequestedPageNumberValid(
                $collection,
                $this->getRequest()->getPageNumber()
            )
        ) {
            foreach ($collection as $attribute) {
                /** @var Mage_Eav_Model_Entity_Attribute $attribute */
                $result[] = array(
                    'attribute_id' => (int)$attribute->getId(),
                    'attribute_code' => $attribute->getAttributeCode(),
                    'frontend_input' => $attribute->getFrontendInput(),
                    'options' => $this->getAttributeOptions($attribute, $store->getId()),
                );
            }
        }

        $this->getResponse()->setBody(
            Mage::helper('core')->jsonEncode($result)
        );

        return '';
    }

    /**
     * Retrieve attribute options
     *
     * @param Mage_Eav_Model_Entity_Attribute $attribute
     * @param int $storeId
     *
     * @return array
     * @throws Mage_Core_Exception
     */
    private function getAttributeOptions(Mage_Eav_Model_Entity_Attribute $attribute, $storeId)
    {
        $options = array();
        if ($attribute->usesSource()) {
            $attribute->setStoreId($storeId);
            foreach ($attribute->getSource()->getAllOptions(false) as $opt) {
                $options[] = array(
                    'label' => $opt['label'],
                    'value' => $opt['value']
                );
            }
        }

        return $options;
    }
}
