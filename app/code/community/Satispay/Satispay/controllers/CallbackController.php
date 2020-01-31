<?php
require_once(dirname(__FILE__) . '/../includes/gbusiness-api-php-sdk/init.php');

class Satispay_Satispay_CallbackController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $helper = Mage::helper('satispay');
        $logger = Mage::getModel('satispay/logger', array($helper->debugModeEnable()));
        $sandbox = $helper->isSandbox();
        $logger->debug('sandbox: ' . ($sandbox ? 'yes' : 'no'));

        \SatispayGBusiness\Api::setSandbox($sandbox);
        \SatispayGBusiness\Api::setPluginVersionHeader('1.2.0');
        \SatispayGBusiness\Api::setPluginNameHeader('Magento');
        \SatispayGBusiness\Api::setTypeHeader('ECOMMERCE-PLUGIN');
        $magentoVersion = Mage::getVersionInfo();
        \SatispayGBusiness\Api::setPlatformVersionHeader($magentoVersion['major'] . '.' . $magentoVersion['minor'] . '.' . $magentoVersion['revision']);
        \SatispayGBusiness\Api::setPublicKey($helper->getPublicKey(null, $sandbox));
        \SatispayGBusiness\Api::setPrivateKey($helper->getPrivateKey(null, $sandbox));
        \SatispayGBusiness\Api::setKeyId($helper->getKeyId(null, $sandbox));


        $payments = \SatispayGBusiness\Payment::get($this->getRequest()->getQuery('payment_id'));
        $order = Mage::getModel('sales/order')->load($payments->metadata->order_id);
        $logger->debug(print_r(array('orderStatus' => $order->getStatus(), 'paymentStatus' => $payments->status), true));
        if ($order->getStatus() === 'pending') {
            if ($payments->status === 'ACCEPTED') {
                $payment = $order->getPayment();
                $payment->setTransactionId($payments->id)
                    ->setIsTransactionClosed(false)
                    ->registerCaptureNotification($order->getGrandTotal(), false);
                $order->save();
                $logger->debug('transaction: ' . $payments->id);
                $invoice = $payment->getCreatedInvoice();
                if ($invoice && !$order->getEmailSent()) {
                    $order->queueNewOrderEmail()
                        ->setIsCustomerNotified(true)
                        ->save();
                }
                $logger->debug('invoice: ' . $invoice->getIncrementId());
            }

            if ($payments->status === 'FAILURE' || $payments->status === 'CANCELED') {
                $order->cancel()->save();
                $logger->error('order: ' . $order->getIncrementId() . ' has been canceled' );
            }
        }
        $this->getResponse()->setBody('OK');
    }
}
