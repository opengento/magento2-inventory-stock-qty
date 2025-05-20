<?php
/**
 * Copyright © OpenGento, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Opengento\InventoryStockQty\Service;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryCatalogFrontendUi\Model\GetProductQtyLeft as BaseGetProductQtyLeft;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Exception\SkuIsNotAssignedToStockException;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;

class GetProductQtyLeft extends BaseGetProductQtyLeft
{
    public function __construct(
        private GetStockItemConfigurationInterface $getStockItemConfig,
        private GetProductSalableQtyInterface $getProductSalableQty,
        private IsSalableQtyAvailableForDisplaying $isSalableQtyAvailableForDisplaying,
    ) {}

    /**
     * @throws SkuIsNotAssignedToStockException
     * @throws InputException
     * @throws LocalizedException
     */
    public function execute(string $productSku, int $stockId): float
    {
        $productSalableQty = $this->getProductSalableQty->execute($productSku, $stockId);
        $stockItemConfig = $this->getStockItemConfig->execute($productSku, $stockId);

        return $this->isSalableQtyAvailableForDisplaying->execute($productSalableQty, $stockItemConfig)
            ? $productSalableQty
            : 0;
    }
}
