<?php
class M2E_MultichannelConnect_Model_Order_ShippingInformation extends Varien_Object
{
    const KEY_IS_SHIPPED = 'isShipped';
    const KEY_TRACKING_TITLE = 'trackingTitle';
    const KEY_TRACKING_NUMBER = 'trackingNumber';

    /**
     * Returns is order shipped
     * @return bool
     */
    public function getIsShipped()
    {
        return $this->getData(self::KEY_IS_SHIPPED);
    }

    /**
     * Get tracking title
     * @return string|null
     */
    public function getTrackingTitle()
    {
        return $this->getData(self::KEY_TRACKING_TITLE);
    }

    /**
     * Get tracking number
     * @return string|null
     */
    public function getTrackingNumber()
    {
        return $this->getData(self::KEY_TRACKING_NUMBER);
    }
}
