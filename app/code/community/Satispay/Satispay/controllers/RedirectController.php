<?php
require_once(dirname(__FILE__).'/../includes/online-api-php-sdk/init.php');

class Satispay_Satispay_RedirectController extends Mage_Core_Controller_Front_Action {
  public function indexAction() {
    $helper = Mage::helper('satispay');

    \SatispayOnline\Api::setSecurityBearer($helper->getSecurityBearer());
    \SatispayOnline\Api::setStaging($helper->isStaging());
    \SatispayOnline\Api::setPluginName('Magento');
    \SatispayOnline\Api::setType('ECOMMERCE-PLUGIN');
    $magentoVersion = Mage::getVersionInfo();
    \SatispayOnline\Api::setPlatformVersion($magentoVersion['major'].'.'.$magentoVersion['minor'].'.'.$magentoVersion['revision']);

    $charge = \SatispayOnline\Charge::get($this->getRequest()->getQuery('charge_id'));
    $order = Mage::getModel('sales/order')->load($charge->metadata->order_id);

    if ($charge->status === 'SUCCESS') {
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

      $this->getResponse()->setRedirect(Mage::getUrl('checkout/cart', array(
        '_secure' => true
      )));
    }
  }
}
