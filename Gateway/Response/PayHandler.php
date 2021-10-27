<?php

declare(strict_types=1);

namespace Maxpay\Payment\Gateway\Response;

use Exception;
use Magento\Framework\DB\Transaction;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\OrderRepositoryInterface as OrderRepository;
use Magento\Sales\Api\TransactionRepositoryInterface as TransactionRepository;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\InvoiceRepository;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface as TransactionBuilder;
use Magento\Sales\Model\Service\InvoiceService;
use Maxpay\Payment\Gateway\Config\Config;
use Maxpay\Payment\Gateway\Helper\SubjectReader;

/**
 * Maxpay PayHandler class
 */
class PayHandler implements HandlerInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var InvoiceService
     */
    private $invoiceService;

    /**
     * @var InvoiceRepository
     */
    private $invoiceRepository;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var Transaction
     */
    private $transaction;

    /**
     * @var TransactionRepository
     */
    private $transactionRepository;

    /**
     * @var TransactionBuilder
     */
    private $transactionBuilder;

    /**
     * @param SubjectReader $subjectReader
     * @param InvoiceService $invoiceService
     * @param InvoiceRepository $invoiceRepository
     * @param OrderRepository $orderRepository
     * @param Transaction $transaction
     * @param TransactionRepository $transactionRepository
     * @param TransactionBuilder $transactionBuilder
     */
    public function __construct(
        SubjectReader         $subjectReader,
        InvoiceService        $invoiceService,
        InvoiceRepository     $invoiceRepository,
        OrderRepository       $orderRepository,
        Transaction           $transaction,
        TransactionRepository $transactionRepository,
        TransactionBuilder    $transactionBuilder
    )
    {
        $this->subjectReader = $subjectReader;
        $this->invoiceService = $invoiceService;
        $this->invoiceRepository = $invoiceRepository;
        $this->orderRepository = $orderRepository;
        $this->transaction = $transaction;
        $this->transactionRepository = $transactionRepository;
        $this->transactionBuilder = $transactionBuilder;
    }

    /**
     * @param array $handlingSubject
     * @param array $response
     * @throws LocalizedException
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);

        $orderId = $paymentDO->getOrder()->getId();
        $order = $this->orderRepository->get($orderId);

        $payment = $order->getPayment();
        if (is_null($payment)) {
            throw new Exception('Payment does not exist.');
        }

        $transactionId = $this->subjectReader->readTransactionId($handlingSubject) ?? $payment->getAdditionalInformation(Config::MAXPAY_TRANSACTION_ID);
        $payment->setAdditionalInformation(Config::MAXPAY_TRANSACTION_ID, $transactionId);

        /** @var Order $order */
        $invoice = $this->invoiceService->prepareInvoice($order);
        $invoice->setTransactionId($transactionId);
        $invoice->register();
        $invoice->pay();
        $invoice->getOrder()->setState(Order::STATE_PROCESSING)
            ->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING));
        $this->invoiceRepository->save($invoice);

        $transactionSave = $this->transaction->addObject(
            $invoice
        )->addObject(
            $invoice->getOrder()
        );
        $transactionSave->save();

        $transaction = $this->transactionBuilder->setPayment($payment)
            ->setOrder($order)
            ->setTransactionId($transactionId)
            ->setFailSafe(true)
            ->build(TransactionInterface::TYPE_CAPTURE);

        $formattedAmount = $order->getBaseCurrency()->formatTxt($invoice->getBaseGrandTotal());
        if ($order->getBaseCurrencyCode() != $order->getOrderCurrencyCode()) {
            $formattedAmount = $formattedAmount . ' [' . $order->formatPriceTxt($order->getBaseGrandTotal()) . ']';
        }

        $message = 'Captured amount of %1.';
        $message = __($message, $formattedAmount);
        $payment->addTransactionCommentsToOrder($transaction, $message);

        $this->orderRepository->save($order);
        $this->transactionRepository->save($transaction);
    }
}
