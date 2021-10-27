<?php

declare(strict_types=1);

namespace Maxpay\Payment\Gateway\Response;

use Exception;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Maxpay\Payment\Gateway\Helper\SubjectReader;
use Psr\Log\LoggerInterface as Logger;

/**
 * Maxpay VaultCardDetailsHandler class
 */
class VaultCardDetailsHandler implements HandlerInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param SubjectReader $subjectReader
     * @param Json $serializer
     * @param Logger $logger
     */
    public function __construct(
        SubjectReader $subjectReader,
        Json          $serializer,
        Logger        $logger
    )
    {
        $this->subjectReader = $subjectReader;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    /**
     * @param array $handlingSubject
     * @param array $response
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        $payment = $paymentDO->getPayment();

        /** @var PaymentTokenInterface $paymentToken */
        $paymentToken = $payment->getExtensionAttributes()->getVaultPaymentToken();

        try {
            $jsonDetails = $this->serializer->unserialize($paymentToken->getTokenDetails());
            $payment->setCcType($jsonDetails['type']);
            $payment->setCcLast4($jsonDetails['last4']);
            $payment->setCcExpMonth($jsonDetails['expMonth']);
            $payment->setCcExpYear($jsonDetails['expYear']);
        } catch (Exception $e) {
            $this->logger->critical($e);
        }

    }
}
