<?php
class M2E_MultichannelConnect_Model_Order_ProxyObject_RegionResolver
{
    /** @var Mage_Directory_Model_Region */
    private $region;

    /** @var Mage_Directory_Model_Country */
    private $country;

    /**
     * @param string $countryId
     * @param string $regionName
     * @return string|null
     * @throws Exception
     */
    public function getRegionIdByName($countryId, $regionName)
    {
        $country = $this->getCountry($countryId);
        if (!$country->getId()) {
            throw new Exception('Country not found.');
        }

        if ($this->region === null) {
            $countryRegions = $country->getRegionCollection();
            $countryRegions->getSelect()->where('code = ? OR default_name = ?', $regionName);
            $this->region = $countryRegions->getFirstItem();
        }

        $isRegionRequired = Mage::helper('directory')->isRegionRequired($country->getId());
        if ($isRegionRequired && !$this->region->getId()) {
            throw new Exception(
                sprintf('Invalid Region/State value "%s" in the Shipping Address.', $regionName)
            );
        }

        return $this->region->getId();
    }

    /**
     * @param $countryId
     * @return Mage_Directory_Model_Country
     */
    public function getCountry($countryId)
    {
        if ($this->country === null) {
            $this->country = Mage::getModel('directory/country');

            try {
                $this->country->loadByCode($countryId);
            } catch (Exception $e) {
            }
        }

        return $this->country;
    }
}
