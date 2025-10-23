<?php

abstract class M2E_MultichannelConnect_Controller_Adminhtml_AbstractController extends Mage_Adminhtml_Controller_Action
{
    const MENU_ID = 'multichannelconnect';

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('multichannelconnect');
    }

    /**
     * @return M2E_MultichannelConnect_Model_Module
     */
    protected function getModule()
    {
        return Mage::getModel('MultichannelConnect/Module');
    }
}
