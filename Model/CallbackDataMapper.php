<?php

declare(strict_types=1);

namespace Maxpay\Payment\Model;

use Exception;
use Maxpay\Payment\Gateway\Config\Config;
use Psr\Log\LoggerInterface as Logger;

/**
 * Maxpay CallbackDataMapper class
 */
class CallbackDataMapper
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param Logger $logger
     */
    public function __construct(
        Logger $logger
    )
    {
        $this->logger = $logger;
    }

    /**
     * @param $requestParams
     * @return array|void
     */
    public function convert($requestParams)
    {
        try {
            return [
                Config::MAXPAY_USER_ID => $requestParams['uniqueUserId'],
                Config::MAXPAY_TRANSACTION_ID => $requestParams['uniqueTransactionId'],
                Config::MAXPAY_ORDER_CURRENCY => $requestParams['currency'],
                Config::MAXPAY_ORDER_AMOUNT => $requestParams['totalAmount'],
                Config::MAXPAY_ORDER_STATUS => $requestParams['status'],
                Config::MAXPAY_RESPONSE_MESSAGE => $requestParams['message'],
                Config::MAXPAY_BILL_TOKEN => $requestParams['billToken'] ?? null,
                Config::CC_TYPE => $requestParams['customParameters']['custom_card_brand'] ?? null,
                Config::CC_LAST4 => $requestParams['customParameters']['custom_card_last'] ?? null,
                Config::CC_EXP_MONTH => $requestParams['customParameters']['custom_card_expiration_month'] ?? null,
                Config::CC_EXP_YEAR => $requestParams['customParameters']['custom_card_expiration_year'] ?? null
            ];

        } catch (Exception $e) {
            $this->logger->debug($e);
        }
    }
}
