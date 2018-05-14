<?php


namespace Payfast\Payfast\Gateway\Validator;


use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;

class ResponseCodeValidator extends AbstractValidator
{

    /**
     * Performs domain-related validation for business object
     *
     * @param array $validationSubject
     *
     * @return ResultInterface
     */
    public function validate( array $validationSubject )
    {
        $pre = __METHOD__. ' : ';
        $this->logger->debug($pre. 'bof');

        if (!isset($validationSubject['response']) || !is_array($validationSubject['response'])) {
            throw new \InvalidArgumentException('Response does not exist');
        }

        $response = $validationSubject['response'];
        
        $this->logger->debug($pre . 'response has : '. print_r($response, true));
        
        
        // TODO: Implement validate() method.
    }
}