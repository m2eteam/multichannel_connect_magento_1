<?php

class M2E_MultichannelConnect_Model_Resource_Product_Type_Configurable
    extends Mage_Catalog_Model_Resource_Product_Type_Configurable
{
    /**
     * @param array $parentIds
     * @param bool $required
     * @return array
     */
    public function getChildrenIds($parentIds, $required = true)
    {
        $childrenIds = array();
        if (!empty($parentIds)) {
            $select = $this->_getReadAdapter()->select()
                ->from(array('l' => $this->getMainTable()), array('product_id', 'parent_id'))
                ->join(
                    array('e' => $this->getTable('catalog/product')),
                    'e.entity_id = l.product_id AND e.required_options = 0',
                    array()
                )
                ->where('parent_id IN (?)', $parentIds);

            foreach ($this->_getReadAdapter()->fetchAll($select) as $row) {
                $childrenIds[$row['parent_id']][] = $row['product_id'];
            }
        }

        return $childrenIds;
    }

    /**
     * @param array $productIds
     * @return array
     */
    public function getConfigurableAttributeCodes($productIds)
    {
        $attributeCodes = array();
        if (!empty($productIds)) {
            $select = $this->_getReadAdapter()->select()
                ->from(array(
                    'sa' => $this->getTable('catalog/product_super_attribute')),
                    array('product_id', 'attribute_id')
                )
                ->join(
                    array('ea' => $this->getTable('eav/attribute')),
                    'ea.attribute_id = sa.attribute_id',
                    array('attribute_code')
                )
                ->where('sa.product_id IN (?)', $productIds);

            foreach ($this->_getReadAdapter()->fetchAll($select) as $row) {
                $attributeCodes[$row['product_id']][] = $row['attribute_code'];
            }
        }

        return $attributeCodes;
    }
}
