<?php namespace Payfast\Payfast\Gateway\Http;

use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;


class TransferFactory implements TransferFactoryInterface
{

    /**
     * Builds gateway transfer object
     *
     * @param array $request
     *
     * @return TransferInterface
     */
    public function create( array $request )
    {
        // TODO: Implement create() method.
    }
}