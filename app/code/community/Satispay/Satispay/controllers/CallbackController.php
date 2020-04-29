<?php
require_once(dirname(__FILE__) . '/../includes/gbusiness-api-php-sdk/init.php');

class Satispay_Satispay_CallbackController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $helper = Mage::helper('satispay');
        /** @var \Satispay_Satispay_Model_Logger $logger */
        $logger = Mage::getModel('satispay/logger', array($helper->debugModeEnable()));

        $paymentId = $this->getRequest()->getParam('payment_id');
        if (!$paymentId) {
            $logger->error('Parameter payment_id is required.');
            $this->getResponse()->setBody('Error, see logs for more details.');
            return;
        }

        $isSandbox = $helper->isSandbox();
        $logger->debug('sandbox: ' . ($isSandbox ? 'yes' : 'no'));

        \SatispayGBusiness\Api::setSandbox($isSandbox);
        \SatispayGBusiness\Api::setPluginVersionHeader($helper->getExtensionVersion());
        \SatispayGBusiness\Api::setPluginNameHeader('Magento');
        \SatispayGBusiness\Api::setTypeHeader('ECOMMERCE-PLUGIN');
        \SatispayGBusiness\Api::setPlatformVersionHeader(Mage::getVersion());
        \SatispayGBusiness\Api::setPublicKey($helper->getPublicKey());
        \SatispayGBusiness\Api::setPrivateKey($helper->getPrivateKey());
        \SatispayGBusiness\Api::setKeyId($helper->getKeyId($isSandbox));

        try {
            $serverPayment = \SatispayGBusiness\Payment::get($paymentId);
        } catch (Exception $e) {
            $logger->error($e->getMessage());
            $this->getResponse()->setBody('Error, see logs for more details.');
            return;
        }

        if (!isset($serverPayment->status)) {
            $logger->error('Invalid server payment object retrieved for ' . $paymentId);
            $this->getResponse()->setBody('Error, see logs for more details.');
            $this->getResponse()->setBody('OK');
        }

        $order = Mage::getModel('sales/order')->load($serverPayment->metadata->order_id);
        $logger->debug(print_r(array('orderStatus' => $order->getStatus(), 'paymentStatus' => $serverPayment->status), true));

        if ($order->getStatus() !== 'pending') {
            $logger->debug('Server payment ' . $paymentId . ' already processed');
            $this->getResponse()->setBody('OK');
            return;
        }

        if ($serverPayment->status === 'ACCEPTED') {
            $payment = $order->getPayment();
            $payment->setTransactionId($serverPayment->id)
                ->setIsTransactionClosed(false)
                ->registerCaptureNotification($order->getGrandTotal(), false);
            $order->save();
            $logger->debug('Transaction saved for: ' . $serverPayment->id);
            $invoice = $payment->getCreatedInvoice();
            if ($invoice && !$order->getEmailSent()) {
                $order->queueNewOrderEmail()
                    ->setIsCustomerNotified(true)
                    ->save();
            }
            $logger->debug('Invoice: ' . $invoice->getIncrementId());
        }

        if ($serverPayment->status === 'FAILURE' || $serverPayment->status === 'CANCELED') {
            $order->cancel()->save();
            $logger->error('Order ' . $order->getIncrementId() . ' has been canceled.' );
        }

        $this->getResponse()->setBody('OK');
    }
}
