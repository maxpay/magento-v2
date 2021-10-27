<?php

declare(strict_types=1);

namespace Maxpay\Payment\Gateway\Helper;

use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Maxpay\Lib\Exception\GeneralMaxpayException;
use Maxpay\Lib\Model\FixedProduct;

class PrepareProductHelper extends ScrineyHelper
{
    /**
     * @param OrderAdapterInterface $order
     * @return FixedProduct[]
     * @throws GeneralMaxpayException
     */
    public function getCustomProducts(OrderAdapterInterface $order): array
    {
        $currency = $order->getCurrencyCode();
        $orderId = $order->getOrderIncrementId();
        $totalAmount = $order->getGrandTotalAmount();

        return [
            new FixedProduct(
                $orderId,
                'Order id #' . $orderId,
                (float)$totalAmount,
                $currency
            )
        ];
    }

    /**
     * @param OrderAdapterInterface $order
     * @return FixedProduct
     * @throws GeneralMaxpayException
     */
    public function getCustomProduct(OrderAdapterInterface $order): FixedProduct
    {
        $currency = $order->getCurrencyCode();
        $orderId = $order->getOrderIncrementId();
        $totalAmount = $order->getGrandTotalAmount();

        return
            new FixedProduct(
                $orderId,
                'Order id #' . $orderId,
                (float)$totalAmount,
                $currency
            );
    }
}
