<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\ProductPricesRestApi\Api\Storefront\Provider;

use Generated\Api\Storefront\AbstractProductPricesStorefrontResource;
use Generated\Shared\Transfer\CurrentProductPriceTransfer;
use Generated\Shared\Transfer\PriceProductFilterTransfer;
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

class AbstractProductPricesStorefrontProvider extends AbstractStorefrontProvider
{
    protected const string MAPPING_TYPE_SKU = 'sku';

    protected const string KEY_ID_PRODUCT_ABSTRACT = 'id_product_abstract';

    protected const string URI_VAR_SKU = 'abstractProductSku';

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
     * @return array<\Generated\Api\Storefront\AbstractProductPricesStorefrontResource>
     */
    protected function provideCollection(): array
    {
        $sku = $this->resolveAbstractProductSku();
        $localeName = $this->getLocale()->getLocaleNameOrFail();

        $productAbstractData = $this->productStorageClient->findProductAbstractStorageDataByMapping(
            static::MAPPING_TYPE_SKU,
            $sku,
            $localeName,
        );

        if ($productAbstractData === null) {
            throw new GlueApiException(
                Response::HTTP_NOT_FOUND,
                ProductPricesRestApiConfig::RESPONSE_CODE_ABSTRACT_PRODUCT_PRICES_NOT_FOUND,
                ProductPricesRestApiConfig::RESPONSE_DETAILS_ABSTRACT_PRODUCT_PRICES_NOT_FOUND,
            );
        }

        $priceProductTransfers = $this->priceProductStorageClient->getPriceProductAbstractTransfers(
            (int)($productAbstractData[static::KEY_ID_PRODUCT_ABSTRACT] ?? 0),
        );

        $filterTransfer = (new PriceProductFilterTransfer())->setCurrency($this->currencyClient->getCurrent());
        $currentProductPriceTransfer = $this->priceProductClient
            ->resolveProductPriceTransferByPriceProductFilter($priceProductTransfers, $filterTransfer);

        $resource = new AbstractProductPricesStorefrontResource();
        $resource->abstractProductSku = $sku;
        $resource->price = $currentProductPriceTransfer->getPrice();
        $resource->prices = $this->buildPricesArray($currentProductPriceTransfer);

        return [$resource];
    }

    protected function resolveAbstractProductSku(): string
    {
        if (!$this->hasUriVariable(static::URI_VAR_SKU)) {
            $this->throwMissingAbstractProductSku();
        }

        $sku = (string)$this->getUriVariable(static::URI_VAR_SKU);

        if ($sku === '') {
            $this->throwMissingAbstractProductSku();
        }

        return $sku;
    }

    protected function throwMissingAbstractProductSku(): never
    {
        throw new GlueApiException(
            Response::HTTP_BAD_REQUEST,
            ProductsRestApiConfig::RESPONSE_CODE_ABSTRACT_PRODUCT_SKU_IS_NOT_SPECIFIED,
            ProductsRestApiConfig::RESPONSE_DETAIL_ABSTRACT_PRODUCT_SKU_IS_NOT_SPECIFIED,
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
