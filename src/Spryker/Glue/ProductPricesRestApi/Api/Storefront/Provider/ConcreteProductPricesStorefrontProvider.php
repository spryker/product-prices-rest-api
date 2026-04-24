<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\ProductPricesRestApi\Api\Storefront\Provider;

use Generated\Api\Storefront\ConcreteProductPricesStorefrontResource;
use Generated\Shared\Transfer\CurrentProductPriceTransfer;
use Generated\Shared\Transfer\PriceProductFilterTransfer;
use Generated\Shared\Transfer\PriceProductResolveConditionsTransfer;
use Spryker\ApiPlatform\Exception\GlueApiException;
use Spryker\ApiPlatform\State\Provider\AbstractStorefrontProvider;
use Spryker\Client\Currency\CurrencyClientInterface;
use Spryker\Client\Price\PriceClientInterface;
use Spryker\Client\PriceProduct\PriceProductClientInterface;
use Spryker\Client\PriceProductStorage\PriceProductStorageClientInterface;
use Spryker\Client\ProductStorage\ProductStorageClientInterface;
use Spryker\Glue\ProductPricesRestApi\ProductPricesRestApiConfig;
use Spryker\Glue\ProductsRestApi\ProductsRestApiConfig;
use Symfony\Component\HttpFoundation\Response;

class ConcreteProductPricesStorefrontProvider extends AbstractStorefrontProvider
{
    protected const string MAPPING_TYPE_SKU = 'sku';

    protected const string KEY_ID_PRODUCT_CONCRETE = 'id_product_concrete';

    protected const string KEY_ID_PRODUCT_ABSTRACT = 'id_product_abstract';

    protected const string URI_VAR_SKU = 'concreteProductSku';

    public function __construct(
        protected ProductStorageClientInterface $productStorageClient,
        protected PriceProductStorageClientInterface $priceProductStorageClient,
        protected PriceProductClientInterface $priceProductClient,
        protected PriceClientInterface $priceClient,
        protected CurrencyClientInterface $currencyClient,
    ) {
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     *
     * @return array<\Generated\Api\Storefront\ConcreteProductPricesStorefrontResource>
     */
    protected function provideCollection(): array
    {
        $sku = $this->resolveConcreteProductSku();

        $localeName = $this->getLocale()->getLocaleNameOrFail();
        $productConcreteData = $this->productStorageClient->findProductConcreteStorageDataByMapping(
            static::MAPPING_TYPE_SKU,
            $sku,
            $localeName,
        );

        if ($productConcreteData === null) {
            throw new GlueApiException(
                Response::HTTP_NOT_FOUND,
                ProductPricesRestApiConfig::RESPONSE_CODE_CONCRETE_PRODUCT_PRICES_NOT_FOUND,
                ProductPricesRestApiConfig::RESPONSE_DETAILS_CONCRETE_PRODUCT_PRICES_NOT_FOUND,
            );
        }

        $priceProductTransfers = $this->priceProductStorageClient->getResolvedPriceProductConcreteTransfers(
            (int)($productConcreteData[static::KEY_ID_PRODUCT_CONCRETE] ?? 0),
            (int)($productConcreteData[static::KEY_ID_PRODUCT_ABSTRACT] ?? 0),
        );

        $filterTransfer = (new PriceProductFilterTransfer())
            ->setCurrency($this->currencyClient->getCurrent())
            ->setPriceProductResolveConditions(
                (new PriceProductResolveConditionsTransfer())->fromArray($productConcreteData, true),
            );

        $currentProductPriceTransfer = $this->priceProductClient
            ->resolveProductPriceTransferByPriceProductFilter($priceProductTransfers, $filterTransfer);

        $resource = new ConcreteProductPricesStorefrontResource();
        $resource->concreteProductSku = $sku;
        $resource->price = $currentProductPriceTransfer->getPrice();
        $resource->prices = $this->buildPricesArray($currentProductPriceTransfer);

        return [$resource];
    }

    protected function resolveConcreteProductSku(): string
    {
        if (!$this->hasUriVariable(static::URI_VAR_SKU)) {
            $this->throwMissingConcreteProductSku();
        }

        $sku = (string)$this->getUriVariable(static::URI_VAR_SKU);

        if ($sku === '') {
            $this->throwMissingConcreteProductSku();
        }

        return $sku;
    }

    protected function throwMissingConcreteProductSku(): never
    {
        throw new GlueApiException(
            Response::HTTP_BAD_REQUEST,
            ProductsRestApiConfig::RESPONSE_CODE_CONCRETE_PRODUCT_SKU_IS_NOT_SPECIFIED,
            ProductsRestApiConfig::RESPONSE_DETAIL_CONCRETE_PRODUCT_SKU_IS_NOT_SPECIFIED,
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function buildPricesArray(CurrentProductPriceTransfer $currentProductPriceTransfer): array
    {
        $currency = $currentProductPriceTransfer->getCurrency();
        $currencyData = $currency !== null ? [
            'code' => $currency->getCode(),
            'name' => $currency->getName(),
            'symbol' => $currency->getSymbol(),
        ] : null;

        $isGross = $this->priceClient->getCurrentPriceMode() === $this->priceClient->getGrossPriceModeIdentifier();
        $prices = [];

        foreach ($currentProductPriceTransfer->getPrices() as $priceType => $amount) {
            $prices[] = [
                'priceTypeName' => $priceType,
                'netAmount' => $isGross ? null : $amount,
                'grossAmount' => $isGross ? $amount : null,
                'currency' => $currencyData,
            ];
        }

        return $prices;
    }
}
