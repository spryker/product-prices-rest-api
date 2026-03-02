<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\ProductPricesRestApi\Processor\Mapper;

use ArrayObject;
use Generated\Shared\Transfer\CurrentProductPriceTransfer;
use Generated\Shared\Transfer\RestCurrencyTransfer;
use Generated\Shared\Transfer\RestPriceProductTransfer;
use Generated\Shared\Transfer\RestProductPriceAttributesTransfer;
use Generated\Shared\Transfer\RestProductPricesAttributesTransfer;
use Generated\Shared\Transfer\RestWishlistItemsAttributesTransfer;
use Generated\Shared\Transfer\WishlistItemTransfer;
use Spryker\Glue\ProductPricesRestApi\Dependency\Client\ProductPricesRestApiToPriceClientInterface;

class ProductPricesMapper implements ProductPricesMapperInterface
{
    /**
     * @var \Spryker\Glue\ProductPricesRestApi\Dependency\Client\ProductPricesRestApiToPriceClientInterface
     */
    protected $priceClient;

    /**
     * @var string|null
     */
    protected static $currentPriceMode;

    /**
     * @var string|null
     */
    protected static $grossPriceModeIdentifier;

    /**
     * @var string|null
     */
    protected static $netPriceModeIdentifier;

    /**
     * @var array<\Spryker\Glue\ProductPricesRestApiExtension\Dependency\Plugin\RestProductPricesAttributesMapperPluginInterface>
     */
    protected $restProductPricesAttributesMapperPlugins;

    /**
     * @param \Spryker\Glue\ProductPricesRestApi\Dependency\Client\ProductPricesRestApiToPriceClientInterface $priceClient
     * @param array<\Spryker\Glue\ProductPricesRestApiExtension\Dependency\Plugin\RestProductPricesAttributesMapperPluginInterface> $restProductPricesAttributesMapperPlugins
     */
    public function __construct(
        ProductPricesRestApiToPriceClientInterface $priceClient,
        array $restProductPricesAttributesMapperPlugins
    ) {
        $this->priceClient = $priceClient;
        $this->restProductPricesAttributesMapperPlugins = $restProductPricesAttributesMapperPlugins;
    }

    public function mapCurrentProductPriceTransferToRestProductPricesAttributesTransfer(
        CurrentProductPriceTransfer $currentProductPriceTransfer
    ): RestProductPricesAttributesTransfer {
        /** @todo: This mapping should be changed after decision about price filtering in api. */
        $productPricesRestAttributesTransfer = (new RestProductPricesAttributesTransfer())
            ->setPrice($currentProductPriceTransfer->getPrice());
        foreach ($currentProductPriceTransfer->getPrices() as $priceType => $amount) {
            $restProductPriceAttributesTransfer = $this->getRestProductPriceAttributesTransfer($currentProductPriceTransfer, $priceType, $amount);
            $restProductPriceAttributesTransfer = $this->executeRestProductPriceAttributesMapperPlugins(
                $currentProductPriceTransfer,
                $restProductPriceAttributesTransfer,
            );

            $productPricesRestAttributesTransfer->addPrice($restProductPriceAttributesTransfer);
        }

        return $productPricesRestAttributesTransfer;
    }

    public function mapWishlistItemTransferPricesToRestWishlistItemsAttributesTransfer(
        WishlistItemTransfer $wishlistItemTransfer,
        RestWishlistItemsAttributesTransfer $restWishlistItemsAttributesTransfer
    ): RestWishlistItemsAttributesTransfer {
        $restPriceProductTransfers = new ArrayObject();

        foreach ($wishlistItemTransfer->getPrices() as $priceProductTransfer) {

            /** @var \Generated\Shared\Transfer\MoneyValueTransfer $moneyValueTransfer */
            $moneyValueTransfer = $priceProductTransfer->getMoneyValue();
            $restPriceProductTransfer = (new RestPriceProductTransfer())
                ->fromArray($moneyValueTransfer->toArray(), true);

            if ($priceProductTransfer->getPriceType()) {
                /** @var \Generated\Shared\Transfer\PriceTypeTransfer $priceTypeTransfer */
                $priceTypeTransfer = $priceProductTransfer->getPriceType();
                $restPriceProductTransfer->setPriceTypeName($priceTypeTransfer->getName());
            }

            $restPriceProductTransfers->append($restPriceProductTransfer);
        }

        return $restWishlistItemsAttributesTransfer->setPrices($restPriceProductTransfers);
    }

    protected function getRestProductPriceAttributesTransfer(
        CurrentProductPriceTransfer $currentProductPriceTransfer,
        string $priceType,
        int $amount
    ): RestProductPriceAttributesTransfer {
        $restProductPriceAttributesTransfer = new RestProductPriceAttributesTransfer();

        $restProductPriceAttributesTransfer->setPriceTypeName($priceType);
        $restProductPriceAttributesTransfer->setCurrency(
            (new RestCurrencyTransfer())
                ->fromArray($currentProductPriceTransfer->getCurrencyOrFail()->toArray(), true),
        );

        if ($this->getCurrentPriceMode() === $this->getGrossPriceModeIdentifier()) {
            $restProductPriceAttributesTransfer->setGrossAmount($amount);

            return $restProductPriceAttributesTransfer;
        }
        if ($this->getCurrentPriceMode() === $this->getNetPriceModeIdentifier()) {
            $restProductPriceAttributesTransfer->setNetAmount($amount);

            return $restProductPriceAttributesTransfer;
        }

        return $restProductPriceAttributesTransfer;
    }

    public function executeRestProductPriceAttributesMapperPlugins(
        CurrentProductPriceTransfer $currentProductPriceTransfer,
        RestProductPriceAttributesTransfer $restProductPriceAttributesTransfer
    ): RestProductPriceAttributesTransfer {
        foreach ($this->restProductPricesAttributesMapperPlugins as $restProductPricesAttributesMapperPlugin) {
            $restProductPriceAttributesTransfer = $restProductPricesAttributesMapperPlugin->map(
                $currentProductPriceTransfer,
                $restProductPriceAttributesTransfer,
            );
        }

        return $restProductPriceAttributesTransfer;
    }

    protected function getCurrentPriceMode(): string
    {
        if (!static::$currentPriceMode) {
            static::$currentPriceMode = $this->priceClient->getCurrentPriceMode();
        }

        return static::$currentPriceMode;
    }

    protected function getGrossPriceModeIdentifier(): string
    {
        if (static::$grossPriceModeIdentifier === null) {
            static::$grossPriceModeIdentifier = $this->priceClient->getGrossPriceModeIdentifier();
        }

        return static::$grossPriceModeIdentifier;
    }

    protected function getNetPriceModeIdentifier(): string
    {
        if (static::$netPriceModeIdentifier === null) {
            static::$netPriceModeIdentifier = $this->priceClient->getNetPriceModeIdentifier();
        }

        return static::$netPriceModeIdentifier;
    }
}
