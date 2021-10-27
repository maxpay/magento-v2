<?php

declare(strict_types=1);

namespace Maxpay\Payment\Controller\Iframe;

use Exception;
use Magento\Checkout\Model\Session;
use Magento\Checkout\Model\Session\SuccessValidator;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Maxpay\Payment\Gateway\Config\Config;

class Index implements HttpGetActionInterface
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
     * @var SuccessValidator
     */
    private $successValidator;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @param PageFactory $pageFactory
     * @param RedirectFactory $redirectFactory
     * @param SuccessValidator $successValidator
     * @param Session $checkoutSession
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        PageFactory      $pageFactory,
        RedirectFactory  $redirectFactory,
        SuccessValidator $successValidator,
        Session          $checkoutSession,
        ManagerInterface $messageManager
    )
    {
        $this->pageFactory = $pageFactory;
        $this->redirectFactory = $redirectFactory;
        $this->successValidator = $successValidator;
        $this->checkoutSession = $checkoutSession;
        $this->messageManager = $messageManager;
    }

    /**
     * @return ResultInterface
     * @throws Exception
     */
    public function execute(): ResultInterface
    {
        $resultRedirect = $this->redirectFactory->create();

        $isValid = $this->successValidator->isValid();
        if ($isValid) {
            $order = $this->checkoutSession->getLastRealOrder();
            $orderPayment = $order->getPayment();
            if (!$orderPayment instanceof OrderPaymentInterface) {
                throw new Exception('Order Payment does not exist.');
            }

            $isValid = ($orderPayment->getMethod() === Config::CODE);
        }

        if (!$isValid) {
            $this->messageManager->addErrorMessage(
                __('Can\'t find order.')
            );
            return $resultRedirect->setPath('checkout/cart');
        }

        $resultPage = $this->pageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Maxpay Payment'));

        return $resultPage;
    }
}

