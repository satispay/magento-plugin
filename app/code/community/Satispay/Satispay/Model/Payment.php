<?php
require_once(dirname(__FILE__).'/../includes/online-api-php-sdk/init.php');

class Satispay_Satispay_Model_Payment extends Mage_Payment_Model_Method_Abstract {
  protected $_code = 'satispay';
  protected $_canRefund = true;
  protected $_canRefundInvoicePartial = true;
  protected $_canUseForMultishipping = false;

  public function refund(Varien_Object $payment, $amount) {
    $helper = Mage::helper('satispay');

    \SatispayOnline\Api::setSecurityBearer($helper->getSecurityBearer($payment->getOrder()->getStoreId()));
    \SatispayOnline\Api::setStaging($helper->isStaging($payment->getOrder()->getStoreId()));
    \SatispayOnline\Api::setPluginName('Magento');
    \SatispayOnline\Api::setType('ECOMMERCE-PLUGIN');
    $magentoVersion = Mage::getVersionInfo();
    \SatispayOnline\Api::setPlatformVersion($magentoVersion['major'].'.'.$magentoVersion['minor'].'.'.$magentoVersion['revision']);

    $refund = \SatispayOnline\Refund::create(array(
      'charge_id' => $payment->getParentTransactionId(),
      'currency' => $payment->getOrder()->getBaseCurrencyCode(),
      'description' => '#'.$payment->getOrder()->getIncrementId(),
      'amount' => round($amount * 100)
    ));

    return $this;
  }

  public function canUseForCurrency($currencyCode) {
    if ($currencyCode !== 'EUR') return false;
    return true;
  }

  public function getOrderPlaceRedirectUrl() {
    return Mage::getUrl('satispay/payment', array(
      '_secure' => true
    ));
  }
}
