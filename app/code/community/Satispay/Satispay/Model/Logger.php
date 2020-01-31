<?php

class Satispay_Satispay_Model_Logger
{

    const LOG_FILENAME = 'satispay.log';

    /**
     * @var Mage_Core_Model_Logger
     */
    protected $logger;

    /**
     * @var boolean
     */
    private $debugModeEnable;

    /**
     * Satispay_Satispay_Model_Logger constructor.
     * @param boolean $debugModeEnable
     */
    public function __construct($debugModeEnable)
    {
        $this->debugModeEnable = $debugModeEnable;
        $this->logger = Mage::getModel('core/logger');
    }

    /**
     * @param $message
     */
    public function debug($message)
    {
        $this->logger->log($message, Zend_Log::DEBUG, self::LOG_FILENAME, $this->debugModeEnable);
    }

    /**
     * @param $message
     */
    public function error($message)
    {
        $this->logger->log($message, Zend_Log::ERR, self::LOG_FILENAME, $this->debugModeEnable);
    }

    /**
     * @param Exception $e
     */
    public function exception(Exception $e)
    {
        $this->logger->logException($e);
    }
}