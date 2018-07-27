<?php
require_once(dirname(__FILE__).'/../includes/online-api-php-sdk/init.php');

class Satispay_Satispay_CallbackController extends Mage_Core_Controller_Front_Action {
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

    if ($order->getStatus() === 'pending') {
      if ($charge->status === 'SUCCESS') {
        $payment = $order->getPayment();
        $payment->setTransactionId($charge->id)
          ->setIsTransactionClosed(false)
          ->registerCaptureNotification($order->getGrandTotal(), false);
        $order->save();

        $invoice = $payment->getCreatedInvoice();
        if ($invoice && !$order->getEmailSent()) {
          $order->queueNewOrderEmail()
            ->setIsCustomerNotified(true)
            ->save();
        }
      }

      if ($charge->status === 'FAILURE') {
        $order->cancel()->save();
      }
    }
  }
}
