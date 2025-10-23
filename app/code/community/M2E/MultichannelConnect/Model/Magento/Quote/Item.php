<?php

class M2E_MultichannelConnect_Model_Magento_Quote_Item
{
    const TAX_CLASS_ID_NONE = 0;
    /** @var Mage_Sales_Model_Quote */
    protected $_quote = null;

    /** @var M2E_MultichannelConnect_Model_Order_OrderItem */
    protected $_proxyItem = null;

    /** @var Mage_Catalog_Model_Product */
    protected $_product = null;

    /** @var Mage_GiftMessage_Model_Message */
    protected $_giftMessage = null;

    /** @var M2E_MultichannelConnect_Model_Order_ProxyObject */
    private $proxyOrder = null;

    public function init(
        Mage_Sales_Model_Quote $quote,
        M2E_MultichannelConnect_Model_Order_OrderItem $proxyItem,
        M2E_MultichannelConnect_Model_Order_ProxyObject $proxyOrder
    ) {
        $this->_quote = $quote;
        $this->_proxyItem = $proxyItem;
        $this->proxyOrder = $proxyOrder;

        return $this;
    }

    //########################################

    /**
     * @return Mage_Catalog_Model_Product|null
     * @throws Exception
     */
    public function getProduct()
    {
        if ($this->_product !== null) {
            return $this->_product;
        }

        $productId = Mage::getModel('catalog/product')->getIdBySku($this->_proxyItem->getSku());
        $this->_product = Mage::getModel('catalog/product')->load($productId);

        // tax class id should be set before price calculation
        return $this->setTaxClassIntoProduct($this->_product);
    }

    /**
     * @return Mage_Catalog_Model_Product
     */
    public function setTaxClassIntoProduct(Mage_Catalog_Model_Product $product)
    {
        $itemTaxRate = $this->getTaxRateOfProxyItem();
        $isOrderHasTax = $this->proxyOrder->hasTax();
        $hasRatesForCountry = Mage::getSingleton('MultichannelConnect/Magento_Tax_Helper')
            ->hasRatesForCountry($this->_quote->getShippingAddress()->getCountryId());
        $calculationBasedOnOrigin = Mage::getSingleton('MultichannelConnect/Magento_Tax_Helper')
            ->isCalculationBasedOnOrigin($this->_quote->getStore());

        if ($this->proxyOrder->isTaxModeNone()
            || ($this->proxyOrder->isTaxModeChannel() && $itemTaxRate <= 0)
            || ($this->proxyOrder->isTaxModeMagento() && !$hasRatesForCountry && !$calculationBasedOnOrigin)
            || ($this->proxyOrder->isTaxModeMixed() && $itemTaxRate <= 0 && $isOrderHasTax)
        ) {
            return $product->setTaxClassId(self::TAX_CLASS_ID_NONE);
        }

        if ($this->proxyOrder->isTaxModeMagento()
            || $itemTaxRate <= 0
            || $itemTaxRate == $this->getProductTaxRate($product->getTaxClassId())
        ) {
            return $product;
        }

        // Create tax rule according to channel tax rate
        // ---------------------------------------
        /** @var $taxRuleBuilder M2E_MultichannelConnect_Model_Magento_Tax_Rule_Builder */
        $taxRuleBuilder = Mage::getModel('MultichannelConnect/Magento_Tax_Rule_Builder');
        $taxRuleBuilder->buildProductTaxRule(
            $itemTaxRate,
            $this->_quote->getShippingAddress()->getCountryId(),
            $this->_quote->getCustomerTaxClassId()
        );

        $taxRule = $taxRuleBuilder->getRule();
        $productTaxClasses = $taxRule->getProductTaxClasses();
        // ---------------------------------------

        return $product->setTaxClassId(array_shift($productTaxClasses));
    }

    protected function getProductTaxRate($productTaxClassId)
    {
        /** @var $taxCalculator Mage_Tax_Model_Calculation */
        $taxCalculator = Mage::getSingleton('tax/calculation');

        $request = $taxCalculator->getRateRequest(
            $this->_quote->getShippingAddress(),
            $this->_quote->getBillingAddress(),
            $this->_quote->getCustomerTaxClassId(),
            $this->_quote->getStore()
        );
        $request->setProductClassId($productTaxClassId);

        return $taxCalculator->getRate($request);
    }

    /**
     * @return float|int
     */
    private function getTaxRateOfProxyItem()
    {
        $productPriceTaxRateObject = $this->proxyOrder->getProductPriceTaxRateObject();

        $rateValue = $productPriceTaxRateObject->getValue();
        if (!$productPriceTaxRateObject->isEnabledRoundingOfValue()) {
            return $rateValue;
        }

        $notRoundedTaxRateValue = $productPriceTaxRateObject->getNotRoundedValue();
        if ($rateValue !== $notRoundedTaxRateValue) {
            //TODO: will implement
        }

        return $notRoundedTaxRateValue;
    }

    //########################################

    public function getRequest()
    {
        $request = new Varien_Object();
        $request->setQty($this->_proxyItem->getQty());

        return $request;
    }

    //########################################

    public function getGiftMessageId()
    {
        $giftMessage = $this->getGiftMessage();

        return $giftMessage ? $giftMessage->getId() : null;
    }

    public function getGiftMessage()
    {
        if ($this->_giftMessage !== null) {
            return $this->_giftMessage;
        }

        $giftMessageData = $this->_proxyItem->getGiftMessage();

        if (!is_array($giftMessageData)) {
            return null;
        }

        $giftMessageData['customer_id'] = (int)$this->_quote->getCustomerId();
        /** @var $giftMessage Mage_GiftMessage_Model_Message */
        $giftMessage = Mage::getModel('giftmessage/message')->addData($giftMessageData);

        if ($giftMessage->isMessageEmpty()) {
            return null;
        }

        $this->_giftMessage = $giftMessage->save();

        return $this->_giftMessage;
    }

    //########################################

    public function getAdditionalData(Mage_Sales_Model_Quote_Item $quoteItem)
    {
        $additionalData = array('');
        $existAdditionalData = is_string($quoteItem->getAdditionalData())
            ? json_decode($quoteItem->getAdditionalData(), true)
            : array();

        return Mage::helper('core')->jsonEncode(array_merge($existAdditionalData, $additionalData));
    }
    //########################################
}
