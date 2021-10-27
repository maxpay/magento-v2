<?php

declare(strict_types=1);

namespace Maxpay\Payment\Model;

use Exception;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface as Request;
use Magento\Framework\Math\Random;
use Magento\Sales\Api\OrderRepositoryInterface as OrderRepository;
use Psr\Log\LoggerInterface as Logger;

/**
 * Maxpay AuthorizeByToken class
 */
class AuthorizeByToken
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var TokenRepository
     */
    private $tokenRepository;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var Random
     */
    private $mathRandom;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param Request $request
     * @param Logger $logger
     * @param TokenRepository $tokenRepository
     * @param CustomerSession $customerSession
     * @param CheckoutSession $checkoutSession
     * @param OrderRepository $orderRepository
     * @param Random $mathRandom
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        Request               $request,
        Logger                $logger,
        TokenRepository       $tokenRepository,
        CustomerSession       $customerSession,
        CheckoutSession       $checkoutSession,
        OrderRepository       $orderRepository,
        Random                $mathRandom,
        SearchCriteriaBuilder $searchCriteriaBuilder
    )
    {
        $this->request = $request;
        $this->logger = $logger;
        $this->tokenRepository = $tokenRepository;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
        $this->mathRandom = $mathRandom;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Authorize customer by token
     */
    public function authorizeSession()
    {
        try {
            $token = (string)$this->request->getParam('token');

            $tokenDO = $this->tokenRepository->getByToken($token);

            if (!$this->customerSession->isLoggedIn()) {
                $customerId = $tokenDO->getCustomerId();
                if (!empty($customerId)) {
                    $this->customerSession->loginById($customerId);
                }
            }
            if (empty($this->checkoutSession->getLastQuoteId())) {
                $orderId = $tokenDO->getOrderId();
                $order = $this->orderRepository->get($orderId);
                $this->checkoutSession->setLastQuoteId($order->getQuoteId())
                    ->setLastSuccessQuoteId($order->getQuoteId())
                    ->setLastOrderId($order->getEntityId())
                    ->setLastRealOrderId($order->getIncrementId())
                    ->setLastOrderStatus($order->getStatus());
            }
            $this->tokenRepository->delete($tokenDO);

        } catch (Exception $e) {
            $this->logger->debug($e);
        }
    }

    /**
     * Generate Maxpay token
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function generateToken(): string
    {
        do {
            $token = $this->mathRandom->getUniqueHash();
        } while (!$this->checkTokenUnique($token));

        return $token;
    }

    /**
     * Check generate token are different from DB
     *
     * @param $token
     * @return bool
     */
    protected function checkTokenUnique($token): bool
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('customer_token', $token)->create();
        $items = $this->tokenRepository->getList($searchCriteria);
        return !$items->getTotalCount();
    }

}
