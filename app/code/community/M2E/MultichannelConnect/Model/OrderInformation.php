<?php

class M2E_MultichannelConnect_Model_OrderInformation extends Varien_Object
{
    const ORDER_ITEMS = 'orderItems';
    const SHIPPING_ADDRESS = 'shippingAddress';
    const BILLING_ADDRESS = 'billingAddress';
    const STORE_VIEW_CODE = 'storeViewCode';
    const CURRENCY = 'currency';
    const CHANNEL_ORDER_ID = 'channelOrderId';
    const SHIPPING_INFORMATION = 'shippingInformation';
    const TAX = 'tax';
    const TOTALS = 'totals';

    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->initObjectFields($data);
    }

    /**
     * Returns shipping address
     * @return M2E_MultichannelConnect_Model_Order_Address
     */
    public function getShippingAddress()
    {
        return $this->getData(self::SHIPPING_ADDRESS);
    }

    /**
     * Returns billing address
     * @return M2E_MultichannelConnect_Model_Order_Address|null
     */
    public function getBillingAddress()
    {
        return $this->getData(self::BILLING_ADDRESS);
    }

    /**
     * Returns order items
     * @return M2E_MultichannelConnect_Model_Order_OrderItem[]
     */
    public function getOrderItems()
    {
        return $this->getData(self::ORDER_ITEMS);
    }

    /**
     * Returns store view code
     * @return string
     */
    public function getStoreViewCode()
    {
        return $this->getData(self::STORE_VIEW_CODE);
    }

    /**
     * Returns currency
     * @return string
     */
    public function getCurrency()
    {
        return $this->getData(self::CURRENCY);
    }

    /**
     * Returns channel order id
     * @return string
     */
    public function getChannelOrderId()
    {
        return $this->getData(self::CHANNEL_ORDER_ID);
    }

    /**
     * Returns shipping information
     * @return M2E_MultichannelConnect_Model_Order_ShippingInformation|null
     */
    public function getShippingInformation()
    {
        return $this->getData(self::SHIPPING_INFORMATION);
    }

    /**
     * Returns order tax
     * @return M2E_MultichannelConnect_Model_Order_Tax|null
     */
    public function getTax()
    {
        return $this->getData(self::TAX);
    }

    /**
     * Returns total including tax
     * @return M2E_MultichannelConnect_Model_Order_Total|null
     */
    public function getTotals()
    {
        return $this->getData(self::TOTALS);
    }

    /**
     * Create and set object fields from request
     *
     * @param array $data
     * @return void
     */
    private function initObjectFields(array $data)
    {
        $this->setData(
            self::SHIPPING_ADDRESS,
            new M2E_MultichannelConnect_Model_Order_Address($data[self::SHIPPING_ADDRESS])
        );
        $this->setData(
            self::BILLING_ADDRESS,
            new M2E_MultichannelConnect_Model_Order_Address($data[self::BILLING_ADDRESS])
        );
        $this->setData(
            self::SHIPPING_INFORMATION,
            new M2E_MultichannelConnect_Model_Order_ShippingInformation($data[self::SHIPPING_INFORMATION])
        );
        $this->setData(self::TAX, new M2E_MultichannelConnect_Model_Order_Tax($data[self::TAX]));
        $this->setData(self::TOTALS, new M2E_MultichannelConnect_Model_Order_Total($data[self::TOTALS]));

        $items = array();
        foreach ($data[self::ORDER_ITEMS] as $item) {
            $items[] = new M2E_MultichannelConnect_Model_Order_OrderItem($item);
        }
        $this->setData(self::ORDER_ITEMS, $items);
    }
}
