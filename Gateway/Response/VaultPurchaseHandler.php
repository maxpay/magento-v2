<?php

declare(strict_types=1);

namespace Maxpay\Payment\Gateway\Response;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Maxpay\Lib\Exception\GeneralMaxpayException;
use Maxpay\Payment\Gateway\Config\Config;
use Maxpay\Payment\Gateway\Helper\PrepareProductHelper;
use Maxpay\Payment\Gateway\Helper\PrepareUserHelper;
use Maxpay\Payment\Gateway\Helper\SubjectReader;
use Maxpay\Scriney;
use Psr\Log\LoggerInterface as Logger;

/**
 * Maxpay VaultPurchaseHandler class
 */
class VaultPurchaseHandler implements HandlerInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var PrepareUserHelper
     */
    private $userHelper;

    /**
     * @var PrepareProductHelper
     */
    private $productHelper;

    /**
     * @param SubjectReader $subjectReader
     * @param Logger $logger
     * @param Config $config
     * @param PrepareUserHelper $userHelper
     * @param PrepareProductHelper $productHelper
     */
    public function __construct(
        SubjectReader        $subjectReader,
        Logger               $logger,
        Config               $config,
        PrepareUserHelper    $userHelper,
        PrepareProductHelper $productHelper
    )
    {
        $this->subjectReader = $subjectReader;
        $this->logger = $logger;
        $this->config = $config;
        $this->userHelper = $userHelper;
        $this->productHelper = $productHelper;
    }

    /**
     * @param array $handlingSubject
     * @param array $response
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws GeneralMaxpayException
     */
    public function handle(array $handlingSubject, array $response)
    {
        $logger = null;
        if ($this->config->isDebugModeEnabled()) {
            $logger = $this->logger;
        }

        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        $payment = $paymentDO->getPayment();
        $userId = $paymentDO->getOrder()->getCustomerId();
        $order = $paymentDO->getOrder();

        $vaultPaymentToken = $payment->getExtensionAttributes()->getVaultPaymentToken();
        $vaultGatewayToken = $vaultPaymentToken->getGatewayToken();

        $scriney = new Scriney($this->config->getPublicKey(), $this->config->getPrivateKey(), $logger);
        $result = $scriney->createRebillRequest($vaultGatewayToken, (string)$userId)
            ->setUserInfo($this->userHelper->prepareUserProfile($order))
            ->setCustomProduct($this->productHelper->getCustomProduct($order))
            ->send();
        try {
            $scriney->validateApiResult($result);
            $payment->setTransactionId($result['transactionId']);
            $payment->setAdditionalInformation(Config::MAXPAY_TRANSACTION_ID, $result['transactionId']);
        } catch (Exception $e) {
            $this->logger->critical($e);
        }
    }
}
