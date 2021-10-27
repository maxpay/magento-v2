<?php

declare(strict_types=1);

namespace Maxpay\Payment\Gateway\Validator;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Webapi\Request;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Maxpay\Payment\Gateway\Config\Config;
use Maxpay\Scriney;
use Psr\Log\LoggerInterface as Logger;

/**
 * Maxpay ChecksumResponseValidator class
 */
class ChecksumResponseValidator extends AbstractValidator
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Logger
     */
    private $logger;
    /**
     * @var Json
     */
    private $json;

    /**
     * @param Request $request
     * @param Config $config
     * @param Logger $logger
     * @param ResultInterfaceFactory $resultFactory
     */
    public function __construct(
        Request                $request,
        Config                 $config,
        Logger                 $logger,
        ResultInterfaceFactory $resultFactory,
        Json                   $json
    )
    {
        $this->request = $request;
        $this->config = $config;
        $this->logger = $logger;
        $this->json = $json;
        parent::__construct($resultFactory);
    }

    /**
     * @param array $validationSubject
     * @return ResultInterface
     * @throws \Maxpay\Lib\Exception\GeneralMaxpayException
     */
    public function validate(array $validationSubject): ResultInterface
    {
        $logger = null;
        if ($this->config->isDebugModeEnabled()) {
            $logger = $this->logger;
        }
        $data = file_get_contents('php://input');
        $body = $this->json->unserialize($data);
        $data = $this->json->serialize($body);

        $headers = $this->request->getHeaders()->toArray();

        $scriney = new Scriney($this->config->getPublicKey(), $this->config->getPrivateKey(), $logger);
        $isValid = $scriney->validateCallback($data, $headers);

        return $this->createResult(
            $isValid,
            !$isValid ? [__('Wrong transaction checkSum')] : []
        );
    }

}
