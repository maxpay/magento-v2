<?php

declare(strict_types=1);

namespace Maxpay\Payment\Gateway\Command;

use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;

/**
 * Maxpay RefundCommand class
 */
class RefundCommand implements CommandInterface
{
    /**
     * @var HandlerInterface|null
     */
    private $handler;

    /**
     * @param HandlerInterface|null $handler
     */
    public function __construct(
        ?HandlerInterface $handler = null
    )
    {
        $this->handler = $handler;
    }

    /**
     * @param array $commandSubject
     * @return null
     */
    public function execute(array $commandSubject)
    {
        $response = [];

        if (null !== $this->handler) {
            $this->handler->handle(
                $commandSubject,
                $response
            );
        }

        return null;
    }
}
