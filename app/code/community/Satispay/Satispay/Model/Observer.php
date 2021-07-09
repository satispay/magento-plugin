<?php

class Satispay_Satispay_Model_Observer
{
    public function finalize()
    {
        $helper = Mage::helper('satispay');
        $logger = Mage::getModel('satispay/logger', array($helper->debugModeEnable()));
        $logger->debug("Starting cron job to finalize Satispay orders");
        $rangeStart = $this->getStartDateScheduledTime($helper);
        $rangeEnd = $this->getEndDateScheduledTime();

        $isActive = $helper->isActive();
        $isActivated = $helper->isActivated();
        $isFinalizeActivated = $helper->getFinalizeUnhandledTransactions();
        if ($isActive && $isActivated && $isFinalizeActivated) {
            $orderCollection = Mage::getModel('sales/order')->getCollection()
                ->addAttributeToFilter('state','new')
                ->addAttributeToFilter('status','pending')
                ->addAttributeToFilter('updated_at', array('from'=>$rangeStart, 'to'=>$rangeEnd))
                ->addFieldToSelect('*');

            foreach ($orderCollection as $order) {
                $orderPayment = $order->getPayment();
                if (isset($orderPayment) && $orderPayment->getMethod() === 'satispay') {
                    try {
                        $this->processOrder($order, $orderPayment);
                    } catch (\Exception $e) {
                        $orderId = $order->getEntityId();
                        $logger->error("Could not finalize Order $orderId for Satispay payment: " . $e->getMessage());
                    }
                }
            }
        }
        $logger->debug("Ending cron job that finalize Satispay orders");
    }

    /**
     * Get the start criteria for the scheduled datetime
     */
    private function getStartDateScheduledTime($helper)
    {
        $maxHours = $helper->getFinalizeMaxHours();
        $now = new \DateTime();
        $scheduledTimeFrame = $maxHours;
        if (is_null($scheduledTimeFrame) || $scheduledTimeFrame == 0) {
            $scheduledTimeFrame = 4; // DEFAULT_MAX_HOURS
        }
        $tosub = new \DateInterval('PT'. $scheduledTimeFrame . 'H');
        return $now->sub($tosub)->format('Y-m-d H:i:s');
    }

    /**
     * Get the end criteria for the scheduled datetime
     */
    private function getEndDateScheduledTime()
    {
        $now = new \DateTime();
        // remove just 1 hour so normal transactions can still be processed
        $tosub = new \DateInterval('PT'. 1 . 'H');
        return $now->sub($tosub)->format('Y-m-d H:i:s');
    }

    private function processOrder(Mage_Sales_Model_Order $order, Mage_Sales_Model_Order_Payment $payment)
    {
        $satispayPaymentId = $payment->getLastTransId();
        if(isset($satispayPaymentId)) {
            // callback logic to finalize payments
            $callbackIndex = Mage::getSingleton('satispay/index');
            $hasBeenFinalized = $callbackIndex->execute($satispayPaymentId);
            if ($hasBeenFinalized) {
                $this->addCommentToOrder($order);
            }
        }
    }

    private function addCommentToOrder(Mage_Sales_Model_Order $order)
    {
        $order->addStatusHistoryComment('The Satispay Payment has been finalized by custom command line action')
            ->setIsVisibleOnFront(false)
            ->setIsCustomerNotified(false)
            ->save();
    }
}
