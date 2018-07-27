<?php
require_once(dirname(__FILE__).'/../includes/online-api-php-sdk/init.php');

class Satispay_Satispay_PaymentController extends Mage_Core_Controller_Front_Action {
  public function indexAction() {
    $helper = Mage::helper('satispay');

    \SatispayOnline\Api::setSecurityBearer($helper->getSecurityBearer());
    \SatispayOnline\Api::setStaging($helper->isStaging());
    \SatispayOnline\Api::setPluginName('Magento');
    \SatispayOnline\Api::setType('ECOMMERCE-PLUGIN');
    $magentoVersion = Mage::getVersionInfo();
    \SatispayOnline\Api::setPlatformVersion($magentoVersion['major'].'.'.$magentoVersion['minor'].'.'.$magentoVersion['revision']);

    $session = Mage::getSingleton('checkout/session');
    $order = $session->getLastRealOrder();

    $backFromSatispay = $this->getRequest()->getQuery('back-from-satispay');
    if (!empty($backFromSatispay)) {
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
    } else {
      $checkout = \SatispayOnline\Checkout::create(array(
        'description' => '#'.$order->getIncrementId(),
        'phone_number' => '',
        'redirect_url' => Mage::getUrl('satispay/redirect', array(
          '_secure' => true
        )),
        'callback_url' => Mage::getUrl('satispay/callback', array(
          '_secure' => true,
          '_query' => 'charge_id={uuid}'
        )),
        'checkout_expire_callback_url' => Mage::getUrl('satispay/expire', array(
          '_secure' => true,
          '_query' => 'order_id='.$order->getId()
        )),
        'expire_in' => 60*15,
        'amount_unit' => round($order->getGrandTotal() * 100),
        'currency' => $order->getOrderCurrencyCode(),
        'metadata' => array(
          'order_id' => $order->getId()
        )
      ));

      $this->getResponse()->setBody("<script>history.replaceState({}, '', '?back-from-satispay=1'); setTimeout(function () { location.href = '$checkout->checkout_url'; }, 200);</script>");
    }
  }
}
