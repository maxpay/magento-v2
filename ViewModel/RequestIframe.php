<?php

declare(strict_types=1);

namespace Maxpay\Payment\ViewModel;

use Magento\Framework\App\RequestInterface as Request;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Payment\Gateway\Data\Order\OrderAdapterFactory;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Sales\Api\OrderRepositoryInterface as OrderRepository;
use Maxpay\Payment\Gateway\Config\Config;
use Maxpay\Payment\Gateway\Helper\PrepareProductHelper;
use Maxpay\Payment\Gateway\Helper\PrepareUserHelper;
use Maxpay\Payment\Model\AuthorizeByToken;
use Maxpay\Payment\Model\TokenFactory;
use Maxpay\Payment\Model\TokenRepository;
use Psr\Log\LoggerInterface as Logger;

/**
 * Maxpay RequestIframe class
 */
class RequestIframe extends AbstractIframe
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var OrderAdapterFactory
     */
    private $orderAdapterFactory;

    /**
     * @param Request $request
     * @param OrderRepository $orderRepository
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
        Request              $request,
        OrderRepository      $orderRepository,
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
        $this->request = $request;
        $this->orderRepository = $orderRepository;
        $this->orderAdapterFactory = $orderAdapterFactory;
        parent::__construct($config, $logger, $urlBuilder, $userHelper, $productHelper, $messageManager, $tokenFactory, $tokenRepository, $authorizeToken);
    }

    /**
     * @return OrderAdapterInterface
     */
    protected function getOrder(): OrderAdapterInterface
    {
        $orderId = $this->request->getParam('orderId');
        $order = $this->orderRepository->get($orderId);

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
        return $this->urlBuilder->getUrl('maxpay/redirect/online', $this->getRouteParams($token));
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
