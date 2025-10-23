<?php

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

// === 1. Create REST role ===
$role = Mage::getModel('api2/acl_global_role')
    ->setData(array('role_name' => 'M2E Multichannel Connect'))
    ->save();

// === 2. Create rules for the role ===
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

// === 3. Create attribute filter ===
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

// === 4. Create OAuth consumer ===
/** @var $helper Mage_Oauth_Helper_Data */
$helper = Mage::helper('oauth');

$consumer = Mage::getModel('oauth/consumer');
$consumer->setName('M2E Multichannel Connect')
    ->setKey($helper->generateConsumerKey())
    ->setSecret($helper->generateConsumerSecret())
    ->save();

// === 5. Set store configs ===
$installer->setConfigData(M2E_MultichannelConnect_Model_Module::REST_ROLE_ID_CONFIG_PATH, $role->getId());
$installer->setConfigData(M2E_MultichannelConnect_Model_Module::CONSUMER_ID_CONFIG_PATH, $consumer->getId());

$installer->endSetup();
