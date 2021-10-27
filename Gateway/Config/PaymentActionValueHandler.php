<?php

declare(strict_types=1);

namespace Maxpay\Payment\Gateway\Config;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Config\ValueHandlerInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Model\MethodInterface;

/**
 * Maxpay PaymentActionValueHandler class
 */
class PaymentActionValueHandler implements ValueHandlerInterface
{
    /**
     * @param array $subject
     * @param null $storeId
     * @return string|null
     * @throws LocalizedException
     */
    public function handle(array $subject, $storeId = null): ?string
    {
        $paymentAction = null;
        /* @var PaymentDataObjectInterface $payment */
        if (isset($subject['payment'])) {
            $payment = $subject['payment'];
        }

        if ($payment->getPayment() !== null && $payment->getPayment()->getMethodInstance()->getCode() === Config::CC_VAULT_CODE) {
            $paymentAction = MethodInterface::ACTION_AUTHORIZE;
        }

        return $paymentAction;
    }
}
