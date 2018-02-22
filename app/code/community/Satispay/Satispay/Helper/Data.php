<?php
class Satispay_Satispay_Helper_Data extends Mage_Core_Helper_Abstract {
  public function getSecurityBearer($storeId = null) {
    return Mage::getStoreConfig('payment/satispay/securityBearer', $storeId);
  }

  public function isStaging($storeId = null) {
    return Mage::getStoreConfig('payment/satispay/staging', $storeId);
  }
}
