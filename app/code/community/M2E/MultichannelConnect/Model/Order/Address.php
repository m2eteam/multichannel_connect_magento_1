<?php
class M2E_MultichannelConnect_Model_Order_Address extends Varien_Object
{
    const KEY_EMAIL = 'email';
    const KEY_COUNTRY_ID = 'country_id';
    const KEY_REGION = 'region';
    const KEY_STREET = 'street';
    const KEY_TELEPHONE = 'telephone';
    const KEY_POSTCODE = 'postcode';
    const KEY_CITY = 'city';
    const KEY_FIRSTNAME = 'firstname';
    const KEY_LASTNAME = 'lastname';

    /**
     * Get country id
     * @return string
     */
    public function getCountryId()
    {
        return $this->getData(self::KEY_COUNTRY_ID);
    }

    /**
     * Get street
     * @return string[]
     */
    public function getStreet()
    {
        $street = $this->getData(self::KEY_STREET) ?: array('');

        return is_array($street) ? $street : explode("\n", $street);
    }

    /**
     * Get telephone number
     * @return string
     */
    public function getTelephone()
    {
        return $this->getData(self::KEY_TELEPHONE);
    }

    /**
     * Get postcode
     * @return string
     */
    public function getPostcode()
    {
        return $this->getData(self::KEY_POSTCODE);
    }

    /**
     * Get city name
     * @return string
     */
    public function getCity()
    {
        return $this->getData(self::KEY_CITY);
    }

    /**
     * Get first name
     * @return string
     */
    public function getFirstname()
    {
        return $this->getData(self::KEY_FIRSTNAME);
    }

    /**
     * Get last name
     * @return string
     */
    public function getLastname()
    {
        return $this->getData(self::KEY_LASTNAME);
    }

    /**
     * Get billing/shipping email
     * @return string
     */
    public function getEmail()
    {
        return $this->getData(self::KEY_EMAIL);
    }

    /**
     * Get region name
     * @return string
     */
    public function getRegion()
    {
        return $this->getData(self::KEY_REGION);
    }
}
