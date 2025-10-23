<?php

class M2E_MultichannelConnect_Model_Api2_Order_Cancel_Rest_Admin_V1 extends M2E_MultichannelConnect_Model_Api2_Order_OrderUpdate
{
    /**
     * Cancel order
     *
     * @param array $filteredData
     * @return string
     * @throws Mage_Api2_Exception
     */
    protected function _create(array $filteredData)
    {
        $order = $this->getOrder($filteredData[self::ORDER_ID_FIELD]);
        if ($order->canCancel()) {
            $order->cancel();
            $order->addStatusHistoryComment('Order was cancelled by M2E Cloud.');
            $order->save();

            $this->getResponse()->setHttpResponseCode(200);
        } else {
            $this->_critical('Can not cancel order.');
        }

        return '';
    }
}
