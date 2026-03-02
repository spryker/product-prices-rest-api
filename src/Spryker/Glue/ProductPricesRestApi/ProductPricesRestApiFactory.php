<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\ProductPricesRestApi;

use Spryker\Glue\Kernel\AbstractFactory;
use Spryker\Glue\ProductPricesRestApi\Dependency\Client\ProductPricesRestApiToCurrencyClientInterface;
use Spryker\Glue\ProductPricesRestApi\Dependency\Client\ProductPricesRestApiToPriceClientInterface;
use Spryker\Glue\ProductPricesRestApi\Dependency\Client\ProductPricesRestApiToPriceProductClientInterface;
use Spryker\Glue\ProductPricesRestApi\Dependency\Client\ProductPricesRestApiToPriceProductStorageClientInterface;
use Spryker\Glue\ProductPricesRestApi\Dependency\Client\ProductPricesRestApiToProductStorageClientInterface;
use Spryker\Glue\ProductPricesRestApi\Dependency\Client\ProductPricesRestApiToStoreClientInterface;
use Spryker\Glue\ProductPricesRestApi\Processor\AbstractProductPrices\AbstractProductPricesReader;
use Spryker\Glue\ProductPricesRestApi\Processor\AbstractProductPrices\AbstractProductPricesReaderInterface;
use Spryker\Glue\ProductPricesRestApi\Processor\Builder\PriceProductFilterTransferBuilder;
use Spryker\Glue\ProductPricesRestApi\Processor\Builder\PriceProductFilterTransferBuilderInterface;
use Spryker\Glue\ProductPricesRestApi\Processor\ConcreteProductPrices\ConcreteProductPricesReader;
use Spryker\Glue\ProductPricesRestApi\Processor\ConcreteProductPrices\ConcreteProductPricesReaderInterface;
use Spryker\Glue\ProductPricesRestApi\Processor\Currency\CurrencyUpdater;
use Spryker\Glue\ProductPricesRestApi\Processor\Currency\CurrencyUpdaterInterface;
use Spryker\Glue\ProductPricesRestApi\Processor\Currency\CurrencyValidator;
use Spryker\Glue\ProductPricesRestApi\Processor\Currency\CurrencyValidatorInterface;
use Spryker\Glue\ProductPricesRestApi\Processor\Expander\AbstractProductPricesRelationshipExpander;
use Spryker\Glue\ProductPricesRestApi\Processor\Expander\AbstractProductPricesRelationshipExpanderInterface;
use Spryker\Glue\ProductPricesRestApi\Processor\Expander\ConcreteProductPricesRelationshipExpander;
use Spryker\Glue\ProductPricesRestApi\Processor\Expander\ConcreteProductPricesRelationshipExpanderInterface;
use Spryker\Glue\ProductPricesRestApi\Processor\Mapper\ProductPricesMapper;
use Spryker\Glue\ProductPricesRestApi\Processor\Mapper\ProductPricesMapperInterface;
use Spryker\Glue\ProductPricesRestApi\Processor\PriceMode\PriceModeUpdater;
use Spryker\Glue\ProductPricesRestApi\Processor\PriceMode\PriceModeUpdaterInterface;
use Spryker\Glue\ProductPricesRestApi\Processor\PriceMode\PriceModeValidator;
use Spryker\Glue\ProductPricesRestApi\Processor\PriceMode\PriceModeValidatorInterface;

/**
 * @method \Spryker\Glue\ProductPricesRestApi\ProductPricesRestApiConfig getConfig()
 */
class ProductPricesRestApiFactory extends AbstractFactory
{
    public function createProductPricesMapper(): ProductPricesMapperInterface
    {
        return new ProductPricesMapper(
            $this->getPriceClient(),
            $this->getRestProductPricesAttributesMapperPlugins(),
        );
    }

    public function createAbstractProductPricesReader(): AbstractProductPricesReaderInterface
    {
        return new AbstractProductPricesReader(
            $this->getProductStorageClient(),
            $this->getPriceProductStorageClient(),
            $this->getPriceProductClient(),
            $this->getResourceBuilder(),
            $this->createProductPricesMapper(),
            $this->createPriceProductFilterTransferBuilder(),
        );
    }

    public function createConcreteProductPricesReader(): ConcreteProductPricesReaderInterface
    {
        return new ConcreteProductPricesReader(
            $this->getProductStorageClient(),
            $this->getPriceProductStorageClient(),
            $this->getPriceProductClient(),
            $this->getResourceBuilder(),
            $this->createProductPricesMapper(),
            $this->createPriceProductFilterTransferBuilder(),
        );
    }

    public function createPriceProductFilterTransferBuilder(): PriceProductFilterTransferBuilderInterface
    {
        return new PriceProductFilterTransferBuilder(
            $this->getCurrencyClient(),
        );
    }

    public function createCurrencyValidator(): CurrencyValidatorInterface
    {
        return new CurrencyValidator(
            $this->getCurrencyClient(),
            $this->getStoreClient(),
        );
    }

    public function createPriceModeValidator(): PriceModeValidatorInterface
    {
        return new PriceModeValidator($this->getPriceClient());
    }

    /**
     * @deprecated Will be removed without replacement.
     *
     * @return \Spryker\Glue\ProductPricesRestApi\Processor\Currency\CurrencyUpdaterInterface
     */
    public function createCurrencyUpdater(): CurrencyUpdaterInterface
    {
        return new CurrencyUpdater($this->getCurrencyClient());
    }

    public function createPriceModeUpdater(): PriceModeUpdaterInterface
    {
        return new PriceModeUpdater($this->getPriceClient());
    }

    public function createAbstractProductPricesRelationshipExpander(): AbstractProductPricesRelationshipExpanderInterface
    {
        return new AbstractProductPricesRelationshipExpander(
            $this->createAbstractProductPricesReader(),
            $this->getConfig(),
        );
    }

    public function createConcreteProductPricesRelationshipExpander(): ConcreteProductPricesRelationshipExpanderInterface
    {
        return new ConcreteProductPricesRelationshipExpander(
            $this->createConcreteProductPricesReader(),
            $this->getConfig(),
        );
    }

    public function getPriceProductStorageClient(): ProductPricesRestApiToPriceProductStorageClientInterface
    {
        return $this->getProvidedDependency(ProductPricesRestApiDependencyProvider::CLIENT_PRICE_PRODUCT_STORAGE);
    }

    public function getProductStorageClient(): ProductPricesRestApiToProductStorageClientInterface
    {
        return $this->getProvidedDependency(ProductPricesRestApiDependencyProvider::CLIENT_PRODUCT_STORAGE);
    }

    public function getPriceProductClient(): ProductPricesRestApiToPriceProductClientInterface
    {
        return $this->getProvidedDependency(ProductPricesRestApiDependencyProvider::CLIENT_PRICE_PRODUCT);
    }

    public function getPriceClient(): ProductPricesRestApiToPriceClientInterface
    {
        return $this->getProvidedDependency(ProductPricesRestApiDependencyProvider::CLIENT_PRICE);
    }

    public function getCurrencyClient(): ProductPricesRestApiToCurrencyClientInterface
    {
        return $this->getProvidedDependency(ProductPricesRestApiDependencyProvider::CLIENT_CURRENCY);
    }

    public function getStoreClient(): ProductPricesRestApiToStoreClientInterface
    {
        return $this->getProvidedDependency(ProductPricesRestApiDependencyProvider::CLIENT_STORE);
    }

    /**
     * @return array<\Spryker\Glue\ProductPricesRestApiExtension\Dependency\Plugin\RestProductPricesAttributesMapperPluginInterface>
     */
    public function getRestProductPricesAttributesMapperPlugins(): array
    {
        return $this->getProvidedDependency(ProductPricesRestApiDependencyProvider::PLUGINS_REST_PRODUCT_PRICES_ATTRIBUTES_MAPPER);
    }
}
