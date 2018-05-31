<?php

namespace MGModule\GGSSLWHMCS\eServices\provisioning;

use Exception;

class SSLStepTwoJS {

    private $p;
    private $domainsEmailApprovals = [];
    private $brand = '';

    function __construct(&$params) {
        $this->p = &$params;
    }

    public function run() {
        
        if (!$this->canRun()) {
            return '';
        }

        if (!$this->isValidModule()) {
            return '';
        }
        try {
            $this->setBrand($_POST);
            $this->SSLStepTwoJS();
            
            return \MGModule\GGSSLWHMCS\eServices\ScriptService::getSanEmailsScript(json_encode($this->domainsEmailApprovals), json_encode(\MGModule\GGSSLWHMCS\eServices\FlashService::getFieldsMemory($_GET['cert'])), json_encode($this->brand));
        } catch (Exception $ex) {
            return '';
        }

    }

    private function canRun() {
        if ($this->p['filename'] !== 'configuressl') {
            return false;
        }
        if ($_GET['step'] != 2) {
            return false;
        }
        return true;
    }    

    private function setBrand($params) {
        if(isset($params['sslbrand']) &&  $params['sslbrand'] != null){
            $this->brand = $params['sslbrand'];
        }
    }
    
    private function isValidModule() {
        return \MGModule\GGSSLWHMCS\eRepository\whmcs\service\SSLTemplorary::getInstance()->get($_GET['cert']) === true;

    }

    private function SSLStepTwoJS() {
    
        $decodedCSR   = \MGModule\GGSSLWHMCS\eProviders\ApiProvider::getInstance()->getApi(false)->decodeCSR($_POST['csr']);
        if($decodedCSR['error']) {
            if(isset($decodedCSR['description']))
                throw new Exception($decodedCSR['description']);
            
            throw new Exception(\MGModule\GGSSLWHMCS\mgLibs\Lang::T('incorrectCSR'));
        }
        $mainDomain       = $decodedCSR['csrResult']['CN'];
        $domains = $mainDomain . PHP_EOL . $_POST['fields']['sans_domains'];
        $sansDomains = \MGModule\GGSSLWHMCS\eHelpers\SansDomains::parseDomains(strtolower($domains));
        $this->fetchApprovalEmailsForSansDomains($sansDomains);        
        if(\MGModule\GGSSLWHMCS\eHelpers\Whmcs::isWHMCS73()) {
            if(isset($_POST['privateKey']) && $_POST['privateKey'] != null) {            
                $privKey = decrypt($_POST['privateKey']);
                $GenerateSCR = new \MGModule\GGSSLWHMCS\eServices\provisioning\GenerateCSR($this->p, $_POST);
                $GenerateSCR->savePrivateKeyToDatabase($this->p['serviceid'], $privKey);  
            }
        }
    }

    public function fetchApprovalEmailsForSansDomains($sansDomains) {
        foreach ($sansDomains as $sansDomain) {
            $apiDomainEmails             = \MGModule\GGSSLWHMCS\eProviders\ApiProvider::getInstance()->getApi()->getDomainEmails($sansDomain);
            $this->domainsEmailApprovals[$sansDomain] = $apiDomainEmails['ComodoApprovalEmails'];
        }
        return $this->domainsEmailApprovals;
    }
}
