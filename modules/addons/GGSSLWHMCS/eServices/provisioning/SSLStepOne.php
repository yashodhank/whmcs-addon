<?php

namespace MGModule\GGSSLWHMCS\eServices\provisioning;

use Exception;

class SSLStepOne {

    private $p;

    function __construct(&$params) {        
        $this->p = &$params;
    }

    public function run() {
        try {            
            return $this->SSLStepOne();
        } catch (Exception $e) {
            \MGModule\GGSSLWHMCS\eServices\FlashService::setStepOneError($this->getErrorForClient());
        }
    }

    private function SSLStepOne() {    
        
        $fields['additionalfields'] = [];
        $apiProductId  = $this->p[ConfigOptions::API_PRODUCT_ID];
        $apiRepo       = new \MGModule\GGSSLWHMCS\eRepository\gogetssl\Products();
        $apiProduct    = $apiRepo->getProduct($apiProductId);
        $apiWebServers = \MGModule\GGSSLWHMCS\eRepository\gogetssl\WebServers::getAll($apiProduct->getWebServerTypeId());

        $apiWebServersJSON         = json_encode($apiWebServers);
        $fillVarsJSON              = json_encode(\MGModule\GGSSLWHMCS\eServices\FlashService::getFieldsMemory($_GET['cert']));
        $sanEnabledForWHMCSProduct = $this->p[ConfigOptions::PRODUCT_ENABLE_SAN] === 'on';

        $includedSans = (int) $this->p[ConfigOptions::PRODUCT_INCLUDED_SANS];
        $boughtSans   = (int) $this->p['configoptions'][ConfigOptions::OPTION_SANS_COUNT];
        
        $orderTypes = ['new', 'renew'];
        
        $sansLimit    = $includedSans + $boughtSans;        

        
        $apiConf = (new \MGModule\GGSSLWHMCS\models\apiConfiguration\Repository())->get();        
        $displayCsrGenerator = $apiConf->display_csr_generator;    
        
        if (!$sanEnabledForWHMCSProduct) {
            $sansLimit = 0;
        } 
        //$fields['additionalfields'][\MGModule\GGSSLWHMCS\eRepository\gogetssl\OrderType::getTitle()] = \MGModule\GGSSLWHMCS\eRepository\gogetssl\OrderType::getFields();
        
        if ($sansLimit > 0) {
            $fields['additionalfields'][\MGModule\GGSSLWHMCS\eRepository\gogetssl\San::getTitle()] = \MGModule\GGSSLWHMCS\eRepository\gogetssl\San::getFields($sansLimit);
        }
        if ($apiProduct->isOrganizationRequired()) {
            $fields['additionalfields'][\MGModule\GGSSLWHMCS\eRepository\gogetssl\Organization::getTitle()] = \MGModule\GGSSLWHMCS\eRepository\gogetssl\Organization::getFields();
        }
        $countriesForGenerateCsrForm = \MGModule\GGSSLWHMCS\eRepository\whmcs\config\Countries::getInstance()->getCountriesForMgAddonDropdown(); 
        
        $stepOneBaseScript    = \MGModule\GGSSLWHMCS\eServices\ScriptService::getStepOneBaseScript($apiProduct->brand);
        $orderTypeScript    = \MGModule\GGSSLWHMCS\eServices\ScriptService::getOrderTypeScript($orderTypes, $fillVarsJSON);
        $webServerTypeSctipt  = \MGModule\GGSSLWHMCS\eServices\ScriptService::getWebServerTypeSctipt($apiWebServersJSON);
        $autoFillFieldsScript = \MGModule\GGSSLWHMCS\eServices\ScriptService::getAutoFillFieldsScript($fillVarsJSON);        
        $generateCsrModalScript = ($displayCsrGenerator) ? \MGModule\GGSSLWHMCS\eServices\ScriptService::getGenerateCsrModalScript($fillVarsJSON, $countriesForGenerateCsrForm) : '';
        //when server type is not selected exception
        if(isset($_POST['privateKey']) && $_POST['privateKey'] != null && empty(json_decode($fillVarsJSON))) {
            $autoFillPrivateKeyField = \MGModule\GGSSLWHMCS\eServices\ScriptService::getAutoFillPrivateKeyField($_POST['privateKey']);
        }
        //auto fill order type field
        if(isset($_POST['fields']['order_type']) && $_POST['fields']['order_type'] != null) {
            $autoFillOrderTypeField = \MGModule\GGSSLWHMCS\eServices\ScriptService::getAutoFillOrderTypeField($_POST['fields']['order_type']);
        }
        
        $fields['additionalfields']['<br />']['<br />'] = [
            'Description' => $stepOneBaseScript . $webServerTypeSctipt . $orderTypeScript . $autoFillFieldsScript . $generateCsrModalScript .$autoFillPrivateKeyField . $autoFillOrderTypeField,
        ];

        return $fields;

    }
    private function getErrorForClient() {
        return \MGModule\GGSSLWHMCS\mgLibs\Lang::getInstance()->T('canNotFetchWebServer');

    }  
}
