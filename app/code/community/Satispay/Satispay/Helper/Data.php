<?php

/**
 * Class Satispay_Satispay_Helper_Data
 */
class Satispay_Satispay_Helper_Data extends Mage_Core_Helper_Abstract {

    /**
     * @param null $storeId
     * @return mixed
     */
    public function isActive($storeId = null) {
        return Mage::getStoreConfig('payment/satispay/active', $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function isSandbox($storeId = null) {
    return Mage::getStoreConfig('payment/satispay/sandbox', $storeId);
  }

    /**
     * @param null $storeId
     * @param $sandbox
     * @return mixed
     */
    public function getToken($storeId = null, $sandbox) {
        if($sandbox){
            return Mage::getStoreConfig('payment/satispay/token_sandbox', $storeId);
        }
        return Mage::getStoreConfig('payment/satispay/token', $storeId);
    }

    /**
     * @param $val
     * @param $sandbox
     */
    public function setPublicKey($val, $sandbox){
        $encryptedValue =  Mage::helper('core')->encrypt($val);
      if($sandbox){
         Mage::getConfig()->saveConfig('payment/satispay/key_public_sandbox', $encryptedValue, 'default', 0);
      }else {
          Mage::getConfig()->saveConfig('payment/satispay/key_public', $encryptedValue, 'default', 0);
      }
    }

    /**
     * @param $val
     * @param $sandbox
     */
    public function setPrivateKey($val, $sandbox){
        $encryptedValue =  Mage::helper('core')->encrypt($val);
      if($sandbox){
          Mage::getConfig()->saveConfig('payment/satispay/key_private_sandbox', $encryptedValue, 'default', 0);
      }else {
          Mage::getConfig()->saveConfig('payment/satispay/key_private', $encryptedValue, 'default', 0);
      }
    }

    /**
     * @param $val
     * @param $sandbox
     */
    public function setKeyId($val, $sandbox){
        $encryptedValue =  Mage::helper('core')->encrypt($val);
      if($sandbox){
          Mage::getConfig()->saveConfig('payment/satispay/key_id_sandbox', $encryptedValue, 'default', 0);
      }else {
          Mage::getConfig()->saveConfig('payment/satispay/key_id', $encryptedValue, 'default', 0);
      }
    }

    /**
     * @param null $storeId
     * @param $sandbox
     * @return mixed
     */
    public function getPublicKey($storeId = null, $sandbox){
      if($sandbox){
          return Mage::helper('core')->decrypt(Mage::getStoreConfig('payment/satispay/key_public_sandbox', $storeId));
      }
        return Mage::helper('core')->decrypt(Mage::getStoreConfig('payment/satispay/key_public', $storeId));
    }

    /**
     * @param null $storeId
     * @param $sandbox
     * @return mixed
     */
    public function getPrivateKey($storeId = null, $sandbox){
      if($sandbox){
          return Mage::helper('core')->decrypt(Mage::getStoreConfig('payment/satispay/key_private_sandbox', $storeId));
      }
        return Mage::helper('core')->decrypt(Mage::getStoreConfig('payment/satispay/key_private', $storeId));
    }

    /**
     * @param null $storeId
     * @param $sandbox
     * @return mixed
     */
    public function getKeyId($storeId = null, $sandbox){
      if($sandbox){
          return Mage::helper('core')->decrypt(Mage::getStoreConfig('payment/satispay/key_id_sandbox', $storeId));
      }
        return Mage::helper('core')->decrypt(Mage::getStoreConfig('payment/satispay/key_id', $storeId));
    }

    /**
     * @param null $storeId
     * @return boolean
     */
    public function debugModeEnable($storeId = null){
        return Mage::getStoreConfigFlag('payment/satispay/debug_mode', $storeId);
    }

}
