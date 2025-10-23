<?php

class M2E_MultichannelConnect_Model_Order_ProxyObjectFactory
{
    /**
     * @param M2E_MultichannelConnect_Model_OrderInformation $orderInformation
     *
     * @return M2E_MultichannelConnect_Model_Order_ProxyObject
     */
    public function create(M2E_MultichannelConnect_Model_OrderInformation $orderInformation)
    {
        return new M2E_MultichannelConnect_Model_Order_ProxyObject($orderInformation);
    }
}
