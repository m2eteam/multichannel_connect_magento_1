<?php

class M2E_MultichannelConnect_Block_Adminhtml_Api2_Role extends Mage_Api2_Block_Adminhtml_Roles_Tab_Resources
{
    /**
     * Get Json Representation of Resource Tree
     *
     * @return string
     */
    public function getResTreeJson()
    {
        $resultJson = parent::getResTreeJson();
        $helper = Mage::helper('core');
        $result = $helper->jsonDecode($resultJson);

        return $helper->jsonEncode(
            $this->filterAndDisable($result)
        );
    }

    /**
     * @param array $items
     * @return array
     */
    private function filterAndDisable($items)
    {
        $result = array();
        foreach ($items as $item) {
            if (!empty($item['checked'])) {
                $item['disabled'] = true;
                if (!empty($item['children'])) {
                    $item['children'] = $this->filterAndDisable($item['children']);
                }
                $result[] = $item;
            }
        }
        return $result;
    }
}
