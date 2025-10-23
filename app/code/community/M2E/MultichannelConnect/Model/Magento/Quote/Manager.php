<?php

class M2E_MultichannelConnect_Model_Magento_Quote_Manager
{
    private $additionalData = array();

    /**
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return Mage_Sales_Model_Order
     * @throws Exception
     */
    public function submit($quote)
    {
        try {
            $order = $this->placeOrder($quote);
            $quote->setIsActive(false)->save();
        } catch (Exception $e) {
            $quote->setIsActive(false)->save();
            throw $e;
        }

        return $order;
    }

    public function setAdditionalData($additionalData)
    {
        $this->additionalData = $additionalData;

        return $this;
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @return Mage_Sales_Model_Order
     * @throws Exception
     */
    private function placeOrder($quote)
    {
        /** @var $service Mage_Sales_Model_Service_Quote */
        $service = Mage::getModel('sales/service_quote', $quote);
        $service->setOrderData($this->additionalData);
        $service->submitAll();

        return $service->getOrder();
    }
}
