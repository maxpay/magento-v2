<?php

declare(strict_types=1);

namespace Maxpay\Payment\Controller\Redirect;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Maxpay\Payment\Model\AuthorizeByToken;

/**
 * Maxpay Online class
 */
class Online implements HttpPostActionInterface, CsrfAwareActionInterface
{
    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var AuthorizeByToken
     */
    private $authorize;

    /**
     * @param RedirectFactory $redirectFactory
     * @param AuthorizeByToken $authorize
     */
    public function __construct(RedirectFactory $redirectFactory, AuthorizeByToken $authorize)
    {
        $this->redirectFactory = $redirectFactory;
        $this->authorize = $authorize;
    }

    /**
     * @return Redirect
     */
    public function execute(): Redirect
    {
        $this->authorize->authorizeSession();
        $resultRedirect = $this->redirectFactory->create();
        return $resultRedirect->setPath('sales/order/history');
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
