<?php

require_once(dirname(__FILE__) . '/../../includes/gbusiness-api-php-sdk/init.php');

class Satispay_Satispay_Adminhtml_KeysgeneretorController extends Mage_Adminhtml_Controller_Action
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
        if (!$helper->isActive()) {
            return Mage::app()->getResponse()->setBody('Payment methods is Disabled, please set yes on Active field');
        }
        $sandbox = $helper->isSandbox();
        try {
            if ($helper->getPublicKey(null, $sandbox) && $helper->getPrivateKey(null, $sandbox) && $helper->getKeyId(null, $sandbox)) {
                return Mage::app()->getResponse()->setBody('Activation Code already used, please change your Token and press the button regenerate keys if you want to change your KEYS');
            }
        } catch (Exception $e) {
            $logger->exception($e);
            return Mage::app()->getResponse()->setBody('error');
        }
        try {
            \SatispayGBusiness\Api::setSandbox($sandbox);
            $authentication = \SatispayGBusiness\Api::authenticateWithToken($helper->getToken(null, $sandbox));
            $publicKey = $authentication->publicKey;
            $helper->setPublicKey($publicKey, $sandbox);
            $privateKey = $authentication->privateKey;
            $helper->setPrivateKey($privateKey, $sandbox);
            $keyId = $authentication->keyId;
            $helper->setKeyId($keyId, $sandbox);
            Mage::getModel('core/config')->cleanCache();
        } catch (Exception $e) {
            $logger->exception($e);
            return Mage::app()->getResponse()->setBody('token already used, please change token and generate the new keys');
        }

        $result = 0;
        if (isset($publicKey) && isset($privateKey) && isset($keyId)) {
            $result = 'public key, private key and key id generated ';
        }

        Mage::app()->getResponse()->setBody($result);
    }

    public function regeneratekeysAction()
    {
        $helper = Mage::helper('satispay');
        $logger = Mage::getModel('satispay/logger', array($helper->debugModeEnable()));
        if (!$helper->isActive()) {
            return Mage::app()->getResponse()->setBody('Payment methods is Disabled, please set yes on Active field');
        }
        $sandbox = $helper->isSandbox();
        try {
            \SatispayGBusiness\Api::setSandbox($sandbox);
            $authentication = \SatispayGBusiness\Api::authenticateWithToken($helper->getToken(null, $sandbox));
            $publicKey = $authentication->publicKey;
            $helper->setPublicKey($publicKey, $sandbox);
            $privateKey = $authentication->privateKey;
            $helper->setPrivateKey($privateKey, $sandbox);
            $keyId = $authentication->keyId;
            $helper->setKeyId($keyId, $sandbox);
            Mage::getModel('core/config')->cleanCache();
        } catch (Exception $e) {
            $logger->exeption($e);
            return Mage::app()->getResponse()->setBody('token already used, please change token and generate the new keys');
        }

        $result = 0;
        if (isset($publicKey) && isset($privateKey) && isset($keyId)) {
            $result = 'public key, private key and key id generated ';
        }

        Mage::app()->getResponse()->setBody($result);
    }
}