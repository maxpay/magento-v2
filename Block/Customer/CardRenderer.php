<?php

declare(strict_types=1);

namespace Maxpay\Payment\Block\Customer;

use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Block\AbstractCardRenderer;
use Maxpay\Payment\Gateway\Config\Config;

/**
 * Maxpay CardRenderer class
 */
class CardRenderer extends AbstractCardRenderer
{
    /**
     * Can render specified token
     *
     * @param PaymentTokenInterface $token
     * @return boolean
     */
    public function canRender(PaymentTokenInterface $token): bool
    {
        return $token->getPaymentMethodCode() === Config::CODE;
    }

    /**
     * Get last 4 digits of card
     * @return string
     */
    public function getNumberLast4Digits(): string
    {
        $result = '';
        $tokenDetails = $this->getTokenDetails();
        if (is_array($tokenDetails) && isset($tokenDetails['last4'])) {
            $result = (string)$tokenDetails['last4'];
        }

        return $result;
    }

    /**
     * Get expiration date
     *
     * @return string
     */
    public function getExpDate(): string
    {
        $result = '';
        $tokenDetails = $this->getTokenDetails();
        if (is_array($tokenDetails) && isset($tokenDetails['expMonth']) && isset($tokenDetails['expYear'])) {
            $result = (string)$tokenDetails['expMonth'] . '/' . (string)$tokenDetails['expYear'];
        }

        return $result;
    }

    /**
     * Get credit card icon url
     *
     * @return string
     */
    public function getIconUrl(): string
    {
        $result = '';
        $tokenDetails = $this->getTokenDetails();
        $iconForType = $this->getIconForType((string)$tokenDetails['type']);
        if (is_array($iconForType) && isset($iconForType['url'])) {
            $result = (string)$iconForType['url'];
        }

        return $result;
    }

    /**
     * Get credit card icon height
     *
     * @return int
     */
    public function getIconHeight(): int
    {
        $result = 0;
        $tokenDetails = $this->getTokenDetails();
        $iconForType = $this->getIconForType((string)$tokenDetails['type']);
        if (is_array($iconForType) && isset($iconForType['height'])) {
            $result = (int)$iconForType['height'];
        }

        return $result;
    }

    /**
     * Get credit card icon width
     *
     * @return int
     */
    public function getIconWidth(): int
    {
        $result = 0;
        $tokenDetails = $this->getTokenDetails();
        $iconForType = $this->getIconForType((string)$tokenDetails['type']);
        if (is_array($iconForType) && isset($iconForType['width'])) {
            $result = (int)$iconForType['width'];
        }

        return $result;
    }
}
