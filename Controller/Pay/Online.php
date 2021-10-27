<?php

declare(strict_types=1);

namespace Maxpay\Payment\Controller\Pay;

use Exception;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface as Request;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\OrderRepositoryInterface as OrderRepository;
use Psr\Log\LoggerInterface as Logger;

/**
 * Maxpay Online class
 */
class Online implements HttpGetActionInterface
{
    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param PageFactory $pageFactory
     * @param RedirectFactory $redirectFactory
     * @param Session $customerSession
     * @param ManagerInterface $messageManager
     * @param Request $request
     * @param OrderRepository $orderRepository
     * @param Logger $logger
     */
    public function __construct(
        PageFactory      $pageFactory,
        RedirectFactory  $redirectFactory,
        Session          $customerSession,
        ManagerInterface $messageManager,
        Request          $request,
        OrderRepository  $orderRepository,
        Logger           $logger
    )
    {
        $this->pageFactory = $pageFactory;
        $this->redirectFactory = $redirectFactory;
        $this->customerSession = $customerSession;
        $this->messageManager = $messageManager;
        $this->request = $request;
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
    }

    /**
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $resultRedirect = $this->redirectFactory->create();

        if ($this->customerSession->isLoggedIn()) {
            $orderId = (int)$this->request->getParam('orderId');
            if ($orderId > 0) {
                try {
                    $order = $this->orderRepository->get($orderId);
                } catch (Exception $e) {
                    $this->logger->debug($e);
                    $this->messageManager->addErrorMessage(
                        __('Can\'t find order.')
                    );
                    return $resultRedirect->setPath('sales/order/history');
                }
            }
            $orderCustomerId = $order->getCustomerId();
            $sessionCustomerId = $this->customerSession->getCustomerId();
            if (isset($orderCustomerId) && isset($sessionCustomerId)) {
                if ($orderCustomerId !== $sessionCustomerId) {
                    $this->messageManager->addErrorMessage(
                        __('Can\'t find order.'));
                    return $resultRedirect->setPath('sales/order/history');
                }
            }
        }

        $resultPage = $this->pageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Maxpay Payment'));

        return $resultPage;
    }
}

