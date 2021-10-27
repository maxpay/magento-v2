<?php

declare(strict_types=1);

namespace Maxpay\Payment\ViewModel;

use Magento\Checkout\Model\Session;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Payment\Gateway\Data\Order\OrderAdapterFactory;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Maxpay\Payment\Gateway\Config\Config;
use Maxpay\Payment\Gateway\Helper\PrepareProductHelper;
use Maxpay\Payment\Gateway\Helper\PrepareUserHelper;
use Maxpay\Payment\Model\AuthorizeByToken;
use Maxpay\Payment\Model\TokenFactory;
use Maxpay\Payment\Model\TokenRepository;
use Psr\Log\LoggerInterface as Logger;

/**
 * Maxpay SessionIframe class
 */
class SessionIframe extends AbstractIframe
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var OrderAdapterFactory
     */
    private $orderAdapterFactory;

    /**
     * @param Session $checkoutSession
     * @param Config $config
     * @param Logger $logger
     * @param UrlInterface $urlBuilder
     * @param PrepareUserHelper $userHelper
     * @param PrepareProductHelper $productHelper
     * @param ManagerInterface $messageManager
     * @param TokenFactory $tokenFactory
     * @param TokenRepository $tokenRepository
     * @param AuthorizeByToken $authorizeToken
     * @param OrderAdapterFactory $orderAdapterFactory
     */
    public function __construct(
        Session              $checkoutSession,
        Config               $config,
        Logger               $logger,
        UrlInterface         $urlBuilder,
        PrepareUserHelper    $userHelper,
        PrepareProductHelper $productHelper,
        ManagerInterface     $messageManager,
        TokenFactory         $tokenFactory,
        TokenRepository      $tokenRepository,
        AuthorizeByToken     $authorizeToken,
        OrderAdapterFactory  $orderAdapterFactory
    )
    {
        $this->checkoutSession = $checkoutSession;
        $this->orderAdapterFactory = $orderAdapterFactory;
        parent::__construct($config, $logger, $urlBuilder, $userHelper, $productHelper, $messageManager, $tokenFactory, $tokenRepository, $authorizeToken);
    }

    /**
     * @return OrderAdapterInterface
     */
    protected function getOrder(): OrderAdapterInterface
    {
        $order = $this->checkoutSession->getLastRealOrder();

        return $this->orderAdapterFactory->create(
            ['order' => $order]
        );
    }

    /**
     * @param string $token
     * @return string
     */
    protected function getSuccessUrl(string $token): string
    {
        return $this->urlBuilder->getUrl('maxpay/redirect', $this->getRouteParams($token));
    }

    /**
     * @param string $token
     * @return string
     */
    protected function getDeclineUrl(string $token): string
    {
        return $this->urlBuilder->getUrl('maxpay/redirect/decline', $this->getRouteParams($token));
    }
}
