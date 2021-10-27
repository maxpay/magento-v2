<?php

declare(strict_types=1);

namespace Maxpay\Payment\Gateway\Helper;

use InvalidArgumentException;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Maxpay\Payment\Gateway\Config\Config;

/**
 * Maxpay SubjectReader class
 */
class SubjectReader
{
    /**
     * Reads payment from subject
     *
     * @param mixed[] $subject
     * @return PaymentDataObjectInterface
     */
    public function readPayment(array $subject): PaymentDataObjectInterface
    {
        if (!isset($subject['payment'])
            || !$subject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new InvalidArgumentException('Payment data object should be provided');
        }

        return $subject['payment'];
    }

    /**
     * Reads order status from subject
     * @param array $subject
     * @return string
     */
    public function readOrderStatus(array $subject): string
    {
        if (!isset($subject[Config::MAXPAY_ORDER_STATUS])) {
            throw new InvalidArgumentException('The "status" field does not exists');
        }

        return $subject[Config::MAXPAY_ORDER_STATUS];
    }

    /**
     * Reads BillToken from subject
     *
     * @param array $subject
     * @return mixed|null
     */
    public function readBillToken(array $subject)
    {
        return $subject[Config::MAXPAY_BILL_TOKEN] ?? null;
    }

    /**
     * Reads card Expiration month from subject
     *
     * @param array $subject
     * @return string
     */
    public function readExpMonth(array $subject): string
    {
        if (!isset($subject[Config::CC_EXP_MONTH])) {
            throw new InvalidArgumentException('The "card_expiration_month" field does not exists');
        }

        return (string)$subject[Config::CC_EXP_MONTH];
    }

    /**
     * Reads card Expiration year from subject
     *
     * @param array $subject
     * @return string
     */
    public function readExpYear(array $subject): string
    {
        if (!isset($subject[Config::CC_EXP_YEAR])) {
            throw new InvalidArgumentException('The "card_expiration_year" field does not exists');
        }

        return (string)$subject[Config::CC_EXP_YEAR];
    }

    /**
     * Reads card type from subject
     *
     * @param array $subject
     * @return mixed
     */
    public function readCardType(array $subject)
    {
        if (!isset($subject[Config::CC_TYPE])) {
            throw new InvalidArgumentException('The "card_brand" field does not exists');
        }

        return $subject[Config::CC_TYPE];
    }

    /**
     * Reads last 4 digits of card number from subject
     *
     * @param array $subject
     * @return mixed
     */
    public function readCardLast4(array $subject)
    {
        if (!isset($subject[Config::CC_LAST4])) {
            throw new InvalidArgumentException('The "card_last4" field does not exists');
        }

        return $subject[Config::CC_LAST4];
    }

    /**
     * Reads Transaction ID from subject
     *
     * @param mixed[] $subject
     * @return string
     */
    public function readTransactionId(array $subject): string
    {
        if (!isset($subject[Config::MAXPAY_TRANSACTION_ID])) {
            throw new InvalidArgumentException('The "transactionId" field does not exists');
        }

        return $subject[Config::MAXPAY_TRANSACTION_ID];
    }

    /**
     * Reads order amount from subject
     *
     * @param mixed[] $subject
     * @return string
     */
    public function readAmount(array $subject): string
    {
        if (!isset($subject[Config::MAXPAY_ORDER_AMOUNT])) {
            throw new InvalidArgumentException('The "amount" field does not exists');
        }

        return $subject[Config::MAXPAY_ORDER_AMOUNT];
    }

    /**
     * Reads order currency from subject
     *
     * @param mixed[] $subject
     * @return string
     */
    public function readCurrency(array $subject): string
    {
        if (!isset($subject[Config::MAXPAY_ORDER_CURRENCY])) {
            throw new InvalidArgumentException('The "currency" field does not exists');
        }

        return $subject[Config::MAXPAY_ORDER_CURRENCY];
    }

    /**
     * Reads response message from subject
     *
     * @param mixed[] $subject
     * @return string
     */
    public function readMessage(array $subject): string
    {
        if (!isset($subject[Config::MAXPAY_RESPONSE_MESSAGE])) {
            throw new InvalidArgumentException('The "message" field does not exists');
        }

        return $subject[Config::MAXPAY_RESPONSE_MESSAGE];
    }
}
