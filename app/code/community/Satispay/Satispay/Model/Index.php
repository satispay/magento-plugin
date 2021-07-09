<?php require_once(dirname(__FILE__) . '/../includes/gbusiness-api-php-sdk/init.php');

class Satispay_Satispay_Model_Index
{
    public function execute($paymentId)
    {
        $helper = Mage::helper('satispay');
        /** @var \Satispay_Satispay_Model_Logger $logger */
        $logger = Mage::getModel('satispay/logger', array($helper->debugModeEnable()));
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
            return false;
        }

        if (!isset($serverPayment->status)) {
            $logger->error('Invalid server payment object retrieved for ' . $paymentId);
            return false;
        }

        $order = Mage::getModel('sales/order')->load($serverPayment->metadata->order_id);
        $logger->debug(print_r(array('orderStatus' => $order->getStatus(), 'paymentStatus' => $serverPayment->status), true));

        if ($order->getStatus() !== 'pending') {
            return true;
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
            return true;
        }

        if ($serverPayment->status === 'FAILURE' || $serverPayment->status === 'CANCELED') {
            $order->cancel()->save();
            $logger->error('Order ' . $order->getIncrementId() . ' has been canceled.' );
            return true;
        }

        return false;
    }
}
