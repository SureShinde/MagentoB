<?php
class SoapClientWithTimeout extends SoapClient {
    private $timeout;

    public function __setTimeout($timeout) {
        if(!is_int($timeout) && !is_null($timeout)) {
            throw new Exception("Invalid timeout value!");
        }
        $this->timeout = $timeout;
    }

    public function __doRequest($request, $location, $action, $version, $one_way = FALSE) {
        if(!$this->timeout) {
            $response = parent::__doRequest($request, $location, $action, $version, $one_way);
        }
        else {
            $curl = curl_init($location);

            curl_setopt($curl, CURLOPT_VERBOSE, FALSE);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($curl, CURLOPT_POST, TRUE);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
            curl_setopt($curl, CURLOPT_HEADER, FALSE);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: text/xml", "SOAPAction: {$action}"));
            curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);

            $response = curl_exec($curl);

            if (curl_errno($curl)) {
                $ex = new Exception(curl_error($curl));
                Mage::logException($ex);
                throw $ex;
            }
            curl_close($curl);
        }

        if (!$one_way) {
            return ($response);
        }
    }
}