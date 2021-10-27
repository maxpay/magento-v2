<?php

declare(strict_types=1);

namespace Maxpay\Payment\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Maxpay TokenSearchResultsInterface interface
 */
interface TokenSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return TokenInterface
     */
    public function getItems();

    /**
     * @param array $items
     * @return void
     */
    public function setItems(array $items);

}
