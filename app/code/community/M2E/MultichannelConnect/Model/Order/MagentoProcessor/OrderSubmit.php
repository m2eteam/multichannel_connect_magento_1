<?php
class M2E_MultichannelConnect_Model_Order_MagentoProcessor_OrderSubmit
{
    /**
     * @param M2E_MultichannelConnect_Model_OrderInformation $orderInformation
     * @return Mage_Sales_Model_Order
     */
    public function process(M2E_MultichannelConnect_Model_OrderInformation $orderInformation)
    {
        $proxyOrder = $this->getProxy($orderInformation);
        /** @var M2E_MultichannelConnect_Model_Magento_Quote_Builder $magentoQuoteBuilder */
        $magentoQuoteBuilder = Mage::getModel('MultichannelConnect/Magento_Quote_Builder', $proxyOrder);
        $magentoQuote = $magentoQuoteBuilder->build();

        /** @var M2E_MultichannelConnect_Model_Magento_Quote_Manager $quoteManager */
        $quoteManager = Mage::getModel('MultichannelConnect/Magento_Quote_Manager');

        return $quoteManager->submit($magentoQuote);
    }

    /**
     * @param M2E_MultichannelConnect_Model_OrderInformation $orderInformation
     * @return M2E_MultichannelConnect_Model_Order_ProxyObject
     */
    private function getProxy(M2E_MultichannelConnect_Model_OrderInformation $orderInformation)
    {
        /** @var M2E_MultichannelConnect_Model_Order_ProxyObjectFactory $proxyObjectFactory */
        $proxyObjectFactory = Mage::getModel('MultichannelConnect/Order_ProxyObjectFactory');
        $proxyOrder = $proxyObjectFactory->create($orderInformation);
        $store = Mage::app()->getStore($orderInformation->getStoreViewCode());
        $proxyOrder->setStore($store);

        return $proxyOrder;
    }
}
