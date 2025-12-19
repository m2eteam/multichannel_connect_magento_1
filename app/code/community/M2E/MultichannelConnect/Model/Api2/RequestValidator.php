<?php

class M2E_MultichannelConnect_Model_Api2_RequestValidator
{
    /**
     * @param Varien_Data_Collection $collection
     * @param int|null $requestedPage
     * @return bool
     */
    public static function isRequestedPageNumberValid($collection, $requestedPage)
    {
        return $requestedPage && $collection->getLastPageNumber() >= $requestedPage;
    }
}
