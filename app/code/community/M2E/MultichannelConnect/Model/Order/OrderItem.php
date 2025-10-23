<?php
class M2E_MultichannelConnect_Model_Order_OrderItem extends Varien_Object
{
    const KEY_SKU = 'sku';
    const KEY_QTY = 'qty';
    const KEY_PRICE = 'price';

    /**
     * Returns the product SKU.
     * @return string|null Product SKU. Otherwise, null.
     */
    public function getSku()
    {
        return $this->getData(self::KEY_SKU);
    }

    /**
     * Returns the product quantity.
     * @return float Product quantity.
     */
    public function getQty()
    {
        return $this->getData(self::KEY_QTY);
    }

    /**
     * Returns the product price.
     * @return float|null Product price. Otherwise, null.
     */
    public function getPrice()
    {
        return $this->getData(self::KEY_PRICE);
    }
}
