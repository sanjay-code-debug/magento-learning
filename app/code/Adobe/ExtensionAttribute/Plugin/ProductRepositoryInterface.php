<?php
namespace Adobe\ExtensionAttribute\Plugin;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface as MagentoRepository;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class ProductRepositoryInterface
{
    /**
     * @var CollectionFactory
     */
    private CollectionFactory $collectionFactory;

    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    public function afterGet(MagentoRepository $subject, ProductInterface $product)
    {
        if ($product->getExtensionAttributes() && $product->getExtensionAttributes()->getExaCustom()) {
            return $product;
        }

        $exaCustom = $this->getExaCustom($product->getId());
        $extensionAttribute = $product->getExtensionAttributes()->setExaCustom($exaCustom);
        $product->setExtensionAttributes($extensionAttribute);
        return $product;
    }

    /**
     * @param $productId
     * @return array|mixed|null
     */
    public function getExaCustom($productId)
    {
        return $this->collectionFactory->create()
            ->addFieldToFilter('entity_id', ['eq'=>$productId])
            ->getFirstItem()->getData('exa_custom')??"";
    }



    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $subject
     * @param \Magento\Catalog\Api\Data\ProductSearchResultsInterface $searchCriteria
     * @return \Magento\Catalog\Api\Data\ProductSearchResultsInterface
     */
    public function afterGetList(
        \Magento\Catalog\Api\ProductRepositoryInterface $subject,
        \Magento\Catalog\Api\Data\ProductSearchResultsInterface $searchCriteria
    ): \Magento\Catalog\Api\Data\ProductSearchResultsInterface {
        $products = [];
        foreach ($searchCriteria->getItems() as $entity) {
            /** Get Current Extension Attributes from Product */
            $extensionAttributes = $entity->getExtensionAttributes();
            $exaCustom = $this->getExaCustom($entity->getId());
            $extensionAttributes->setExaCustom($exaCustom);
            $entity->setExtensionAttributes($extensionAttributes);
            $products[] = $entity;
        }
        $searchCriteria->setItems($products);
        return $searchCriteria;
    }
}
