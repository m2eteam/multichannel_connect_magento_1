<?php
/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();

$roleName = 'M2E Multichannel Connect';
$resourceId = 'store_views';
$privilege = 'retrieve';

/** @var Mage_Api2_Model_Acl_Global_Role $role */
$role = Mage::getModel('api2/acl_global_role')->load($roleName, 'role_name');

if ($role && $role->getId()) {
    $adapter = $installer->getConnection();
    $tableName = $installer->getTable('api2/acl_rule');

    $select = $adapter->select()
        ->from($tableName)
        ->where('role_id = ?', $role->getId())
        ->where('resource_id = ?', $resourceId)
        ->where('privilege = ?', $privilege);

    if (!$adapter->fetchOne($select)) {
        $adapter->insert($tableName, array(
            'role_id'     => $role->getId(),
            'resource_id' => $resourceId,
            'privilege'   => $privilege,
        ));
    }
}

$installer->endSetup();
