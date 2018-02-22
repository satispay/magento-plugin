<?php
require_once(dirname(__FILE__).'/../includes/online-api-php-sdk/init.php');

class Satispay_Satispay_CronController extends Mage_Core_Controller_Front_Action {
  public function indexAction() {
    $orders = Mage::getResourceModel('sales/order_collection')
      ->addFieldToFilter('status', 'pending');

    $res = "Canceled orders:\n";

    foreach ($orders as $order) {
      $payment = $order->getPayment();
      if ($payment->getMethod() === 'satispay') {
        
        $createdAt = strtotime($order->getCreatedAt());
        $expireMinutes = 30;
        $expireAt = $createdAt + (60 * $expireMinutes);

        if (time() > $expireAt) {
          $res .= $order->getIncrementId()."\n";
          $order->cancel()->save();
        }
      }
    }

    // echo $res;
  }
}
