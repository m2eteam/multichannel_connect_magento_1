<?php

class M2E_MultichannelConnect_Model_Magento_Quote_CustomerFinder
{
    /**
     * @param string $email
     * @param int $websiteId
     * @return Mage_Customer_Model_Customer
     */
    public function findByEmail($email, $websiteId)
    {
        $customer = Mage::getModel('customer/customer')
            ->setWebsiteId($websiteId)
            ->loadByEmail($email);

        if ($customer->getId() !== null) {
            return $customer;
        }

        return null;
    }
}
