<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\ProductPricesRestApi\Api\Storefront\Exception;

use Spryker\ApiPlatform\Exception\GlueApiException;
use Spryker\Glue\ProductPricesRestApi\ProductPricesRestApiConfig;
use Spryker\Glue\ProductsRestApi\ProductsRestApiConfig;
use Symfony\Component\HttpFoundation\Response;

class ProductPricesExceptionFactory
{
    public function createAbstractProductPricesNotFoundException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_NOT_FOUND,
            ProductPricesRestApiConfig::RESPONSE_CODE_ABSTRACT_PRODUCT_PRICES_NOT_FOUND,
            ProductPricesRestApiConfig::RESPONSE_DETAILS_ABSTRACT_PRODUCT_PRICES_NOT_FOUND,
        );
    }

    public function createConcreteProductPricesNotFoundException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_NOT_FOUND,
            ProductPricesRestApiConfig::RESPONSE_CODE_CONCRETE_PRODUCT_PRICES_NOT_FOUND,
            ProductPricesRestApiConfig::RESPONSE_DETAILS_CONCRETE_PRODUCT_PRICES_NOT_FOUND,
        );
    }

    public function createMissingAbstractProductSkuException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_BAD_REQUEST,
            ProductsRestApiConfig::RESPONSE_CODE_ABSTRACT_PRODUCT_SKU_IS_NOT_SPECIFIED,
            ProductsRestApiConfig::RESPONSE_DETAIL_ABSTRACT_PRODUCT_SKU_IS_NOT_SPECIFIED,
        );
    }

    public function createMissingConcreteProductSkuException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_BAD_REQUEST,
            ProductsRestApiConfig::RESPONSE_CODE_CONCRETE_PRODUCT_SKU_IS_NOT_SPECIFIED,
            ProductsRestApiConfig::RESPONSE_DETAIL_CONCRETE_PRODUCT_SKU_IS_NOT_SPECIFIED,
        );
    }
}
