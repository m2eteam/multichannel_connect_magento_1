<?php

class M2E_MultichannelConnect_Model_Api2_Order_Track_Rest_Admin_V1 extends M2E_MultichannelConnect_Model_Api2_Order_OrderUpdate
{
    /**
     * Add Shipment track order
     *
     * @param array $filteredData
     * @return string
     * @throws Mage_Api2_Exception
     */
    protected function _create(array $filteredData)
    {
        $order = $this->getOrder($filteredData['order_id']);

        if (!empty($filteredData['entity_id'])) {
            $track = $this->update($filteredData);
        } else {
            $track = $this->create($order, $filteredData);
        }

        $this->getResponse()->setBody(
            Mage::helper('core')->jsonEncode($track->getData())
        );

        return '';
    }

    /**
     * Retrieve shipment list by order ID
     *
     * @return array
     * @throws Exception
     */
    protected function _retrieveCollection()
    {
        $orderId = $this->getRequest()->getParam('orderId');
        $result = array();
        /** @var Mage_Sales_Model_Resource_Order_Shipment_Collection $shipments */
        $shipments = Mage::getResourceModel('sales/order_shipment_collection')
            ->addFieldToFilter('order_id', $orderId);

        foreach ($shipments as $shipment) {
            $tracks = array();
            foreach ($shipment->getAllTracks() as $track) {
                $tracks[] = array(
                    'track_number' => $track->getTrackNumber(),
                    'carrier_code' => $track->getCarrierCode(),
                    'title' => $track->getTitle()
                );
            }

            $result[] = array(
                'entity_id' => $shipment->getId(),
                'tracks' => $tracks
            );
        }

        return $result;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param array $data
     * @return Mage_Sales_Model_Order_Shipment_Track
     * @throws Mage_Api2_Exception
     */
    private function create(Mage_Sales_Model_Order $order, $data)
    {
        $shipment = $order->getShipmentsCollection()->getFirstItem();
        if (!$shipment->getId()) {
            $this->_critical('Order has not shipment.');
        }

        $track = Mage::getModel('sales/order_shipment_track')
            ->setCarrierCode($data['carrier_code'])
            ->setTitle($data['title'])
            ->setNumber($data['track_number']);
        $shipment->addTrack($track);

        Mage::getModel('core/resource_transaction')
            ->addObject($shipment)
            ->save();

        return $track;
    }

    /**
     * @param array $data
     * @return Mage_Sales_Model_Order_Shipment_Track
     * @throws Mage_Api2_Exception
     */
    private function update($data)
    {
        $trackId = $data['entity_id'];

        /** @var Mage_Sales_Model_Order_Shipment_Track $track */
        $track = Mage::getModel('sales/order_shipment_track')->load($trackId);
        if (!$track->getId()) {
            $this->_critical(
                "Shipment track with ID {$trackId} does not exist.",
                Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR
            );
        }

        if (!empty($data['carrier_code'])) {
            $track->setCarrierCode($data['carrier_code']);
        }
        if (!empty($data['title'])) {
            $track->setTitle($data['title']);
        }
        if (!empty($data['track_number'])) {
            $track->setNumber($data['track_number']);
        }

        $track->save();

        return $track;
    }
}
