<?php

declare(strict_types=1);

namespace Maxpay\Payment\Gateway\Helper;

use Exception;
use Magento\Directory\Api\CountryInformationAcquirerInterface as Country;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Gateway\Data\AddressAdapterInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;

/**
 * Maxpay ScrineyHelper class
 */
abstract class ScrineyHelper
{
    /**
     * @var Country
     */
    private $country;

    public function __construct(
        Country $country
    )
    {
        $this->country = $country;
    }

    /**
     * @param OrderAddressInterface|AddressAdapterInterface $billing
     * @return string
     * @throws NoSuchEntityException
     * @throws Exception
     */
    protected function getIso3Code($billing): string
    {
        if ($billing instanceof OrderAddressInterface || $billing instanceof AddressAdapterInterface) {
            return $this->country->getCountryInfo($billing->getCountryId())->getThreeLetterAbbreviation();
        } else {
            throw new Exception(__('Billing Address does not exist.'));
        }
    }
}
