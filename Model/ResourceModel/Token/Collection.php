<?php

declare(strict_types=1);

namespace Maxpay\Payment\Model\ResourceModel\Token;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    public function _construct()
    {
        $this->_init(\Maxpay\Payment\Model\Token::class, \Maxpay\Payment\Model\ResourceModel\Token::class);
    }
}
