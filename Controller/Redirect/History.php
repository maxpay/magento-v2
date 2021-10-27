<?php

declare(strict_types=1);

namespace Maxpay\Payment\Controller\Redirect;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;

/**
 * Maxpay History class
 */
class History implements HttpPostActionInterface, CsrfAwareActionInterface
{
    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @param RedirectFactory $redirectFactory
     */
    public function __construct
    (
        RedirectFactory $redirectFactory,
        Session         $customerSession
    )
    {
        $this->redirectFactory = $redirectFactory;
        $this->customerSession = $customerSession;
    }

    /**
     * @return Redirect
     */
    public function execute(): Redirect
    {
        $resultRedirect = $this->redirectFactory->create();
        $this->customerSession->isLoggedIn() ? $url = 'sales/order/history' : $url = '';
        return $resultRedirect->setPath($url);
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException
    {
        return null;
    }

    /**
     * Disable Magento's CSRF validation.
     *
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): bool
    {
        return true;
    }
}
