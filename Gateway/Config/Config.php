<?php

declare(strict_types=1);

namespace Maxpay\Payment\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Payment\Gateway\Config\Config as GatewayConfig;

/**
 * Maxpay Configuration class
 */
class Config extends GatewayConfig implements ConfigInterface
{
    /**
     * @var Json
     */
    private $json;

    /**
     * @param Json $json
     * @param ScopeConfigInterface $scopeConfig
     * @param null $methodCode
     * @param string $pathPattern
     */
    public function __construct(
        Json                 $json,
        ScopeConfigInterface $scopeConfig,
        $methodCode = null,
        string $pathPattern = GatewayConfig::DEFAULT_PATH_PATTERN
    )
    {
        $this->json = $json;
        parent::__construct($scopeConfig, $methodCode, $pathPattern);
    }

    public function isActive(?int $storeId = null): bool
    {
        return (bool)$this->getValue(self::XML_PATH_MAXPAY_ACTIVE, $storeId);
    }

    public function isTestModeEnabled(?int $storeId = null): bool
    {
        return (bool)$this->getValue(self::XML_PATH_MAXPAY_TEST, $storeId);
    }

    public function getIframeHeight(): string
    {
        return (string)$this->getValue(self::XML_PATH_MAXPAY_IFRAME_HEIGHT);
    }

    public function getIframeWidth(): string
    {
        return (string)$this->getValue(self::XML_PATH_MAXPAY_IFRAME_WIDTH);
    }

    public function isDebugModeEnabled(?int $storeId = null): bool
    {
        return (bool)$this->getValue(self::XML_PATH_MAXPAY_TEST, $storeId);
    }

    public function getPublicKey(?int $storeId = null): string
    {
        if ($this->getValue(self::XML_PATH_MAXPAY_TEST, $storeId)) {
            return (string)$this->getValue(self::TEST_PUBLIC_KEY, $storeId);
        } else {
            return (string)$this->getValue(self::PUBLIC_KEY, $storeId);
        }
    }

    public function getPrivateKey(?int $storeId = null): string
    {
        if ($this->getValue(self::XML_PATH_MAXPAY_TEST, $storeId)) {
            return (string)$this->getValue(self::TEST_PRIVATE_KEY, $storeId);
        } else {
            return (string)$this->getValue(self::PRIVATE_KEY, $storeId);
        }
    }

    public function getTransactionId(): string
    {
        return (string)$this->getValue(self::MAXPAY_TRANSACTION_ID);
    }

    public function getUserId(): string
    {
        return (string)$this->getValue(self::MAXPAY_USER_ID);
    }

    public function getTransactionAmount(): int
    {
        return (int)$this->getValue(self::MAXPAY_ORDER_AMOUNT);
    }

    public function getTransactionCurrency(): string
    {
        return (string)$this->getValue(self::MAXPAY_ORDER_CURRENCY);
    }

    /**
     *  Retrieve mapper between Magento and MAXPAY card types
     *
     * @return array
     */
    public function getCcTypesMapper(): array
    {
        return $this->json->unserialize($this->getValue(self::KEY_CC_TYPES_MAPPER)) ?? [];

    }

    public function getDefaultCardType(): string
    {
        return (string)$this->getValue(self::CC_DEFAULT_TYPE);
    }

    public function getCreditCardType(string $type): string
    {
        $mapper = $this->getCcTypesMapper();
        if (isset($mapper[$type])) {
            return $mapper[$type];
        }
        return $this->getDefaultCardType();
    }

    public function getTokenLifetime(): int
    {
        return (int)$this->getValue(self::TOKEN_LIFETIME);
    }
}
