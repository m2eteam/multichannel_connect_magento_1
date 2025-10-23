<?php
class M2E_MultichannelConnect_Model_Order_MagentoProcessor_InvoiceCreate
{
    /**
     * @param Mage_Sales_Model_Order $order
     * @return void
     */
    public function process(Mage_Sales_Model_Order $order)
    {
        if (!$this->canCreateInvoice($order)) {
            return;
        }
        $invoice = $order->prepareInvoice();
        $invoice->register();
        $invoice->getOrder()->setIsInProcess(true);

        Mage::getModel('core/resource_transaction')
            ->addObject($invoice)
            ->addObject($invoice->getOrder())
            ->save();
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return bool
     */
    private function canCreateInvoice(Mage_Sales_Model_Order $order)
    {
        return $order->canInvoice();
    }
}
