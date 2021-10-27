<?php

declare(strict_types=1);

namespace Maxpay\Payment\Controller\Redirect;

use Exception;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\RequestInterface as Request;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Message\ManagerInterface;
use Maxpay\Payment\Model\TokenRepository;

/**
 * Maxpay Decline class
 */
class Decline implements HttpPostActionInterface, CsrfAwareActionInterface
{
    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var TokenRepository
     */
    private $tokenRepository;

    /**
     * @param RedirectFactory $redirectFactory
     * @param ManagerInterface $messageManager
     * @param Request $request
     * @param TokenRepository $tokenRepository
     */
    public function __construct
    (
        RedirectFactory  $redirectFactory,
        ManagerInterface $messageManager,
        Request          $request,
        TokenRepository  $tokenRepository
    )
    {
        $this->redirectFactory = $redirectFactory;
        $this->messageManager = $messageManager;
        $this->request = $request;
        $this->tokenRepository = $tokenRepository;
    }

    /**
     * @return Redirect
     * @throws CouldNotDeleteException
     * @throws Exception
     */
    public function execute(): Redirect
    {
        $token = (string)$this->request->getParam('token');
        if ($token !== '') {
            $tokenDO = $this->tokenRepository->getByToken($token);
            $this->tokenRepository->delete($tokenDO);
        }

        $this->messageManager->addErrorMessage(
            __('Sorry, something went wrong while processing your payment. Please, contact to our manager')
        );
        $resultRedirect = $this->redirectFactory->create();
        return $resultRedirect->setPath('checkout/onepage/success');
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
