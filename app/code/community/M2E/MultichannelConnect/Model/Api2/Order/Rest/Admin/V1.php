<?php

class M2E_MultichannelConnect_Model_Api2_Order_Rest_Admin_V1 extends Mage_Sales_Model_Api2_Order_Rest_Admin_V1
{
    const ORDER_INFORMATION = 'orderInformation';

    /**
     * Create product
     *
     * @param array $filteredData
     * @return string
     */
    protected function _create(array $filteredData)
    {
        /** @var M2E_MultichannelConnect_Model_Order_Builder $orderBuilder */
        $orderBuilder = Mage::getModel('MultichannelConnect/Order_Builder');
        try {
            $orderInformation = new M2E_MultichannelConnect_Model_OrderInformation(
                $filteredData[self::ORDER_INFORMATION]
            );
            /** @var Mage_Sales_Model_Order $order */
            $order = $orderBuilder->processOrder($orderInformation);
        } catch (Exception $e) {
            $exceptionMessage = 'Error while creating order: ' . $e->getMessage();
            Mage::logException($e);
            $this->_critical($exceptionMessage, $e->getCode());
        }

        $this->setResultBody($order);

        return '';
    }

    public function dispatch()
    {
        parent::dispatch();

        $this->getResponse()->setHeader('Content-Type', 'application/json', true);
        $this->getResponse()->clearHeader('Location');
    }

    private function setResultBody(Mage_Sales_Model_Order $order)
    {
        $result = $order->getData();
        $orderItems = array();
        foreach ($order->getAllItems() as $item) {
            $orderItems[] = $item->getData();
        }

        $result['items'] = $orderItems;
        $this->getResponse()->setBody(
            Mage::helper('core')->jsonEncode($result)
        );
    }
}
