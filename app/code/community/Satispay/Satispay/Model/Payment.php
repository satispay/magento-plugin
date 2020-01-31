<?php
require_once(dirname(__FILE__).'/../includes/gbusiness-api-php-sdk/init.php');

class Satispay_Satispay_Model_Payment extends Mage_Payment_Model_Method_Abstract {
  protected $_code = 'satispay';
  protected $_canRefund = true;
  protected $_canRefundInvoicePartial = true;
  protected $_canUseForMultishipping = false;

  public function refund(Varien_Object $payment, $amount) {
      $helper = Mage::helper('satispay');
      $sandbox = $helper->isSandbox();
      $logger = Mage::getModel('satispay/logger', array($helper->debugModeEnable()));
      \SatispayGBusiness\Api::setSandbox($sandbox);
      \SatispayGBusiness\Api::setPluginVersionHeader('1.2.0');
      \SatispayGBusiness\Api::setPluginNameHeader('Magento');
      \SatispayGBusiness\Api::setTypeHeader('ECOMMERCE-PLUGIN');
      $magentoVersion = Mage::getVersionInfo();
      \SatispayGBusiness\Api::setPlatformVersionHeader($magentoVersion['major'].'.'.$magentoVersion['minor'].'.'.$magentoVersion['revision']);
      \SatispayGBusiness\Api::setPublicKey($helper->getPublicKey(null, $sandbox));
      \SatispayGBusiness\Api::setPrivateKey($helper->getPrivateKey(null, $sandbox));
      \SatispayGBusiness\Api::setKeyId($helper->getKeyId(null, $sandbox));

      $refundBody = array(
          "flow" => "REFUND",
          "amount_unit" => round($amount * 100),
          "currency" => $payment->getOrder()->getBaseCurrencyCode(),
          "parent_payment_uid" =>  $payment->getParentTransactionId()
      );
      $payment = \SatispayGBusiness\Payment::create($refundBody);
      $logger->debug(print_r(array('refundBody' => $refundBody), true));

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
