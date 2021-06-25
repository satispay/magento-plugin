<?php
require_once(dirname(__FILE__).'/../includes/gbusiness-api-php-sdk/init.php');

class Satispay_Satispay_RedirectController extends Mage_Core_Controller_Front_Action {

  public function indexAction() {
      $helper = Mage::helper('satispay');
      $logger = Mage::getModel('satispay/logger', array($helper->debugModeEnable()));
      $sandbox = $helper->isSandbox();
      $logger->debug('sandbox: ' . ($sandbox ? 'yes' : 'no'));
      \SatispayGBusiness\Api::setSandbox($sandbox);
      \SatispayGBusiness\Api::setPluginVersionHeader($helper->getExtensionVersion());
      \SatispayGBusiness\Api::setPluginNameHeader('Magento');
      \SatispayGBusiness\Api::setTypeHeader('ECOMMERCE-PLUGIN');
      \SatispayGBusiness\Api::setPlatformVersionHeader(Mage::getVersion());
      \SatispayGBusiness\Api::setPublicKey($helper->getPublicKey());
      \SatispayGBusiness\Api::setPrivateKey($helper->getPrivateKey());
      \SatispayGBusiness\Api::setKeyId($helper->getKeyId($sandbox));
      $satispayPayment = \SatispayGBusiness\Payment::get($this->getRequest()->getQuery('payment_id'));

    $order = Mage::getModel('sales/order')->load($satispayPayment->metadata->order_id);

    if ($satispayPayment->status === 'ACCEPTED') {
      $payment = $order->getPayment();
      if (isset($payment)) {
          $payment->setLastTransId($satispayPayment->id);
          $payment->save();
      }
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
      )));
    }
  }
}
