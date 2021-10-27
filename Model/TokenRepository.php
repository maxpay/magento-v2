<?php

declare(strict_types=1);

namespace Maxpay\Payment\Model;

use Exception;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Maxpay\Payment\Api\Data\TokenInterface;
use Maxpay\Payment\Api\Data\TokenSearchResultsInterface;
use Maxpay\Payment\Api\Data\TokenSearchResultsInterfaceFactory;
use Maxpay\Payment\Api\TokenRepositoryInterface;
use Maxpay\Payment\Gateway\Config\Config;
use Maxpay\Payment\Model\ResourceModel\Token as ResourceModel;
use Maxpay\Payment\Model\ResourceModel\Token\CollectionFactory as TokenCollectionFactory;

class TokenRepository implements TokenRepositoryInterface
{
    /**
     * @var ResourceModel
     */
    private ResourceModel $resourceModel;

    /**
     * @var TokenCollectionFactory
     */
    private $tokenCollectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var TokenSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param ResourceModel $resourceModel
     * @param TokenCollectionFactory $tokenCollectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param TokenSearchResultsInterfaceFactory $searchResultsFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param DateTime $dateTime
     * @param Config $config
     */
    public function __construct(
        ResourceModel                      $resourceModel,
        TokenCollectionFactory             $tokenCollectionFactory,
        CollectionProcessorInterface       $collectionProcessor,
        TokenSearchResultsInterfaceFactory $searchResultsFactory,
        SearchCriteriaBuilder              $searchCriteriaBuilder,
        DateTime                           $dateTime,
        Config                             $config
    )
    {
        $this->resourceModel = $resourceModel;
        $this->tokenCollectionFactory = $tokenCollectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->dateTime = $dateTime;
        $this->config = $config;
    }

    /**
     * @param string $token
     * @return TokenInterface
     * @throws Exception
     */
    public function getByToken(string $token): TokenInterface
    {
        $expirationDate = $this->getExpirationDate();
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('customer_token', $token)
            ->addFilter('created_at', $expirationDate, 'gt');

        $records = $this->getList($searchCriteria->create());

        $items = $records->getItems();
        if (empty($items)) {
            throw new Exception('Sorry, that token is unavailable');
        }

        return current($items);
    }

    /**
     * @param TokenInterface $token
     * @return TokenInterface
     * @throws AlreadyExistsException
     */
    public function save(TokenInterface $token): TokenInterface
    {
        $this->resourceModel->save($token);
        return $token;
    }

    /**
     * @param TokenInterface $token
     * @return bool true on success
     * @throws CouldNotDeleteException
     */
    public function delete(TokenInterface $token): bool
    {
        try {
            $this->resourceModel->delete($token);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete the entry: %1', $exception->getMessage())
            );
        }

        return true;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return TokenSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): TokenSearchResultsInterface
    {
        $collection = $this->tokenCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }

    /**
     * @return string
     */
    private function getExpirationDate(): string
    {
        $tokenLifetime = $this->config->getTokenLifetime();
        $currentTime = $this->dateTime->gmtTimestamp();

        return $this->dateTime->gmtDate('Y-m-d H:i:s', $currentTime - $tokenLifetime);
    }
}
