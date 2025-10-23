<?php

class M2E_MultichannelConnect_Helper_Data_Global extends Mage_Core_Helper_Abstract
{
    public function getValue($key)
    {
        $globalKey = M2E_MultichannelConnect_Helper_Data::CUSTOM_IDENTIFIER.'_'.$key;

        return Mage::registry($globalKey);
    }

    public function setValue($key, $value)
    {
        $globalKey = M2E_MultichannelConnect_Helper_Data::CUSTOM_IDENTIFIER.'_'.$key;
        Mage::register($globalKey, $value);
    }

    public function unsetValue($key)
    {
        $globalKey = M2E_MultichannelConnect_Helper_Data::CUSTOM_IDENTIFIER.'_'.$key;
        Mage::unregister($globalKey);
    }
}
