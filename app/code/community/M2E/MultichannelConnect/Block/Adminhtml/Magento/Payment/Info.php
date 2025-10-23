<?php

class M2E_MultichannelConnect_Block_Adminhtml_Magento_Payment_Info extends Mage_Payment_Block_Info
{
    protected $_order = null;

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('MultichannelConnect/magento/order/payment/info.phtml');
    }

    protected function getAdditionalData($key = '')
    {
        $additionalData = Mage::helper('core')->jsonDecode($this->getInfo()->getAdditionalData());

        if ($key === '') {
            return $additionalData;
        }

        return isset($additionalData[$key]) ? $additionalData[$key] : null;
    }

    /**
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if ($this->_order === null) {
            // do not replace registry with our wrapper
            if ($this->hasOrder()) {
                $this->_order = $this->getOrder();
            } elseif (Mage::registry('current_order')) {
                $this->_order = Mage::registry('current_order');
            } elseif (Mage::registry('order')) {
                $this->_order = Mage::registry('order');
            } elseif (Mage::registry('current_invoice')) {
                $this->_order = Mage::registry('current_invoice')->getOrder();
            } elseif (Mage::registry('current_shipment')) {
                $this->_order = Mage::registry('current_shipment')->getOrder();
            } elseif (Mage::registry('current_creditmemo')) {
                $this->_order = Mage::registry('current_creditmemo')->getOrder();
            }
        }

        return $this->_order;
    }

    public function getPaymentMethod()
    {
        return (string)$this->getAdditionalData('payment_method');
    }

    public function getChannelOrderId()
    {
        return (string)$this->getAdditionalData('channel_order_id');
    }

    protected function _toHtml()
    {
        // Start store emulation process
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation(
            Mage_Core_Model_App::ADMIN_STORE_ID, Mage_Core_Model_App_Area::AREA_ADMINHTML
        );

        $html = parent::_toHtml();

        // Stop store emulation process
        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

        return $html;
    }
}
