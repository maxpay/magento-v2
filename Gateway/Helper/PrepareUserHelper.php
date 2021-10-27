<?php

declare(strict_types=1);

namespace Maxpay\Payment\Gateway\Helper;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Gateway\Data\AddressAdapterInterface;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Maxpay\Lib\Exception\GeneralMaxpayException;
use Maxpay\Lib\Model\UserInfo;

class PrepareUserHelper extends ScrineyHelper
{
    /**
     * @param OrderAdapterInterface $order
     * @return UserInfo
     * @throws GeneralMaxpayException
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function prepareUserProfile(OrderAdapterInterface $order): UserInfo
    {
        $billing = $order->getBillingAddress();
        if (is_null($billing)) {
            throw new LocalizedException(__('Billing Address does not exist.'));
        }

        $email = $billing->getEmail();
        $firstName = $billing->getFirstname();
        $lastName = $billing->getLastname();
        $ISO3Code = $this->getIso3Code($billing);
        $city = $billing->getCity();
        $postalCode = $billing->getPostcode();
        $address = null;

        if ($billing instanceof OrderAddressInterface) {
            $addressArray = $billing->getStreet();
            $address = implode(' ', $addressArray);
        } elseif ($billing instanceof AddressAdapterInterface) {
            $address = $billing->getStreetLine1() . $billing->getStreetLine2();
        }

        $phone = $billing->getTelephone();

        return new UserInfo(
            $email,
            $firstName,
            $lastName,
            $ISO3Code,
            $city,
            $postalCode,
            $address,
            $phone
        );
    }
}
