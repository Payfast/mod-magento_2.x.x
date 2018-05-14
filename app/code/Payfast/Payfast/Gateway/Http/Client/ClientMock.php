<?php


namespace Payfast\Payfast\Gateway\Http\Client;


use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;

class ClientMock implements ClientInterface
{

    const SUCCESS = 1;
    const FAILURE = 0;

    /**
     * @var array
     */
    private $results = [
        self::SUCCESS,
        self::FAILURE
    ];

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param Logger $logger
     */
    public function __construct(
        Logger $logger
    ) {
        $this->logger = $logger;
    }

    public function placeRequest(\Magento\Payment\Gateway\Http\TransferInterface $transferObject)
    {
        $pre = __METHOD__ . ' : ';
        $this->logger->debug($pre . 'bof');
        $this->logger->debug($pre . 'bof' . print_r($transferObject, true));

        // TODO: Implement placeRequest() method.
        $response = $this->generateResponseForCode(
            $this->getResultCode(
                $transferObject
            )
        );

        $this->logger->debug(
            [
                'request' => $transferObject->getBody(),
                'response' => $response
            ]
        );

        $this->logger->debug($pre . 'bof');

        return $response;

    }

}