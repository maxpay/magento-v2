<?php

declare(strict_types=1);

namespace Maxpay\Payment\Model\Checkout;

use Magento\Checkout\Model\ConfigProviderInterface;
use Maxpay\Payment\Gateway\Config\Config;

/**
 * Class MaxpayConfigProvider
 * @package Maxpay\Payment\Model\Checkout
 */
class MaxpayConfigProvider implements ConfigProviderInterface
{
    public function getConfig(): array
    {
        return [
            'payment' => [
                Config::CODE => [
                    'maxpayRedirectUrl' => Config::PATH_REDIRECT_URL,
                    'ccVaultCode' => Config::CC_VAULT_CODE
                ]
            ]
        ];
    }
}
