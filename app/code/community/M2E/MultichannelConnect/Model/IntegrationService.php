<?php

class M2E_MultichannelConnect_Model_IntegrationService
{
    const REST_ROLE_ID_CONFIG_PATH = 'm2e/multichannelconnect/role_id';
    const CONSUMER_ID_CONFIG_PATH = 'm2e/multichannelconnect/consumer_id';

    public function integrationExists()
    {
        try {
            $this->getIntegrationId();
        } catch (Exception $e) {
            return false;
        }

        try {
            $this->getM2ERole();
        } catch (Exception $e) {
            return false;
        }

        try {
            $this->getIntegration();
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @return Mage_Oauth_Model_Consumer
     * @throws Exception
     */
    public function getIntegration()
    {
        $consumerId = $this->getIntegrationId();
        $consumer = Mage::getModel('oauth/consumer')->load($consumerId);
        if(!$consumer->getId()){
            throw new Exception('Integration consumer not found.');
        }

        return $consumer;
    }

    public function integrationCreate()
    {
        $role = $this->createRestApiRole();
        $this->createAdminAcl();
        $consumer = $this->createConsumer();

        $this->insertConfigData($role->getId(), $consumer->getId());
        Mage::getModel('core/config')->cleanCache();

        return $role;
    }

    /**
     * @return Mage_Api2_Model_Acl_Global_Role
     * @throws Exception
     */
    public function getM2ERole()
    {
        $role = Mage::getModel('api2/acl_global_role')->load(
            Mage::getStoreConfig(self::REST_ROLE_ID_CONFIG_PATH)
        );

        if (!$role->getId()) {
            throw new Exception('REST role not found');
        }

        return $role;
    }

    /**
     * @return int
     * @throws Exception
     */
    private function getIntegrationId()
    {
        $consumerId = Mage::getStoreConfig(self::CONSUMER_ID_CONFIG_PATH);
        if (!$consumerId) {
            throw new Exception(
                Mage::helper('MultichannelConnect')->__(
                    'The M2E Cloud Connector Integration has not been installed yet.'
                ));
        }

        return (int)$consumerId;
    }

    private function createRestApiRole()
    {
        $role = Mage::getModel('api2/acl_global_role')
            ->setData(array('role_name' => 'M2E Multichannel Connect'))
            ->save();

        $rules = array(
            array('resource' => 'm2e_module', 'privilege' => 'retrieve'),
            array('resource' => 'm2e_product_attributes', 'privilege' => 'create'),
            array('resource' => 'm2e_category', 'privilege' => 'retrieve'),
            array('resource' => 'm2e_order', 'privilege' => 'create'),
            array('resource' => 'm2e_order_cancel', 'privilege' => 'create'),
            array('resource' => 'm2e_order_shipment', 'privilege' => 'create'),
            array('resource' => 'm2e_order_shipment', 'privilege' => 'retrieve'),
            array('resource' => 'm2e_order_track', 'privilege' => 'create'),
            array('resource' => 'm2e_order_track', 'privilege' => 'retrieve'),
            array('resource' => 'm2e_order_track', 'privilege' => 'update'),
            array('resource' => 'm2e_product', 'privilege' => 'retrieve'),
            array('resource' => 'm2e_stock_item', 'privilege' => 'retrieve'),
            array('resource' => 'order', 'privilege' => 'retrieve'),
            array('resource' => 'order_address', 'privilege' => 'retrieve'),
            array('resource' => 'order_comment', 'privilege' => 'retrieve'),
            array('resource' => 'order_item', 'privilege' => 'retrieve'),
            array('resource' => 'product', 'privilege' => 'retrieve'),
            array('resource' => 'product_category', 'privilege' => 'retrieve'),
            array('resource' => 'product_image', 'privilege' => 'retrieve'),
            array('resource' => 'stock_item', 'privilege' => 'retrieve')
        );

        foreach ($rules as $ruleData) {
            Mage::getModel('api2/acl_global_rule')
                ->setRoleId($role->getId())
                ->setResourceId($ruleData['resource'])
                ->setPrivilege($ruleData['privilege'])
                ->save();
        }

        return $role;
    }

    private function createAdminAcl()
    {
        $userType = 'admin';
        $resourceId = 'all';

        $model = Mage::getModel('api2/acl_filter_attribute')->getCollection()
            ->addFieldToFilter('user_type', $userType)
            ->addFieldToFilter('resource_id', $resourceId)
            ->getFirstItem();

        if (!$model || !$model->getId()) {
            Mage::getModel('api2/acl_filter_attribute')
                ->setUserType($userType)
                ->setResourceId($resourceId)
                ->save();
        }
    }

    private function createConsumer()
    {
        /** @var $helper Mage_Oauth_Helper_Data */
        $helper = Mage::helper('oauth');

        $consumer = Mage::getModel('oauth/consumer');
        $consumer->setName('M2E Multichannel Connect')
            ->setKey($helper->generateConsumerKey())
            ->setSecret($helper->generateConsumerSecret())
            ->save();

        return $consumer;
    }

    private function insertConfigData($roleId, $consumerId)
    {
        Mage::getModel('core/config')->saveConfig(self::REST_ROLE_ID_CONFIG_PATH, $roleId);
        Mage::getModel('core/config')->saveConfig(self::CONSUMER_ID_CONFIG_PATH, $consumerId);
    }
}
