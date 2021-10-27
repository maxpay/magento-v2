<?php

declare(strict_types=1);

namespace Maxpay\Payment\Gateway\Config;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Config\ValueHandlerInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;

/**
 * Maxpay CanAuthorizeValueHandler class
 */
class CanAuthorizeValueHandler implements ValueHandlerInterface
{
    /**
     * @param array $subject
     * @param int|null $storeId
     * @return bool
     * @throws LocalizedException
     */
    public function handle(array $subject, $storeId = null): bool
    {
        $canAuthorize = false;
        /* @var PaymentDataObjectInterface $payment */
        if (isset($subject['payment'])) {
            $payment = $subject['payment'];
        }

        if ($payment->getPayment() !== null && $payment->getPayment()->getMethodInstance()->getCode() === Config::CC_VAULT_CODE) {
            $canAuthorize = true;
        }

        return $canAuthorize;
    }
}
