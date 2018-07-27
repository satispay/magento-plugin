<?php
require_once(dirname(__FILE__).'/../includes/online-api-php-sdk/init.php');

class Satispay_Satispay_ExpireController extends Mage_Core_Controller_Front_Action {
  public function indexAction() {
    $helper = Mage::helper('satispay');

    \SatispayOnline\Api::setSecurityBearer($helper->getSecurityBearer());
    \SatispayOnline\Api::setStaging($helper->isStaging());
    \SatispayOnline\Api::setPluginName('Magento');
    \SatispayOnline\Api::setType('ECOMMERCE-PLUGIN');
    $magentoVersion = Mage::getVersionInfo();
    \SatispayOnline\Api::setPlatformVersion($magentoVersion['major'].'.'.$magentoVersion['minor'].'.'.$magentoVersion['revision']);

    $order = Mage::getModel('sales/order')->load($this->getRequest()->getQuery('order_id'));
    if ($order->getStatus() === 'pending') {
      $order->cancel()->save();
    }
  }
}
