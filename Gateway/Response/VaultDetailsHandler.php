<?php

declare(strict_types=1);

namespace Maxpay\Payment\Gateway\Response;

use DateInterval;
use DateTime;
use DateTimeZone;
use Exception;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\OrderPaymentRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface as OrderRepository;
use Magento\Sales\Model\Order;
use Magento\Vault\Api\Data\PaymentTokenFactoryInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\Ui\VaultConfigProvider;
use Maxpay\Payment\Gateway\Config\Config;
use Maxpay\Payment\Gateway\Helper\SubjectReader;
use Psr\Log\LoggerInterface as Logger;

/**
 * Maxpay VaultDetailsHandler class
 */
class VaultDetailsHandler implements HandlerInterface
{
    /**
     * @var PaymentTokenFactoryInterface
     */
    private $paymentTokenFactory;

    /**
     * @var OrderPaymentExtensionInterfaceFactory
     */
    private $paymentExtensionFactory;

    /**
     * @var OrderPaymentRepositoryInterface
     */
    private $orderPaymentRepository;

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
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param SubjectReader $subjectReader
     * @param PaymentTokenFactoryInterface $paymentTokenFactory
     * @param OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory
     * @param Logger $logger
     * @param OrderPaymentRepositoryInterface $orderPaymentRepository
     * @param OrderRepository $orderRepository
     * @param Config $config
     * @param Json|null $serializer
     */
    public function __construct(
        SubjectReader                         $subjectReader,
        PaymentTokenFactoryInterface          $paymentTokenFactory,
        OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory,
        Logger                                $logger,
        OrderPaymentRepositoryInterface       $orderPaymentRepository,
        OrderRepository                       $orderRepository,
        Config                                $config,
        ?Json                                  $serializer = null
    )
    {
        $this->subjectReader = $subjectReader;
        $this->paymentTokenFactory = $paymentTokenFactory;
        $this->paymentExtensionFactory = $paymentExtensionFactory;
        $this->logger = $logger;
        $this->orderPaymentRepository = $orderPaymentRepository;
        $this->orderRepository = $orderRepository;
        $this->config = $config;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
    }

    /**
     * @param array $handlingSubject
     * @param array $response
     * @throws Exception
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        $orderId = $paymentDO->getOrder()->getId();

        /** @var Order $order */
        $order = $this->orderRepository->get($orderId);
        $payment = $order->getPayment();
        if (!$payment instanceof OrderPaymentInterface) {
            throw new Exception(
                'Order Payment does not exist.'
            );
        }

        // add vault payment token entity to extension attributes
        $paymentToken = $this->getVaultPaymentToken($handlingSubject);
        if (null !== $paymentToken) {
            $payment->setAdditionalInformation(
                VaultConfigProvider::IS_ACTIVE_CODE,
                1
            );
            $extensionAttributes = $this->getExtensionAttributes($payment);
            $extensionAttributes->setVaultPaymentToken($paymentToken);
        }
        $this->orderPaymentRepository->save($payment);
    }

    /**
     * @param array $handlingSubject
     * @return PaymentTokenInterface|null
     * @throws Exception
     */
    private function getVaultPaymentToken(array $handlingSubject): ?PaymentTokenInterface
    {
        try {
            $billToken = $this->subjectReader->readBillToken($handlingSubject);
            $expMonth = $this->subjectReader->readExpMonth($handlingSubject);
            $expYear = $this->subjectReader->readExpYear($handlingSubject);
            $type = $this->subjectReader->readCardType($handlingSubject);
            $last4 = $this->subjectReader->readCardLast4($handlingSubject);
        } catch (Exception $e) {
            $this->logger->critical($e);
            return null;
        }

        $paymentToken = $this->paymentTokenFactory->create(PaymentTokenFactoryInterface::TOKEN_TYPE_CREDIT_CARD);
        $paymentToken->setGatewayToken($billToken);
        $paymentToken->setExpiresAt($this->getExpirationDate($expMonth, $expYear));
        $creditCardType = $this->config->getCreditCardType($type);
        $tokenDetails = [
            'type' => $creditCardType,
            'last4' => $last4,
            'expMonth' => $expMonth,
            'expYear' => $expYear,
        ];

        $paymentToken->setTokenDetails($this->convertDetailsToJSON($tokenDetails));

        return $paymentToken;
    }

    /**
     * Get payment extension attributes
     * @param InfoInterface $payment
     * @return OrderPaymentExtensionInterface
     */
    private function getExtensionAttributes(InfoInterface $payment): OrderPaymentExtensionInterface
    {
        $extensionAttributes = $payment->getExtensionAttributes();
        if (null === $extensionAttributes) {
            $extensionAttributes = $this->paymentExtensionFactory->create();
            $payment->setExtensionAttributes($extensionAttributes);
        }
        return $extensionAttributes;
    }

    /**
     * Convert payment token details to JSON
     * @param array[][] $details
     * @return string
     */
    private function convertDetailsToJSON(array $details): string
    {
        try {
            return $this->serializer->serialize($details);
        } catch (Exception $e) {
            $this->logger->debug($e);
            return '{}';
        }
    }

    /**
     * @param string|int $expMonth
     * @param string|int $expYear
     * @return string
     * @throws Exception
     */
    private function getExpirationDate(string $expMonth, string $expYear): string
    {
        $expDate = new DateTime(
            $expYear
            . '-'
            . $expMonth
            . '-'
            . '01'
            . ' '
            . '00:00:00',
            new DateTimeZone('UTC')
        );
        $expDate->add(new DateInterval('P1M'));
        return $expDate->format('Y-m-d 00:00:00');
    }
}
