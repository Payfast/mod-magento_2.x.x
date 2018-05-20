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
use Payfast\Payfast\Model\Config;
use Psr\Log\LoggerInterface;

class AuthorizationRequest implements BuilderInterface
{

    /** @var Config  */
    private $payfastConfig;

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
        LoggerInterface $logger,
        Config $payfastConfig
    ) {
        $this->config = $config;

        $this->logger = $logger;

        $this->payfastConfig = $payfastConfig;
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
        try {

            $merchantId = $this->config->getValue( 'merchant_id', $order->getStoreId() );
            $merchantKey = $this->config->getValue( 'merchant_key', $order->getStoreId() );
            $data = [
                // Merchant details
                'merchant_id' => $merchantId,
                'merchant_key' => $merchantKey,
                'return_url' => $this->payfastConfig->getPaidSuccessUrl(),
                'cancel_url' => $this->payfastConfig->getPaidCancelUrl(),
                'notify_url' => $this->payfastConfig->getPaidNotifyUrl(),

                // Buyer details
                'name_first' => $address->getFirstname(),
                'name_last' => $address->getLastname(),
                'email_address' => $address->getEmail(),

                // Item details
                'm_payment_id' => $order->getOrderIncrementId(),
                'amount' => $order->getGrandTotalAmount(),

                // 'item_name' => $this->_storeManager->getStore()->getName() .', Order #'. $order->getOrderIncrementId(),
                'item_name' => 'Order #'. $order->getOrderIncrementId(),
                'currency' => $order->getCurrencyCode(),


            ];
        } catch (\Exception $exception) {
            $this->logger->critical($pre. $exception->getTraceAsString());
            throw $exception;
        }
        $pfOutput = '';
        // Create output string
        foreach( $data as $key => $val )
        {
            if (!empty( $val ))
            {
                $pfOutput .= $key .'='. urlencode( $val ) .'&';
            }
        }

        $passPhrase = $this->config->getValue('passphrase', $order->getStoreId());
        $pfOutput = substr( $pfOutput, 0, -1 );

        if ( !empty( $passPhrase ) && $this->config->getValue('server', $order->getStoreId()) !== 'test' )
        {
            $pfOutput = $pfOutput."&passphrase=".urlencode( $passPhrase );
        }

        $this->logger->debug( $pre . 'pfOutput for signature is : '. $pfOutput );

        $pfSignature = md5( $pfOutput );

        $data['signature'] = $pfSignature;
        $data['user_agent'] = 'Magento ' . $this->getAppVersion();

        $this->logger->debug( $pre . 'response to return is :', $data);

        return $data;
    }

    /**
     * getAppVersion
     *
     * @return string
     */
    private function getAppVersion()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $version = $objectManager->get('Magento\Framework\App\ProductMetadataInterface')->getVersion();

        return  (preg_match('([0-9])', $version )) ? $version : '2.0.0';
    }
}