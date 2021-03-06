<?php

namespace MGModule\SSLCENTERWHMCS\eServices\provisioning;

use Exception;

class AdminResendApproverEmail {

    private $p;
    
    function __construct(&$params) {
        $this->p = &$params;
    }

    public function run() {
        try {
            $this->adminResendApproverEmail();
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
        return 'success';
    }
    
    private function adminResendApproverEmail() {
        $ssl = new \MGModule\SSLCENTERWHMCS\eRepository\whmcs\service\SSL();
        $serviceSSL = $ssl->getByServiceId($this->p['serviceid']);
        
        if (is_null($serviceSSL)) {
            throw new Exception('Create has not been initialized.');
        }
  
        \MGModule\SSLCENTERWHMCS\eProviders\ApiProvider::getInstance()->getApi()->resendEmail($serviceSSL->remoteid);
    }
}
