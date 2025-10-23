<?php

class M2E_MultichannelConnect_Model_Api2_Order_OrderUpdate extends Mage_Api2_Model_Resource
{
    const ORDER_ID_FIELD = 'orderId';

    /**
     * @param int $orderId
     * @return Mage_Sales_Model_Order
     * @throws Mage_Api2_Exception
     */
    protected function getOrder($orderId)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->load($orderId);
        if($order->getId() === null) {
            $this->_critical('Order not found.', Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }

        return $order;
    }

    public function dispatch()
    {
        parent::dispatch();

        $this->getResponse()->setHeader('Content-Type', 'application/json', true);
        $this->getResponse()->clearHeader('Location');
    }
}
