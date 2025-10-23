<?php

class M2E_MultichannelConnect_Model_Api2_Order_Shipment_Rest_Admin_V1 extends M2E_MultichannelConnect_Model_Api2_Order_OrderUpdate
{
    /**
     * Ship order
     *
     * @param array $filteredData
     * @return string
     * @throws Mage_Api2_Exception
     */
    protected function _create(array $filteredData)
    {
        $order = $this->getOrder($filteredData[self::ORDER_ID_FIELD]);
        if ($order->canShip()) {
            /** @var M2E_MultichannelConnect_Model_Order_MagentoProcessor_ShipmentCreate $shipmentCreateModel */
            $shipmentCreateModel = Mage::getModel('MultichannelConnect/Order_MagentoProcessor_ShipmentCreate');
            $shipping = $shipmentCreateModel->ship($order);

            $this->getResponse()->setBody(
                Mage::helper('core')->jsonEncode(array('shipping_id' => $shipping->getId()))
            );
        } else {
            $this->_critical('Can not ship order.');
        }

        return '';
    }
}
