<?php

declare(strict_types=1);

namespace Maxpay\Payment\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;
use Maxpay\Payment\Gateway\Helper\SubjectReader;

/**
 * Maxpay TransactionCloseHandler class
 */
class TransactionCloseHandler implements HandlerInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @param SubjectReader $subjectReader
     */
    public function __construct(SubjectReader $subjectReader)
    {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @param array $handlingSubject
     * @param array $response
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        $payment = $paymentDO->getPayment();
        $payment->setIsTransactionClosed(true);
    }
}
