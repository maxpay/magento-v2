<?php

declare(strict_types=1);

namespace Maxpay\Payment\Model\Ui;

use Exception;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\Ui\TokenUiComponentInterface;
use Magento\Vault\Model\Ui\TokenUiComponentInterfaceFactory;
use Magento\Vault\Model\Ui\TokenUiComponentProviderInterface;
use Maxpay\Payment\Gateway\Config\Config;

/**
 * Maxpay TokenUiComponentProvider class
 */
class TokenUiComponentProvider implements TokenUiComponentProviderInterface
{
    /**
     * @var TokenUiComponentInterfaceFactory
     */
    private $componentFactory;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * TokenUiComponentProvider Constructor
     *
     * @param TokenUiComponentInterfaceFactory $componentFactory
     */
    public function __construct(
        TokenUiComponentInterfaceFactory $componentFactory,
        Json                             $serializer
    )
    {
        $this->componentFactory = $componentFactory;
        $this->serializer = $serializer;
    }

    /**core
     * Get UI component for token
     * @param PaymentTokenInterface $paymentToken
     * @return TokenUiComponentInterface
     */
    public function getComponentForToken(PaymentTokenInterface $paymentToken): TokenUiComponentInterface
    {
        try {
            $jsonDetails = $this->serializer->unserialize($paymentToken->getTokenDetails());
        } catch (Exception $e) {
            $jsonDetails = null;
        }

        $component = $this->componentFactory->create(
            [
                'config' => [
                    'code' => Config::CC_VAULT_CODE,
                    TokenUiComponentProviderInterface::COMPONENT_DETAILS => $jsonDetails,
                    TokenUiComponentProviderInterface::COMPONENT_PUBLIC_HASH => $paymentToken->getPublicHash()
                ],
                'name' => 'Maxpay_Payment/js/view/payment/method-renderer/vault'
            ]
        );

        return $component;
    }
}
