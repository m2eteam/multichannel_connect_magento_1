<?php

class M2E_MultichannelConnect_Model_Magento_Payment extends Mage_Payment_Model_Method_Abstract
{
    protected $_code = 'm2epayment';

    protected $_canUseCheckout          = false;
    protected $_canUseInternal          = false;
    protected $_canUseForMultishipping  = false;
    protected $_canRefund               = true;
    protected $_canRefundInvoicePartial = true;

    protected $_infoBlockType = 'MultichannelConnect/adminhtml_magento_payment_info';

    public function assignData($data)
    {
        if ($data instanceof Varien_Object) {
            $data = $data->getData();
        }

        $details = array(
            'payment_method'        => $data['payment_method'],
            'channel_order_id'      => $data['channel_order_id']
        );

        $this->getInfoInstance()->setAdditionalData(
            Mage::helper('core')->jsonEncode($details)
        );

        return $this;
    }
}
