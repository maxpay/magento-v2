<?php

declare(strict_types=1);

namespace Maxpay\Payment\Model\Logger;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

/**
 * Maxpay Handler class
 * @package Maxpay\Payment\Model\Logger
 */
class Handler extends Base
{
    /**
     * Logging level
     *
     * @var int
     */
    protected $loggerType = Logger::DEBUG;

    /**
     * File name
     *
     * @var string
     */
    protected $fileName = '/var/log/maxpay_payment.log';
}
