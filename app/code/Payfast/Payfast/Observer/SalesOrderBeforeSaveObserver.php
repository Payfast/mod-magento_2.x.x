<?php namespace Payfast\Payfast\Observer;
/**
 * Copyright (c) 2008 PayFast (Pty) Ltd
 * You (being anyone who is not PayFast (Pty) Ltd) may download and use this plugin / code in your own website in conjunction with a registered and active PayFast account. If your PayFast account is terminated for any reason, you may not use this plugin / code or part thereof.
 * Except as expressly indicated in this licence, you may not use, copy, modify or distribute this plugin / code or part thereof in any way.
 */
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Payfast\Payfast\Model\Config;

class SalesOrderBeforeSaveObserver implements ObserverInterface
{

    private $_logger;

    /**
     * SalesOrderBeforeSaveObserver constructor.
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(\Psr\Log\LoggerInterface $logger)
    {
        $this->_logger = $logger;
    }

    /**
     * born out of necessity to force order status to not be in processing.
     * provided that user has not paid.
     *
     * @param Observer $observer
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {
        $pre = __METHOD__ . " : ";
        $this->_logger->debug($pre . 'bof');

        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();

        if (
            $order->getPayment()->getMethodInstance()->getCode() == Config::METHOD_CODE &&
            $order->getState() != \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT &&
            empty($order->getPayment()->getAdditionalInformation('pf_payment_id'))
        ) {
            $this->_logger->debug($pre . 'setting order status and preventing sending of emails.');

            $this->_logger->debug('order status : ' . $observer->getOrder()->getStatus());
            $this->_logger->debug('order state : ' . $observer->getOrder()->getState());

            $observer->getOrder()->setStatus(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
            $observer->getOrder()->setState(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
            $observer->getOrder()->setCanSendNewEmailFlag(false);

            return $this;
        }

        $this->_logger->debug('order status : ' . $order->getStatus());
        $this->_logger->debug('order state : ' . $order->getState());

        $this->_logger->debug($pre . "pf_payment_id is : ( {$order->getPayment()->getAdditionalInformation('pf_payment_id')} )" );

        $this->_logger->debug($pre . 'eof');
        return $this;

    }
}
