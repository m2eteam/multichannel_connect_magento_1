<?php
class M2E_MultichannelConnect_Model_Order_Tax extends Varien_Object
{
    const KEY_TOTAL = 'total';
    const KEY_SHIPPING_TAX = 'shipping_tax';

    /**
     * Get order total tax
     * @return float
     */
    public function getTotal()
    {
        return $this->getData(self::KEY_TOTAL);
    }

    /**
     * Get order shipping tax
     * @return float|null
     */
    public function getShippingTax()
    {
        return $this->getData(self::KEY_SHIPPING_TAX);
    }
}
