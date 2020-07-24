<?php
require_once(dirname(__FILE__) . '/../includes/gbusiness-api-php-sdk/init.php');

class Satispay_Satispay_PaymentController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $helper = Mage::helper('satispay');
        $logger = Mage::getModel('satispay/logger', array($helper->debugModeEnable()));
        $sandbox = $helper->isSandbox();
        $logger->debug('sandbox: ' . ($sandbox ? 'yes' : 'no'));
        $publicKey = $helper->getPublicKey();
        $privateKey = $helper->getPrivateKey();
        $keyId = $helper->getKeyId($sandbox);
        if (!$publicKey || !$privateKey || !$keyId) {
            $logger->error('PublicKey: ' . ($publicKey ? 'ok' : 'missing'));
            $logger->error('PrivateKey: ' . ($privateKey ? 'ok' : 'missing'));
            $logger->error('KeyId: ' . ($keyId ? 'ok' : 'missing'));
            $session = Mage::getSingleton('checkout/session');
            $order = $session->getLastRealOrder();
            $session->clearHelperData();
            $order->cancel()->save();

            $cart = Mage::getSingleton('checkout/cart');
            $items = $order->getItemsCollection();
            foreach ($items as $item) {
                try {
                    $cart->addOrderItem($item);
                } catch (Exception $e) {
                    $logger->exception($e);
                }
            }
            $cart->save();
            $logger->error('order ' . $order->getIncrementId() . ' has been canceled');
            return $this->getResponse()->setRedirect(Mage::getUrl('checkout/cart', array(
                '_secure' => true,
                Mage::getSingleton('core/session')->addError('Please Generate New KEYS to complete the order with Satispay')
            )));
        }

        \SatispayGBusiness\Api::setSandbox($sandbox);
        \SatispayGBusiness\Api::setPluginVersionHeader($helper->getExtensionVersion());
        \SatispayGBusiness\Api::setPluginNameHeader('Magento');
        \SatispayGBusiness\Api::setTypeHeader('ECOMMERCE-PLUGIN');
        \SatispayGBusiness\Api::setPlatformVersionHeader(Mage::getVersion());
        \SatispayGBusiness\Api::setPublicKey($publicKey);
        \SatispayGBusiness\Api::setPrivateKey($privateKey);
        \SatispayGBusiness\Api::setKeyId($keyId);

        $session = Mage::getSingleton('checkout/session');
        $order = $session->getLastRealOrder();

        $backFromSatispay = $this->getRequest()->getQuery('back-from-satispay');
        if (!empty($backFromSatispay)) {
            $logger->error('back-from-satispay is not empty');
            $session->clearHelperData();
            $order->cancel()->save();

            $cart = Mage::getSingleton('checkout/cart');
            $items = $order->getItemsCollection();
            foreach ($items as $item) {
                try {
                    $cart->addOrderItem($item);
                } catch (Exception $e) {
                }
            }
            $cart->save();
            $logger->error('order ' . $order->getIncrementId() . ' has been canceled');
            $this->getResponse()->setRedirect(Mage::getUrl('checkout/cart', array(
                '_secure' => true
            )));
        } else {
            $paymentBody = array(
                "flow" => "MATCH_CODE",
                "amount_unit" => round($order->getGrandTotal() * 100),
                "currency" => $order->getOrderCurrencyCode(),
                "external_code" => $order->getIncrementId(),
                "callback_url" => Mage::getUrl('satispay/callback', array(
                    "_secure" => true,
                    "_query" => "payment_id={uuid}"
                )),
                "metadata" => array(
                    "order_id" => $order->getId(),
                    'redirect_url' => Mage::getUrl('satispay/redirect', array(
                        '_secure' => true,
                        '_query' => 'payment_id={uuid}'
                    ))
                )
            );
            $logger->debug(print_r(array('paymentBody ' => $paymentBody), true));
            $payment = \SatispayGBusiness\Payment::create($paymentBody);
            $satispayUrl = 'https://online.satispay.com/';
            if ($sandbox) {
                $satispayUrl = 'https://staging.online.satispay.com/';
            }
            $paymentUrl = $satispayUrl . 'pay/' . $payment->id;
            $buttonUrl = $satispayUrl . 'web-button.js';

            $this->getResponse()->setBody("<script src=\"$buttonUrl\" data-payment-id=\"$payment->id\"></script><script>history.replaceState({}, '', '?back-from-satispay=1'); setTimeout(function () { location.href = '$paymentUrl'; }, 200);</script>");
        }
    }
}
