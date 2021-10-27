<?php

declare(strict_types=1);

namespace Maxpay\Payment\Model;

use Magento\Framework\Model\AbstractModel;
use Maxpay\Payment\Api\Data\TokenInterface;

/**
 * Maxpay Token class
 */
class Token extends AbstractModel implements TokenInterface
{
    /**
     * Init Resource Model
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Token::class);
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return parent::getData(self::CUSTOMER_TOKEN);
    }

    /**
     * @param string $token
     * @return Token
     */
    public function setToken(string $token): Token
    {
        return $this->setData(self::CUSTOMER_TOKEN, $token);
    }

    /**
     * @return string|null
     */
    public function getOrderId(): string
    {
        return parent::getData(self::ORDER_ID);
    }

    /**
     * @param int $orderId
     * @return Token
     */
    public function setOrderId(int $orderId): Token
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * @return string|null
     */
    public function getCustomerId(): ?string
    {
        return parent::getData(self::CUSTOMER_ID);
    }

    /**
     * @param int|null $customerId
     * @return Token
     */
    public function setCustomerId(?int $customerId): Token
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * @return string
     */
    public function getCreatedAt(): string
    {
        return parent::getData(self::CREATED_AT);
    }

}
