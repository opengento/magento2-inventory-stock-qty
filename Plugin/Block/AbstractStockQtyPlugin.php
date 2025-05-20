<?php
/**
 * Copyright © OpenGento, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Opengento\InventoryStockQty\Plugin\Block;

use Magento\CatalogInventory\Block\Stockqty\AbstractStockqty;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Exception\SkuIsNotAssignedToStockException;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface;
use Opengento\InventoryStockQty\Service\GetProductQtyLeft;
use Opengento\InventoryStockQty\Service\IsSalableQtyAvailableForDisplaying;

class AbstractStockQtyPlugin
{
    public function __construct(
        private IsSalableQtyAvailableForDisplaying $isSalableQtyAvailableForDisplaying,
        private GetProductQtyLeft $getProductQtyLeft,
        private StockByWebsiteIdResolverInterface $stockByWebsiteId,
        private GetStockItemConfigurationInterface $getStockItemConfiguration,
        private GetProductSalableQtyInterface $getProductSalableQty,
        private IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType,
    ) {}

    /**
     * @throws SkuIsNotAssignedToStockException
     * @throws InputException
     * @throws LocalizedException
     */
    public function aroundIsMsgVisible(AbstractStockqty $subject, callable $proceed): bool
    {
        $product = $subject->getProduct();
        $sku = $product->getSku();
        $stockId = (int)$this->stockByWebsiteId->execute((int)$product->getStore()->getWebsiteId())->getStockId();
        $stockItemConfig = $this->getStockItemConfiguration->execute($sku, $stockId);

        return $stockItemConfig->isManageStock()
            && $this->isSourceItemManagementAllowedForProductType->execute($subject->getProduct()->getTypeId())
            && $this->isSalableQtyAvailableForDisplaying->execute(
            $this->getProductSalableQty->execute($sku, $stockId),
            $stockItemConfig
        );
    }

    /**
     * @throws SkuIsNotAssignedToStockException
     * @throws InputException
     * @throws LocalizedException
     */
    public function aroundGetStockQtyLeft(AbstractStockqty $subject, callable $proceed): float
    {
        $product = $subject->getProduct();

        return $this->getProductQtyLeft->execute(
            $product->getSku(),
            (int)$this->stockByWebsiteId->execute(
                (int)$product->getStore()->getWebsiteId()
            )->getStockId()
        );
    }
}
