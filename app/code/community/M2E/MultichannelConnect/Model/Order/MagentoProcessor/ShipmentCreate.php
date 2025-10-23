<?php

class M2E_MultichannelConnect_Model_Order_MagentoProcessor_ShipmentCreate
{
    /**
     * @param Mage_Sales_Model_Order $order
     * @param M2E_MultichannelConnect_Model_Order_ShippingInformation $shippingInformation
     * @return int
     * @throws Exception
     */
    public function process(
        Mage_Sales_Model_Order $order,
        M2E_MultichannelConnect_Model_Order_ShippingInformation $shippingInformation
    ) {
        if ($order->canShip()) {
            $shipment = $this->ship($order);
            if ($shippingInformation->getIsShipped()) {
                $this->addShippmentTrack($shipment, $shippingInformation);
            }
        } else {
            throw new Exception('Cannot ship this order');
        }

        return (int)$shipment->getId();
    }

    /**
     * @param Mage_Sales_Model_Order $order
     *
     * @return Mage_Sales_Model_Order_Shipment
     */
    public function ship(Mage_Sales_Model_Order $order)
    {
        $qtys = array();
        foreach ($order->getAllItems() as $item) {
            $qtyToShip = $item->getQtyToShip();
            if ($qtyToShip > 0) {
                $qtys[$item->getId()] = $qtyToShip;
            }
        }

        $shipment = $order->prepareShipment($qtys);
        $shipment->register();
        $shipment->getOrder()->setIsInProcess(true);
        Mage::getModel('core/resource_transaction')
            ->addObject($shipment)
            ->addObject($shipment->getOrder())
            ->save();

        return $shipment;
    }

    /**
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @param M2E_MultichannelConnect_Model_Order_ShippingInformation $shippingInformation
     * @return void
     */
    private function addShippmentTrack(
        Mage_Sales_Model_Order_Shipment $shipment,
        M2E_MultichannelConnect_Model_Order_ShippingInformation $shippingInformation
    ) {
        Mage::getModel('sales/order_shipment_track')
            ->setShipment($shipment)
            ->setData('title', $shippingInformation->getTrackingTitle())
            ->setData('number', $shippingInformation->getTrackingNumber())
            ->setData('carrier_code', Mage_Sales_Model_Order_Shipment_Track::CUSTOM_CARRIER_CODE)
            ->setData('order_id', $shipment->getData('order_id'))
            ->save();
    }
}
