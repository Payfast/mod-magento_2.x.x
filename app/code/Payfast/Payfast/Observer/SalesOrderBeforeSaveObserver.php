<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Payfast\Payfast\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Payfast\Payfast\Model\Config;

class SalesOrderBeforeSaveObserver implements ObserverInterface
{
    private $orderResourceModel;

    private $_logger;

    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order $orderResourceModel,
        \Psr\Log\LoggerInterface $logger
    ){
        $this->orderResourceModel = $orderResourceModel;
        $pre = __METHOD__ . " : ";

        $this->_logger = $logger;

        $this->_logger->debug( $pre . 'bof' );
    }

    /**
     * born out of necesity to force order status to not be in
     * @param Observer $observer
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {
        $pre = __METHOD__ . " : ";
        $this->_logger->debug( $pre . 'bof' );

        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();

        $this->_logger->debug('order status : ' . $order->getStatus());
        $this->_logger->debug('order state : ' . $order->getState());
        /**
         *  our module will set this statuses before redirecting out to PayFast
         *  so we don't want to update them again instruction payload status is COMPLETE.
         *
         **/
        try {

             if ($order->getPayment()->getMethodInstance()->getCode() == Config::METHOD_CODE ) {

                if (
                    $order->getState() != \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT &&
                    empty($order->getPayment()->getAdditionalInformation('pf_payment_id'))
                ) {
                    $this->_logger->debug($pre . 'setting order status and preventing sending of emails.');

                    $this->_logger->debug('order status : ' .$observer->getOrder()->getStatus());
                    $this->_logger->debug('order state : ' . $observer->getOrder()->getState());

                    $observer->getOrder()->setStatus(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
                    $observer->getOrder()->setState(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
                    $observer->getOrder()->setCanSendNewEmailFlag(false);
                    return $this;

                }
            }
        } catch ( \Exception $e ) {
            $this->_logger->debug( $pre . 'Exception found in : '. $e->getTraceAsString() );
        }



        $this->_logger->debug( $pre . 'eof' );
        return $this;

    }
}
