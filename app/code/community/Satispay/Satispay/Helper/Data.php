<?php

/**
 * Class Satispay_Satispay_Helper_Data
 */
class Satispay_Satispay_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @param null $storeId
     * @return mixed
     */
    public function isActive($storeId = null)
    {
        return Mage::getStoreConfig('payment/satispay/active', $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function isSandbox($storeId = null)
    {
        return Mage::getStoreConfigFlag('payment/satispay/sandbox', $storeId);
    }

    public function isActivated($storeId = null)
    {
        $isSandbox = $this->isSandbox($storeId);
        if (empty($this->getPrivateKey()) || empty($this->getPublicKey()) || empty($this->getKeyId($isSandbox))) {
            return false;
        }

        return true;
    }

    /**
     * @param null $storeId
     * @param $isSandbox
     * @return mixed
     */
    public function getToken($isSandbox, $storeId = null)
    {
        if ($isSandbox) {
            return Mage::getStoreConfig('payment/satispay/token_sandbox', $storeId);
        }
        return Mage::getStoreConfig('payment/satispay/token', $storeId);
    }

    /**
     * @param $val
     */
    public function setPublicKey($val)
    {
        $encryptedValue = Mage::helper('core')->encrypt($val);
        Mage::getConfig()->saveConfig('payment/satispay/public_key', $encryptedValue, 'default', 0);
    }

    /**
     * @param $val
     */
    public function setPrivateKey($val)
    {
        $encryptedValue = Mage::helper('core')->encrypt($val);
        Mage::getConfig()->saveConfig('payment/satispay/private_key', $encryptedValue, 'default', 0);
    }

    /**
     * @param $val
     * @param $isSandbox
     */
    public function setKeyId($val, $isSandbox)
    {
        $encryptedValue = Mage::helper('core')->encrypt($val);
        if ($isSandbox) {
            Mage::getConfig()->saveConfig('payment/satispay/key_id_sandbox', $encryptedValue, 'default', 0);
        } else {
            Mage::getConfig()->saveConfig('payment/satispay/key_id', $encryptedValue, 'default', 0);
        }
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getPublicKey($storeId = null)
    {
        return Mage::helper('core')->decrypt(Mage::getStoreConfig('payment/satispay/public_key', $storeId));
    }

    /**
     * @param null $storeId
     * @param $isSandbox
     * @return mixed
     */
    public function getPrivateKey($storeId = null)
    {
        return Mage::helper('core')->decrypt(Mage::getStoreConfig('payment/satispay/private_key', $storeId));
    }

    /**
     * @param null $storeId
     * @param $isSandbox
     * @return mixed
     */
    public function getKeyId($isSandbox, $storeId = null)
    {
        if ($isSandbox) {
            return Mage::helper('core')->decrypt(Mage::getStoreConfig('payment/satispay/key_id_sandbox', $storeId));
        }
        return Mage::helper('core')->decrypt(Mage::getStoreConfig('payment/satispay/key_id', $storeId));
    }

    /**
     * @param null $storeId
     * @return boolean
     */
    public function debugModeEnable($storeId = null)
    {
        return Mage::getStoreConfigFlag('payment/satispay/debug_mode', $storeId);
    }

    public function getExtensionVersion()
    {
        return (string)Mage::getConfig()->getNode()->modules->Satispay_Satispay->version;
    }
}
