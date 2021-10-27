<?php

declare(strict_types=1);

namespace Maxpay\Payment\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Maxpay TokenInterface interface
 */
interface TokenInterface extends ExtensibleDataInterface
{
    const CUSTOMER_TOKEN = 'customer_token';
    const ORDER_ID = 'order_id';
    const CUSTOMER_ID = 'customer_id';
    const CREATED_AT = 'created_at';

    /**
     * Get Maxpay token
     *
     * @return string
     */
    public function getToken(): string;

    /**
     * Set Maxpay token
     *
     * @param string $token
     */
    public function setToken(string $token);

    /**
     * Get order id
     *
     * @return string
     */
    public function getOrderId(): string;

    /**
     * Set order id
     *
     * @param int $orderId
     */
    public function setOrderId(int $orderId);

    /**
     * Get customer id
     *
     * @return string|null
     */
    public function getCustomerId(): ?string;

    /**
     * Set customer id
     *
     * @param int|null $customerId
     */
    public function setCustomerId(?int $customerId);

    /**
     * Get creation date
     *
     * @return string
     */
    public function getCreatedAt(): string;
}
