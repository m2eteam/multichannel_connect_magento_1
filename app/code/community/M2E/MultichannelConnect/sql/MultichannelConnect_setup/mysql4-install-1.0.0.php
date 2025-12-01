<?php
/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/** @var M2E_MultichannelConnect_Model_IntegrationService $integrationService */
$integrationService = Mage::getModel('MultichannelConnect/IntegrationService');
$integrationService->integrationCreate();

$installer->endSetup();
