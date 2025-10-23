<?php
class M2E_MultichannelConnect_Model_Order_Builder
{
    /**
     * @param M2E_MultichannelConnect_Model_OrderInformation $orderInformation
     * @return mixed
     */
    public function processOrder($orderInformation)
    {
        /** @var M2E_MultichannelConnect_Model_Order_MagentoProcessor_OrderSubmit $orderSubmitModel */
        $orderSubmitModel = Mage::getModel('MultichannelConnect/Order_MagentoProcessor_OrderSubmit');
        $order = $orderSubmitModel->process($orderInformation);

        /** @var M2E_MultichannelConnect_Model_Order_MagentoProcessor_InvoiceCreate $invoiceCreateModel */
        $invoiceCreateModel = Mage::getModel('MultichannelConnect/Order_MagentoProcessor_InvoiceCreate');
        $invoiceCreateModel->process($order);

        /** @var M2E_MultichannelConnect_Model_Order_ShippingInformation $shippingInformation */
        $shippingInformation = $orderInformation->getShippingInformation();
        if ($shippingInformation && $shippingInformation->getIsShipped()) {
            /** @var M2E_MultichannelConnect_Model_Order_MagentoProcessor_ShipmentCreate $shipmentCreateModel */
            $shipmentCreateModel = Mage::getModel('MultichannelConnect/Order_MagentoProcessor_ShipmentCreate');
            $shippingId = $shipmentCreateModel->process(
                $order,
                $shippingInformation
            );
        }

        $order = Mage::getModel('sales/order')->load($order->getEntityId());

        if (isset($shippingId)) {
            $order->setData('shipment_id', $shippingId);
        }

        return $order;
    }
}
