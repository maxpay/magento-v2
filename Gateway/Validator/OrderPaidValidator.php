<?php

declare(strict_types=1);

namespace Maxpay\Payment\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Magento\Sales\Api\OrderRepositoryInterface as OrderRepository;
use Maxpay\Payment\Gateway\Helper\SubjectReader;

/**
 * Maxpay OrderPaidValidator class
 */
class OrderPaidValidator extends AbstractValidator
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

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
        $paymentDO = $this->subjectReader->readPayment($validationSubject);

        $orderId = $paymentDO->getOrder()->getOrderIncrementId();
        $order = $this->orderRepository->get($orderId);
        $isValid = $order->getBaseTotalDue() > 0;

        return $this->createResult(
            $isValid,
            !$isValid ? [__('Order was already paid')] : []
        );
    }

}
