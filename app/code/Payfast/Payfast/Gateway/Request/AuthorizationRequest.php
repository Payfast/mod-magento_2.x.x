<?php
/**
 * Created by PhpStorm.
 * User: lefu
 * Date: 2018/05/07
 * Time: 8:48 PM
 */

namespace Payfast\Payfast\Gateway\Request;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Psr\Log\LoggerInterface;

class AuthorizationRequest implements BuilderInterface
{


    /**
     * @var ConfigInterface
     */
    private $config;

    /** @var LoggerInterface */
    private $logger;
    /**
     * @param ConfigInterface $config
     */
    public function __construct(
        ConfigInterface $config,
        LoggerInterface $logger
    ) {
        $this->config = $config;

        $this->logger = $logger;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $pre  = __METHOD__ . ' : ';
        $this->logger->debug($pre. 'bof');

        if (!isset($buildSubject['payment'])
            || !$buildSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        /** @var PaymentDataObjectInterface $payment */
        $payment = $buildSubject['payment'];
        $order = $payment->getOrder();
        $address = $order->getShippingAddress();

        $response = [
            'TXN_TYPE' => 'A',
            'INVOICE' => $order->getOrderIncrementId(),
            'AMOUNT' => $order->getGrandTotalAmount(),
            'CURRENCY' => $order->getCurrencyCode(),
            'EMAIL' => $address->getEmail(),
            'MERCHANT_KEY' => $this->config->getValue(
                'merchant_gateway_key',
                $order->getStoreId()
            )
        ];

        $this->logger->debug( $pre . 'response to return is :', $response);
        
        return $response;
    }
}