<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\ProductPricesRestApi\Processor\Currency;

use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;
use Spryker\Glue\ProductPricesRestApi\Dependency\Client\ProductPricesRestApiToCurrencyClientInterface;
use Spryker\Glue\ProductPricesRestApi\ProductPricesRestApiConfig;

/**
 * @deprecated Will be removed without replacement.
 */
class CurrencyUpdater implements CurrencyUpdaterInterface
{
    /**
     * @var \Spryker\Glue\ProductPricesRestApi\Dependency\Client\ProductPricesRestApiToCurrencyClientInterface
     */
    protected $currencyClient;

    public function __construct(ProductPricesRestApiToCurrencyClientInterface $currencyClient)
    {
        $this->currencyClient = $currencyClient;
    }

    public function setCurrentCurrency(RestRequestInterface $restRequest): void
    {
        $currencyIsoCode = $this->getRequestParameter($restRequest, ProductPricesRestApiConfig::REQUEST_PARAMETER_CURRENCY);
        if ($currencyIsoCode) {
            $this->currencyClient->setCurrentCurrencyIsoCode($currencyIsoCode);
        }
    }

    protected function getRequestParameter(RestRequestInterface $restRequest, string $parameterName): string
    {
        /** @var string $response */
        $response = $restRequest->getHttpRequest()->query->get($parameterName, '');

        return $response;
    }
}
