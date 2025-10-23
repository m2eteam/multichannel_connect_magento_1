<?php

class M2E_MultichannelConnect_Model_Magento_Quote_Builder
{
    /** @var M2E_MultichannelConnect_Model_Order_ProxyObject */
    private $proxyOrder;

    /** @var Mage_Sales_Model_Quote|null */
    private $quote = null;

    private $originalStoreConfig = array();

    public function __construct(M2E_MultichannelConnect_Model_Order_ProxyObject $proxyOrder)
    {
        $this->proxyOrder = $proxyOrder;
    }

    public function __destruct()
    {
        if ($this->quote === null) {
            return;
        }

        $store = $this->quote->getStore();

        foreach ($this->originalStoreConfig as $key => $value) {
            $store->setConfig($key, $value);
        }
    }

    //########################################

    public function getQuote()
    {
        return $this->quote;
    }

    //########################################

    /**
     * @return Mage_Sales_Model_Quote
     * @throws Exception
     */
    public function build()
    {
        try {
            // do not change invoke order
            // ---------------------------------------
            $this->initializeQuote();
            $this->initializeCustomer();
            $this->initializeAddresses();

            $this->configureStore();
            $this->configureTaxCalculation();

            $this->initializeCurrency();
            $this->initializeShippingMethodData();
            $this->initializeQuoteItems();
            $this->initializePaymentMethodData();

            $this->quote->collectTotals()->save();

            $this->prepareOrderNumber();

            return $this->quote;
            // ---------------------------------------
        } catch (Exception $e) {
            // Remove ordered items from customer cart
            $this->quote->setIsActive(false);
            $this->quote->removeAllAddresses();
            $this->quote->removeAllItems();

            $this->quote->save();
            throw $e;
        }
    }

    //########################################

    protected function initializeQuote()
    {
        $this->quote = Mage::getModel('sales/quote');

        $this->quote->setCheckoutMethod($this->proxyOrder->getCheckoutMethod());
        $this->quote->setStore($this->proxyOrder->getStore());
        $this->quote->getStore()->setData('current_currency', $this->quote->getStore()->getBaseCurrency());
        $this->quote->save();

        $this->quote->setIsM2eQuote(true);
        $this->quote->setNeedProcessChannelTaxes(true);

        Mage::getSingleton('checkout/session')->replaceQuote($this->quote);
    }

    //########################################

    protected function initializeCustomer()
    {
        $this->quote
            ->setCustomerId(null)
            ->setCustomerEmail($this->proxyOrder->getBuyerEmail())
            ->setCustomerFirstname($this->proxyOrder->getCustomerFirstName())
            ->setCustomerLastname($this->proxyOrder->getCustomerLastName())
            ->setCustomerIsGuest(true)
            ->setCustomerGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);
    }

    //########################################

    protected function initializeAddresses()
    {
        $billingAddress = $this->quote->getBillingAddress();
        $billingAddress->addData($this->proxyOrder->getBillingAddressData());
        $billingAddress->implodeStreetAddress();

        $billingAddress->setLimitCarrier($this->proxyOrder->getCarrierCode());
        $billingAddress->setShippingMethod($this->proxyOrder->getShippingMethodCode());
        $billingAddress->setCollectShippingRates(true);
        $billingAddress->setShouldIgnoreValidation(true);

        // ---------------------------------------

        $shippingAddress = $this->quote->getShippingAddress();
        $shippingAddress->setSameAsBilling(0); // maybe just set same as billing?
        $shippingAddress->addData($this->proxyOrder->getShippingAddress());
        $shippingAddress->implodeStreetAddress();

        $shippingAddress->setLimitCarrier($this->proxyOrder->getCarrierCode());
        $shippingAddress->setShippingMethod($this->proxyOrder->getShippingMethodCode());
        $shippingAddress->setCollectShippingRates(true);

        // ---------------------------------------
    }

    //########################################

    protected function initializeCurrency()
    {
        /** @var $currency M2E_MultichannelConnect_Model_Currency */
        $currency = Mage::getSingleton('MultichannelConnect/Currency');

        if ($currency->isConvertible($this->proxyOrder->getCurrency(), $this->quote->getStore())) {
            $currentCurrency = Mage::getModel('directory/currency')->load($this->proxyOrder->getCurrency());
        } else {
            $currentCurrency = $this->quote->getStore()->getBaseCurrency();
        }

        $this->quote->getStore()->setData('current_currency', $currentCurrency);
    }

    //########################################

    /**
     * Configure store (invoked only after address, customer and store initialization and before price calculations)
     */
    protected function configureStore()
    {
        /** @var $storeConfigurator M2E_MultichannelConnect_Model_Magento_Quote_Store_Configurator */
        $storeConfigurator = Mage::getModel('MultichannelConnect/Magento_Quote_Store_Configurator');
        $storeConfigurator->init($this->quote, $this->proxyOrder);

        $this->originalStoreConfig = $storeConfigurator->getOriginalStoreConfig();

        $storeConfigurator->prepareStoreConfigForOrder();
    }

    //########################################

    protected function configureTaxCalculation()
    {
        // this prevents customer session initialization (which affects cookies)
        // see Mage_Tax_Model_Calculation::getCustomer()
        Mage::getSingleton('tax/calculation')->setCustomer($this->quote->getCustomer());
    }

    //########################################

    /**
     * @param M2E_MultichannelConnect_Model_Order_OrderItem $item
     * @param M2E_MultichannelConnect_Model_Magento_Quote_Item $quoteItemBuilder
     * @param Mage_Catalog_Model_Product $product
     * @param Varien_Object $request
     * @throws Exception
     */
    protected function initializeQuoteItem($item, $quoteItemBuilder, $product, $request)
    {
        // ---------------------------------------
        $productOriginalPrice = (float)$product->getPrice();

        $price = $this->proxyOrder->convertPriceToBase($item->getPrice());
        $product->setPrice($price);
        $product->setSpecialPrice($price);
        // ---------------------------------------

        // see Mage_Sales_Model_Observer::substractQtyFromQuotes
        $this->quote->setItemsCount($this->quote->getItemsCount() + 1);
        $this->quote->setItemsQty((float)$this->quote->getItemsQty() + $request->getQty());

        $result = $this->quote->addProduct($product, $request);
        if (is_string($result)) {
            throw new Exception($result);
        }

        $quoteItem = $this->quote->getItemByProduct($product);
        if ($quoteItem === false) {
            return;
        }

        $weight = $product->getTypeInstance()->getWeight();
        $quoteItem->setStoreId($this->quote->getStoreId());
        $quoteItem->setOriginalCustomPrice($item->getPrice());
        $quoteItem->setOriginalPrice($productOriginalPrice);
        $quoteItem->setBaseOriginalPrice($productOriginalPrice);
        $quoteItem->setWeight($weight);
        $quoteItem->setNoDiscount(1);

        $giftMessageId = $quoteItemBuilder->getGiftMessageId();
        if (!empty($giftMessageId)) {
            $quoteItem->setGiftMessageId($giftMessageId);
        }

        $quoteItem->setAdditionalData($quoteItemBuilder->getAdditionalData($quoteItem));

        $quoteItem->save();
    }

    /**
     * @throws Exception
     */
    protected function initializeQuoteItems()
    {
        foreach ($this->proxyOrder->getItems() as $item) {
            $this->clearQuoteItemsCache();

            /** @var M2E_MultichannelConnect_Model_Magento_Quote_Item $quoteItemBuilder */
            $quoteItemBuilder = Mage::getModel('MultichannelConnect/Magento_Quote_Item');
            $quoteItemBuilder->init($this->quote, $item, $this->proxyOrder);

            $product = $quoteItemBuilder->getProduct();
            $this->initializeQuoteItem($item, $quoteItemBuilder, $product, $quoteItemBuilder->getRequest());
        }
    }

    /**
     * Mage_Sales_Model_Quote_Address caches items after each collectTotals call. Some extensions calls collectTotals
     * after adding new item to quote in observers. So we need clear this cache before adding new item to quote.
     */
    protected function clearQuoteItemsCache()
    {
        foreach ($this->quote->getAllAddresses() as $address) {

            /** @var $address Mage_Sales_Model_Quote_Address */

            $address->unsetData('cached_items_all');
            $address->unsetData('cached_items_nominal');
            $address->unsetData('cached_items_nonnominal');
        }
    }

    //########################################

    protected function initializeShippingMethodData()
    {
        Mage::helper('MultichannelConnect/Data_Global')->unsetValue('shipping_data');
        Mage::helper('MultichannelConnect/Data_Global')->setValue('shipping_data', $this->proxyOrder->getShippingData());

        //TODO: need implement?
        //$this->proxyOrder->initializeShippingMethodDataPretendedToBeSimple();
    }

    //########################################

    protected function initializePaymentMethodData()
    {
        $quotePayment = $this->quote->getPayment();
        $quotePayment->importData($this->proxyOrder->getPaymentData());
    }

    //########################################

    protected function prepareOrderNumber()
    {
        $orderNumber = $this->quote->getReservedOrderId();
        empty($orderNumber) && $orderNumber = $this->quote->getResource()->getReservedOrderId($this->quote);

        if ($this->quote->getResource()->isOrderIncrementIdUsed($orderNumber)) {
            $orderNumber = $this->quote->getResource()->getReservedOrderId($this->quote);
        }

        $this->quote->setReservedOrderId($orderNumber);
    }

    //########################################
}
