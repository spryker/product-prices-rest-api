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
use Generated\Shared\Transfer\RestCurrencyTransfer;
use Generated\Shared\Transfer\RestProductPriceAttributesTransfer;
use Spryker\ApiPlatform\State\Provider\AbstractStorefrontProvider;
use Spryker\Client\Currency\CurrencyClientInterface;
use Spryker\Client\Price\PriceClientInterface;
use Spryker\Client\PriceProduct\PriceProductClientInterface;
use Spryker\Client\PriceProductStorage\PriceProductStorageClientInterface;
use Spryker\Client\ProductStorage\ProductStorageClientInterface;
use Spryker\Glue\ProductPricesRestApi\Api\Storefront\Exception\ProductPricesExceptionFactory;
use Spryker\Service\Container\Attributes\Plugins;

class ConcreteProductPricesStorefrontProvider extends AbstractStorefrontProvider
{
    protected const string MAPPING_TYPE_SKU = 'sku';

    protected const string KEY_ID_PRODUCT_CONCRETE = 'id_product_concrete';

    protected const string KEY_ID_PRODUCT_ABSTRACT = 'id_product_abstract';

    protected const string URI_VAR_SKU = 'concreteProductSku';

    protected const string QUERY_PARAM_CURRENCY = 'currency';

    /**
     * @param array<\Spryker\Glue\ProductPricesRestApiExtension\Dependency\Plugin\RestProductPricesAttributesMapperPluginInterface> $restProductPricesAttributesMapperPlugins
     */
    public function __construct(
        protected ProductStorageClientInterface $productStorageClient,
        protected PriceProductStorageClientInterface $priceProductStorageClient,
        protected PriceProductClientInterface $priceProductClient,
        protected PriceClientInterface $priceClient,
        protected CurrencyClientInterface $currencyClient,
        protected ProductPricesExceptionFactory $exceptionFactory = new ProductPricesExceptionFactory(),
        #[Plugins(dependencyProviderMethod: 'getRestProductPricesAttributesMapperPlugins')]
        protected array $restProductPricesAttributesMapperPlugins = [],
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
            throw $this->exceptionFactory->createConcreteProductPricesNotFoundException();
        }

        $priceProductTransfers = $this->priceProductStorageClient->getResolvedPriceProductConcreteTransfers(
            (int)($productConcreteData[static::KEY_ID_PRODUCT_CONCRETE] ?? 0),
            (int)($productConcreteData[static::KEY_ID_PRODUCT_ABSTRACT] ?? 0),
        );

        $filterTransfer = $this->buildPriceProductFilter($productConcreteData);
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
            throw $this->exceptionFactory->createMissingConcreteProductSkuException();
        }

        $sku = (string)$this->getUriVariable(static::URI_VAR_SKU);

        if ($sku === '') {
            throw $this->exceptionFactory->createMissingConcreteProductSkuException();
        }

        return $sku;
    }

    /**
     * @param array<string, mixed> $productConcreteData
     */
    protected function buildPriceProductFilter(array $productConcreteData): PriceProductFilterTransfer
    {
        $filterTransfer = (new PriceProductFilterTransfer())
            ->setPriceProductResolveConditions(
                (new PriceProductResolveConditionsTransfer())->fromArray($productConcreteData, true),
            );

        $currencyIsoCode = $this->getRequest()->query->get(static::QUERY_PARAM_CURRENCY);

        if (!is_string($currencyIsoCode) || !in_array($currencyIsoCode, $this->currencyClient->getCurrencyIsoCodes(), true)) {
            return $filterTransfer->setCurrency($this->currencyClient->getCurrent());
        }

        return $filterTransfer
            ->setCurrency($this->currencyClient->fromIsoCode($currencyIsoCode))
            ->setCurrencyIsoCode($currencyIsoCode);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function buildPricesArray(CurrentProductPriceTransfer $currentProductPriceTransfer): array
    {
        $isGross = $this->priceClient->getCurrentPriceMode() === $this->priceClient->getGrossPriceModeIdentifier();
        $isNet = $this->priceClient->getCurrentPriceMode() === $this->priceClient->getNetPriceModeIdentifier();

        $prices = [];
        foreach ($currentProductPriceTransfer->getPrices() as $priceType => $amount) {
            $restProductPriceAttributesTransfer = (new RestProductPriceAttributesTransfer())
                ->setPriceTypeName($priceType)
                ->setCurrency(
                    (new RestCurrencyTransfer())->fromArray($currentProductPriceTransfer->getCurrencyOrFail()->toArray(), true),
                );

            if ($isGross) {
                $restProductPriceAttributesTransfer->setGrossAmount($amount);
            }
            if ($isNet) {
                $restProductPriceAttributesTransfer->setNetAmount($amount);
            }

            foreach ($this->restProductPricesAttributesMapperPlugins as $plugin) {
                $restProductPriceAttributesTransfer = $plugin->map(
                    $currentProductPriceTransfer,
                    $restProductPriceAttributesTransfer,
                );
            }

            $prices[] = $restProductPriceAttributesTransfer->toArray(true, true);
        }

        return $prices;
    }
}
