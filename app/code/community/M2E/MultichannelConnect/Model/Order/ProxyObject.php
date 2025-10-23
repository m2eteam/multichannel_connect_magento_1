<?php

class M2E_MultichannelConnect_Model_Order_ProxyObject
{
    const CHECKOUT_GUEST = 'guest';

    /** @var M2E_MultichannelConnect_Model_Currency */
    private $currency;

    /** @var M2E_MultichannelConnect_Model_Magento_Payment */
    private $payment;

    /** @var M2E_MultichannelConnect_Model_OrderInformation */
    private $orderInformation;

    /** @var Mage_Core_Model_Store */
    private $store;

    /** @var array */
    private $shippingAddressData = array();

    /** @var array */
    private $billingAddressData = array();

    /** @var M2E_MultichannelConnect_Model_Magento_Shipping */
    private $shipping;

    /** @var M2E_MultichannelConnect_Model_Order_ProxyObject_RegionResolver */
    private $regionResolver;

    public function __construct(
        M2E_MultichannelConnect_Model_OrderInformation $orderInformation
    ) {
        $this->orderInformation = $orderInformation;
        $this->regionResolver = Mage::getModel('MultichannelConnect/Order_ProxyObject_RegionResolver');
        $this->currency = Mage::getModel('MultichannelConnect/Currency');
        $this->payment = Mage::getModel('MultichannelConnect/Magento_Payment');
        $this->shipping = Mage::getModel('MultichannelConnect/Magento_Shipping');
    }

    /**
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->payment->getCode();
    }

    /**
     * @return string
     */
    public function getCarrierCode()
    {
        return $this->shipping->getCarrierCode();
    }

    /**
     * @return string
     */
    public function getShippingMethodCode()
    {
        return $this->shipping->getShippingMethodCode();
    }

    /**
     * @return M2E_MultichannelConnect_Model_Order_OrderItem[]
     */
    public function getItems()
    {
        return $this->orderInformation->getOrderItems();
    }

    /**
     * @param Mage_Core_Model_Store $store
     *
     * @return $this
     */
    public function setStore(Mage_Core_Model_Store $store)
    {
        $this->store = $store;

        return $this;
    }

    /**
     * @return Mage_Core_Model_Store
     * @throws \Exception
     */
    public function getStore()
    {
        if (!isset($this->store)) {
            throw new \Exception('Store is not set.');
        }

        return $this->store;
    }

    /**
     * @return string
     */
    public function getCheckoutMethod()
    {
        return self::CHECKOUT_GUEST;
    }

    /**
     * @return bool
     */
    public function isCheckoutMethodGuest()
    {
        return $this->getCheckoutMethod() == self::CHECKOUT_GUEST;
    }

    /**
     * @return string
     */
    public function getChannelOrderNumber()
    {
        return $this->orderInformation->getChannelOrderId();
    }

    public function getCustomerFirstName()
    {
        return $this->orderInformation->getShippingAddress()->getFirstname();
    }

    public function getCustomerLastName()
    {
        return $this->orderInformation->getShippingAddress()->getLastname();
    }

    public function getBuyerEmail()
    {
        return $this->orderInformation->getShippingAddress()->getEmail();
    }

    /**
     * @return array
     */
    public function getShippingAddress()
    {
        if (empty($this->shippingAddressData)) {
            $shippingAddress = $this->orderInformation->getShippingAddress();
            $this->shippingAddressData['firstname'] = $shippingAddress->getFirstname();
            $this->shippingAddressData['lastname'] = $shippingAddress->getLastname();
            $this->shippingAddressData['email'] = $shippingAddress->getEmail();
            $this->shippingAddressData['country_id'] = $shippingAddress->getCountryId();
            $this->shippingAddressData['region'] = $shippingAddress->getRegion();
            $this->shippingAddressData['region_id'] = $this->regionResolver->getRegionIdByName(
                $shippingAddress->getCountryId(),
                $shippingAddress->getRegion()
            );
            $this->shippingAddressData['city'] = $shippingAddress->getCity();
            $this->shippingAddressData['postcode'] = $shippingAddress->getPostcode();
            $this->shippingAddressData['telephone'] = $shippingAddress->getTelephone();
            $this->shippingAddressData['street'] = $shippingAddress->getStreet();
            $this->shippingAddressData['save_in_address_book'] = 0;
        }

        return $this->shippingAddressData;
    }

    /**
     * @return array
     */
    public function getBillingAddressData()
    {
        if (empty($this->billingAddressData)) {

            $billingAddress = $this->orderInformation->getShippingAddress();
            $this->billingAddressData['firstname'] = $billingAddress->getFirstname();
            $this->billingAddressData['lastname'] = $billingAddress->getLastname();
            $this->billingAddressData['email'] = $billingAddress->getEmail();
            $this->billingAddressData['country_id'] = $billingAddress->getCountryId();
            $this->billingAddressData['region'] = $billingAddress->getRegion();
            $this->billingAddressData['region_id'] = $this->regionResolver->getRegionIdByName(
                $billingAddress->getCountryId(),
                $billingAddress->getRegion()
            );
            $this->billingAddressData['city'] = $billingAddress->getCity();
            $this->billingAddressData['postcode'] = $billingAddress->getPostcode();
            $this->billingAddressData['telephone'] = $billingAddress->getTelephone();
            $this->billingAddressData['street'] = $billingAddress->getStreet();
            $this->billingAddressData['save_in_address_book'] = 0;
        }

        return $this->billingAddressData;
    }

    /**
     * @return bool
     */
    public function shouldIgnoreBillingAddressValidation()
    {
        return false;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->orderInformation->getCurrency();
    }

    public function convertPrice($price)
    {
        return $this->currency->convertPrice($price, $this->getCurrency(), $this->getStore());
    }

    public function convertPriceToBase($price)
    {
        return $this->currency->convertPriceToBaseCurrency($price, $this->getCurrency(), $this->getStore());
    }

    /**
     * @return array
     */
    public function getPaymentData()
    {
        return array(
            'method'                => $this->payment->getCode(),
            'payment_method'        => '',
            'channel_order_id'      => $this->orderInformation->getChannelOrderId()
        );
    }

    /**
     * @return array
     */
    public function getShippingData()
    {
        return array(
            'carrier_title' => (string)__(
                'M2E Cloud Connector'
            ),
            'shipping_method' => $this->shipping->getShippingMethodCode(),
            'shipping_price' => $this->getBaseShippingPrice(),
        );
    }

    private function getBaseShippingPrice()
    {
        return $this->convertPriceToBase($this->getShippingPrice());
    }

    /**
     * @return float
     */
    private function getShippingPrice()
    {
        return $this->orderInformation->getTotals()->getShipping();
    }

    /**
     * @return bool
     */
    public function hasTax()
    {
        return $this->getTaxRate() > 0;
    }

    /**
     * @return int|float
     */
    public function getTaxRate()
    {
        /** @var M2E_MultichannelConnect_Model_Order_Tax_ProductTaxRate $taxModel */
        $taxModel = new M2E_MultichannelConnect_Model_Order_Tax_ProductTaxRate(
            $this->orderInformation->getTax()->getTotal(),
            $this->orderInformation->getTotals()->getSubtotal(),
            false//TODO: rounding will implement
        );

        return $taxModel->getValue();
    }

    // ---------------------------------------

    /**
     * @return float|int
     */
    public function getProductPriceTaxRate()
    {
        if (!$this->hasTax()) {
            return 0;
        }

        return $this->getTaxRate();
    }

    /**
     * @return M2E_MultichannelConnect_Model_Order_Tax_ProductTaxRate
     */
    public function getProductPriceTaxRateObject()
    {
        return new M2E_MultichannelConnect_Model_Order_Tax_ProductTaxRate(
            $this->orderInformation->getTax()->getTotal(),
            $this->orderInformation->getTotals()->getSubtotal(),
            false//TODO: rounding will implement
        );
    }

    /**
     * @return float|int
     */
    public function getShippingPriceTaxRate()
    {
        if (!$this->hasTax()) {
            return 0;
        }

        if (!$this->orderInformation->getTax()->getShippingTax()) {
            return 0;
        }

        return $this->getProductPriceTaxRate();
    }

    public function getShippingPriceTaxRateObject()
    {
        return null;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isProductPriceIncludeTax()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isShippingPriceIncludeTax()
    {
        //Taxes from chanel
        return true;
    }

    /**
     * @return bool
     */
    public function isTaxModeNone()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isTaxModeChannel()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isTaxModeMagento()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isTaxModeMixed()
    {
        return false;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getComments()
    {
        return array_merge($this->getGeneralComments(), $this->getChannelComments());
    }

    /**
     * @return array
     */
    public function getChannelComments()
    {
        return array();
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getGeneralComments()
    {
        $store = $this->getStore();
        $currencyConvertRate = $this->currency->getConvertRateFromBase($this->getCurrency(), $store, 4);

        if ($this->currency->isBase($this->getCurrency(), $store)) {
            return array();
        }

        $comments = array();

        if (!$this->currency->isAllowed($this->getCurrency(), $store)) {
            $comments[] = (string)__(
                '<b>Attention!</b> The Order Prices are incorrect. Conversion was not ' .
                'performed as "%order_currency" Currency is not enabled. Default ' .
                'Currency "%store_currency" was used instead. Please, ' .
                'enable Currency in System > Configuration > Currency Setup.',
                array(
                    'order_currency' => $this->getCurrency(),
                    'store_currency' => $store->getBaseCurrencyCode(),
                )
            );
        } elseif ($currencyConvertRate == 0) {
            $comments[] = __(
                '<b>Attention!</b> The Order Prices are incorrect. Conversion was not ' .
                'performed as there\'s no rate for "%order_currency". Default Currency ' .
                '"%store_currency" was used instead. Please, add Currency convert ' .
                'rate in System > Manage Currency > Rates.',
                array(
                    'order_currency' => $this->getCurrency(),
                    'store_currency' => $store->getBaseCurrencyCode(),
                )
            );
        } else {
            $comments[] = __(
                'Because the Order Currency is different from the Store Currency, the conversion ' .
                'from <b>"%order_currency" to "%store_currency"</b> was performed ' .
                'using <b>%currency_rate</b> as a rate.',
                array(
                    'order_currency' => $this->getCurrency(),
                    'store_currency' => $store->getBaseCurrencyCode(),
                    'currency_rate' => $currencyConvertRate,
                )
            );
        }

        return $comments;
    }
}
