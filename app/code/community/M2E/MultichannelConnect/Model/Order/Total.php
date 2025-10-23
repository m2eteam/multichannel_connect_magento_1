<?php
class M2E_MultichannelConnect_Model_Order_Total extends Varien_Object
{
    const KEY_SUBTOTAL = 'subtotal';
    const KEY_SHIPPING = 'shipping';

    /**
     * Get subtotal
     * @return float
     */
    public function getSubtotal()
    {
        return $this->getData(self::KEY_SUBTOTAL);
    }

    /**
     * Get shipping price
     * @return float
     */
    public function getShipping()
    {
        return $this->getData(self::KEY_SHIPPING);
    }
}
