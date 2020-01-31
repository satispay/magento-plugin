<?php
require_once(dirname(__FILE__).'/../includes/gbusiness-api-php-sdk/init.php');

class Satispay_Satispay_RedirectController extends Mage_Core_Controller_Front_Action {

  public function indexAction() {
      $helper = Mage::helper('satispay');
      $logger = Mage::getModel('satispay/logger', array($helper->debugModeEnable()));
      $sandbox = $helper->isSandbox();
      $logger->debug('sandbox: ' . ($sandbox ? 'yes' : 'no'));
      \SatispayGBusiness\Api::setSandbox($sandbox);
      \SatispayGBusiness\Api::setPluginVersionHeader('1.2.0');
      \SatispayGBusiness\Api::setPluginNameHeader('Magento');
      \SatispayGBusiness\Api::setTypeHeader('ECOMMERCE-PLUGIN');
      $magentoVersion = Mage::getVersionInfo();
      \SatispayGBusiness\Api::setPlatformVersionHeader($magentoVersion['major'].'.'.$magentoVersion['minor'].'.'.$magentoVersion['revision']);
      \SatispayGBusiness\Api::setPublicKey($helper->getPublicKey(null, $sandbox));
      \SatispayGBusiness\Api::setPrivateKey($helper->getPrivateKey(null, $sandbox));
      \SatispayGBusiness\Api::setKeyId($helper->getKeyId(null, $sandbox));
      $payment = \SatispayGBusiness\Payment::get($this->getRequest()->getQuery('payment_id'));

    $order = Mage::getModel('sales/order')->load($payment->metadata->order_id);

    if ($payment->status === 'ACCEPTED') {
      $this->getResponse()->setRedirect(Mage::getUrl('checkout/onepage/success', array(
        '_secure' => true
      )));
    } else {
      $session = Mage::getSingleton('checkout/session');
      $session->clearHelperData();
      $order->cancel()->save();

      $cart = Mage::getSingleton('checkout/cart');
      $items = $order->getItemsCollection();
      foreach ($items as $item) {
        try {
          $cart->addOrderItem($item);
        } catch (Exception $e) { }
      }
      $cart->save();
      // cart session message
      $this->getResponse()->setRedirect(Mage::getUrl('checkout/cart', array(
        '_secure' => true,
          Mage::getSingleton('core/session')->addSuccess('ATTENTION: you don\'t have sufficient balance. Try borrowing from a friend to complete the payment')
      )));
    }
  }
}
