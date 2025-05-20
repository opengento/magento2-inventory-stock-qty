<?php
/**
 * Copyright © OpenGento, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Opengento\InventoryStockQty\Service;

use Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface;

class IsSalableQtyAvailableForDisplaying
{
    public function execute(float $productSalableQty, StockItemConfigurationInterface $stockItemConfig): bool
    {
        return (
            $stockItemConfig->getBackorders() === StockItemConfigurationInterface::BACKORDERS_NO
            || (
                $stockItemConfig->getBackorders() !== StockItemConfigurationInterface::BACKORDERS_NO
                && $stockItemConfig->getMinQty() < 0
            )
            ) && $productSalableQty > 0 && $productSalableQty <= $stockItemConfig->getStockThresholdQty();
    }
}
