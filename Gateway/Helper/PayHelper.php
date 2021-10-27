<?php

declare(strict_types=1);

namespace Maxpay\Payment\Gateway\Helper;

use Exception;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface as Logger;

/**
 * Maxpay PayHelper class
 */
class PayHelper extends AbstractHelper implements ArgumentInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var UrlInterface
     */
    private $urlInterface;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param UrlInterface $urlInterface
     * @param Context $context
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        UrlInterface             $urlInterface,
        Context                  $context
    )
    {
        $this->orderRepository = $orderRepository;
        $this->urlInterface = $urlInterface;
        parent::__construct($context);
    }

    /**
     * @param string $orderId
     * @return bool
     */
    public function canPayOnline(string $orderId): bool
    {
        try {
            $order = $this->orderRepository->get($orderId);
        } catch (Exception $e) {
            $this->logger->debug($e);
        }
        return $order->getBaseTotalDue() > 0;
    }

    /**
     * @param string $orderId
     * @return string
     */
    public function getRedirectUrl(string $orderId): string
    {
        return $this->urlInterface->getUrl('maxpay/pay/online', ['orderId' => $orderId]);
    }
}
