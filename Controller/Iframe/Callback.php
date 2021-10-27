<?php

declare(strict_types=1);

namespace Maxpay\Payment\Controller\Iframe;

use Exception;
use LogicException;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Webapi\Request;
use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order;
use Maxpay\Payment\Gateway\Config\Config;
use Maxpay\Payment\Model\CallbackDataMapper;
use Psr\Log\LoggerInterface as Logger;

/**
 * Maxpay Callback class
 */
class Callback implements HttpPostActionInterface, CsrfAwareActionInterface
{
    /**
     * Maxpay callback command
     */
    const CALLBACK = 'callback';

    /**
     * @var Request
     */
    private $request;

    /**
     * @var CommandPoolInterface
     */
    private $commandPool;

    /**
     * @var Order
     */
    private $order;

    /**
     * @var PaymentDataObjectFactory
     */
    private $paymentDataObjectFactory;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var CallbackDataMapper
     */
    private $callbackDataMapper;

    /**
     * @param Request $request
     * @param CommandPoolInterface $commandPool
     * @param Order $order
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param Json $serializer
     * @param JsonFactory $jsonFactory
     * @param Logger $logger
     * @param CallbackDataMapper $callbackDataMapper
     */
    public function __construct(
        Request                  $request,
        CommandPoolInterface     $commandPool,
        Order                    $order,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        Json                     $serializer,
        JsonFactory              $jsonFactory,
        Logger                   $logger,
        CallbackDataMapper       $callbackDataMapper
    )
    {
        $this->request = $request;
        $this->commandPool = $commandPool;
        $this->order = $order;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
        $this->serializer = $serializer;
        $this->resultJsonFactory = $jsonFactory;
        $this->logger = $logger;
        $this->callbackDataMapper = $callbackDataMapper;
    }

    /**
     * @return ResultInterface
     * @throws NotFoundException
     * @throws CommandException
     */
    public function execute(): ResultInterface
    {
        try {
            $params = $this->serializer->unserialize($this->request->getContent());
            $productList = $params['productList'];
            $orderId = null;
            if (is_array($params) && isset($params['productList'])) {
                $productListArray = reset($productList);
                if (is_array($productListArray) && isset($productListArray['productId'])) {
                    $orderId = (string)$productListArray['productId'];
                }
            }
            if (empty($orderId)) {
                throw new Exception('OrderId does not exist.');
            }

            $order = $this->order->loadByIncrementId($orderId);
            $orderPayment = $order->getPayment();
            if (!$orderPayment instanceof OrderPaymentInterface) {
                throw new Exception(
                    'Order Payment does not exist.'
                );
            }

            if ($orderPayment->getMethod() !== Config::CODE) {
                throw new LogicException(
                    'Order Payment Method does not supported by Maxpay Payment.'
                );
            }
        } catch (Exception $e) {
            $this->logger->critical($e);
            throw new NotFoundException(__('Order does not exist'));
        }

        $orderStatus = '';
        if (isset($params['status'])) {
            $orderStatus = $params['status'];
        }

        if (!in_array($orderStatus, [Config::MAXPAY_ORDER_STATUS_REFUND, Config::MAXPAY_ORDER_STATUS_DECLINE], true)) {
            $data = $this->callbackDataMapper->convert($params);
            $data['payment'] = $this->paymentDataObjectFactory->create($orderPayment);
            $this->commandPool->get(self::CALLBACK)->execute($data);
        }

        $result = $this->resultJsonFactory->create();
        $result->setData([]);

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException
    {
        return null;
    }

    /**
     * Disable Magento's CSRF validation.
     *
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): bool
    {
        return true;
    }
}
