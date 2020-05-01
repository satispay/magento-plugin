<?php

require_once(dirname(__FILE__) . '/../../includes/gbusiness-api-php-sdk/init.php');

class Satispay_Satispay_Adminhtml_KeysgeneratorController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Return some checking result
     *
     * @return void
     */
    public function generatekeysAction()
    {
        $helper = Mage::helper('satispay');
        $logger = Mage::getModel('satispay/logger', array($helper->debugModeEnable()));

        $isSandbox = $helper->isSandbox();
        if (empty($helper->getToken($isSandbox))) {
            return Mage::app()->getResponse()->setBody(__('Insert the six characters activation code.'));
        }

        try {
            \SatispayGBusiness\Api::setSandbox($isSandbox);
            $authentication = \SatispayGBusiness\Api::authenticateWithToken($helper->getToken($isSandbox));
            $publicKey = $authentication->publicKey;
            $helper->setPublicKey($publicKey);
            $privateKey = $authentication->privateKey;
            $helper->setPrivateKey($privateKey);
            $keyId = $authentication->keyId;
            $helper->setKeyId($keyId, $isSandbox);
            Mage::getModel('core/config')->cleanCache();
        } catch (Exception $e) {
            $logger->exception($e);
            return Mage::app()->getResponse()->setBody(__('Activation code invalid or already used.'));
        }

        $result = 0;
        if (isset($publicKey) && isset($privateKey) && isset($keyId)) {
            $result = 'Satispay activated correctly';
        }

        Mage::app()->getResponse()->setBody($result);
    }
}
