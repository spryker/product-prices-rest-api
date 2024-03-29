<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\ProductPricesRestApi;

use Spryker\Glue\Kernel\AbstractBundleDependencyProvider;
use Spryker\Glue\Kernel\Container;
use Spryker\Glue\ProductPricesRestApi\Dependency\Client\ProductPricesRestApiToCurrencyClientBridge;
use Spryker\Glue\ProductPricesRestApi\Dependency\Client\ProductPricesRestApiToPriceClientBridge;
use Spryker\Glue\ProductPricesRestApi\Dependency\Client\ProductPricesRestApiToPriceProductClientBridge;
use Spryker\Glue\ProductPricesRestApi\Dependency\Client\ProductPricesRestApiToPriceProductStorageClientBridge;
use Spryker\Glue\ProductPricesRestApi\Dependency\Client\ProductPricesRestApiToProductStorageClientBridge;
use Spryker\Glue\ProductPricesRestApi\Dependency\Client\ProductPricesRestApiToStoreClientBridge;

/**
 * @method \Spryker\Glue\ProductPricesRestApi\ProductPricesRestApiConfig getConfig()
 */
class ProductPricesRestApiDependencyProvider extends AbstractBundleDependencyProvider
{
    /**
     * @var string
     */
    public const CLIENT_PRICE_PRODUCT_STORAGE = 'CLIENT_PRICE_PRODUCT_STORAGE';

    /**
     * @var string
     */
    public const CLIENT_PRODUCT_STORAGE = 'CLIENT_PRODUCT_STORAGE';

    /**
     * @var string
     */
    public const CLIENT_PRICE_PRODUCT = 'CLIENT_PRICE_PRODUCT';

    /**
     * @var string
     */
    public const CLIENT_PRICE = 'CLIENT_PRICE';

    /**
     * @var string
     */
    public const CLIENT_CURRENCY = 'CLIENT_CURRENCY';

    /**
     * @var string
     */
    public const CLIENT_STORE = 'CLIENT_STORE';

    /**
     * @var string
     */
    public const PLUGINS_REST_PRODUCT_PRICES_ATTRIBUTES_MAPPER = 'PLUGINS_REST_PRODUCT_PRICES_ATTRIBUTES_MAPPER';

    /**
     * @param \Spryker\Glue\Kernel\Container $container
     *
     * @return \Spryker\Glue\Kernel\Container
     */
    public function provideDependencies(Container $container): Container
    {
        $container = parent::provideDependencies($container);
        $container = $this->addPriceProductStorageClient($container);
        $container = $this->addProductStorageClient($container);
        $container = $this->addPriceProductClient($container);
        $container = $this->addPriceClient($container);
        $container = $this->addCurrencyClient($container);
        $container = $this->addStoreClient($container);
        $container = $this->addRestProductPricesAttributesMapperPlugins($container);

        return $container;
    }

    /**
     * @param \Spryker\Glue\Kernel\Container $container
     *
     * @return \Spryker\Glue\Kernel\Container
     */
    protected function addPriceProductStorageClient(Container $container): Container
    {
        $container->set(static::CLIENT_PRICE_PRODUCT_STORAGE, function (Container $container) {
            return new ProductPricesRestApiToPriceProductStorageClientBridge(
                $container->getLocator()->priceProductStorage()->client(),
            );
        });

        return $container;
    }

    /**
     * @param \Spryker\Glue\Kernel\Container $container
     *
     * @return \Spryker\Glue\Kernel\Container
     */
    protected function addProductStorageClient(Container $container): Container
    {
        $container->set(static::CLIENT_PRODUCT_STORAGE, function (Container $container) {
            return new ProductPricesRestApiToProductStorageClientBridge(
                $container->getLocator()->productStorage()->client(),
            );
        });

        return $container;
    }

    /**
     * @param \Spryker\Glue\Kernel\Container $container
     *
     * @return \Spryker\Glue\Kernel\Container
     */
    protected function addPriceProductClient(Container $container): Container
    {
        $container->set(static::CLIENT_PRICE_PRODUCT, function (Container $container) {
            return new ProductPricesRestApiToPriceProductClientBridge(
                $container->getLocator()->priceProduct()->client(),
            );
        });

        return $container;
    }

    /**
     * @param \Spryker\Glue\Kernel\Container $container
     *
     * @return \Spryker\Glue\Kernel\Container
     */
    protected function addPriceClient(Container $container): Container
    {
        $container->set(static::CLIENT_PRICE, function (Container $container) {
            return new ProductPricesRestApiToPriceClientBridge(
                $container->getLocator()->price()->client(),
            );
        });

        return $container;
    }

    /**
     * @param \Spryker\Glue\Kernel\Container $container
     *
     * @return \Spryker\Glue\Kernel\Container
     */
    protected function addCurrencyClient(Container $container): Container
    {
        $container->set(static::CLIENT_CURRENCY, function (Container $container) {
            return new ProductPricesRestApiToCurrencyClientBridge(
                $container->getLocator()->currency()->client(),
            );
        });

        return $container;
    }

    /**
     * @param \Spryker\Glue\Kernel\Container $container
     *
     * @return \Spryker\Glue\Kernel\Container
     */
    protected function addStoreClient(Container $container): Container
    {
        $container->set(static::CLIENT_STORE, function (Container $container) {
            return new ProductPricesRestApiToStoreClientBridge($container->getLocator()->store()->client());
        });

        return $container;
    }

    /**
     * @param \Spryker\Glue\Kernel\Container $container
     *
     * @return \Spryker\Glue\Kernel\Container
     */
    protected function addRestProductPricesAttributesMapperPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_REST_PRODUCT_PRICES_ATTRIBUTES_MAPPER, function () {
            return $this->getRestProductPricesAttributesMapperPlugins();
        });

        return $container;
    }

    /**
     * @return array<\Spryker\Glue\ProductPricesRestApiExtension\Dependency\Plugin\RestProductPricesAttributesMapperPluginInterface>
     */
    protected function getRestProductPricesAttributesMapperPlugins(): array
    {
        return [];
    }
}
