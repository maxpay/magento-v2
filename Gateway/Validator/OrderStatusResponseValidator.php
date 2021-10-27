<?php

declare(strict_types=1);

namespace Maxpay\Payment\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Magento\Sales\Api\OrderRepositoryInterface as OrderRepository;
use Maxpay\Payment\Gateway\Config\Config;
use Maxpay\Payment\Gateway\Helper\SubjectReader;

/**
 * Maxpay OrderStatusResponseValidator class
 */
class OrderStatusResponseValidator extends AbstractValidator
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @param SubjectReader $subjectReader
     * @param OrderRepository $orderRepository
     * @param ResultInterfaceFactory $resultFactory
     */
    public function __construct(
        SubjectReader          $subjectReader,
        OrderRepository        $orderRepository,
        ResultInterfaceFactory $resultFactory
    )
    {
        $this->subjectReader = $subjectReader;
        $this->orderRepository = $orderRepository;
        parent::__construct($resultFactory);
    }

    /**
     * @param array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject): ResultInterface
    {
        $orderStatus = $this->subjectReader->readOrderStatus($validationSubject);
        $isValid = $this->checkOrderStatus($orderStatus);

        if (!$isValid) {
            $paymentDO = $this->subjectReader->readPayment($validationSubject);
            $message = $this->subjectReader->readMessage($validationSubject);

            $orderId = $paymentDO->getOrder()->getId();
            $order = $this->orderRepository->get($orderId);
            $order->addCommentToStatusHistory($message);

            $this->orderRepository->save($order);
        }

        return $this->createResult(
            $isValid,
            !$isValid ? [__('Wrong order status')] : []
        );
    }

    /**
     * @param $orderStatus
     * @return bool
     */
    private function checkOrderStatus(string $orderStatus): bool
    {
        return $orderStatus === Config::MAXPAY_ORDER_STATUS_SUCCESS;
    }

}
