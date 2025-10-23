<?php

class M2E_MultichannelConnect_Model_Connector_Client
{
    /**
     * @param string $url
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function post($url, $data)
    {
        $httpClient = new Varien_Http_Client($url);

        $httpClient->setUri($url);
        $httpClient->setHeaders(array(
            'Content-Type' => 'application/json'
        ));

        $httpClient->setRawData(
            json_encode($data),
            'application/json'
        );

        $response = $httpClient->request(Zend_Http_Client::POST);

        if ($response->getStatus() !== 200) {
            throw new Exception('External API error: ' . $response->getBody());
        }

        return array(
            'status'   => $response->getStatus(),
            'response' => $response->getBody(),
        );
    }
}
