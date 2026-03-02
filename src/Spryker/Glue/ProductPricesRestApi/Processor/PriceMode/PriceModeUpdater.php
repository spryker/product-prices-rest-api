<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\ProductPricesRestApi\Processor\PriceMode;

use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;
use Spryker\Glue\ProductPricesRestApi\Dependency\Client\ProductPricesRestApiToPriceClientInterface;
use Spryker\Glue\ProductPricesRestApi\ProductPricesRestApiConfig;

class PriceModeUpdater implements PriceModeUpdaterInterface
{
    /**
     * @var \Spryker\Glue\ProductPricesRestApi\Dependency\Client\ProductPricesRestApiToPriceClientInterface
     */
    protected $priceClient;

    public function __construct(ProductPricesRestApiToPriceClientInterface $priceClient)
    {
        $this->priceClient = $priceClient;
    }

    public function switchPriceMode(RestRequestInterface $restRequest): void
    {
        $priceMode = $this->getRequestParameter($restRequest, ProductPricesRestApiConfig::REQUEST_PARAMETER_PRICE_MODE);
        if ($priceMode) {
            $this->priceClient->switchPriceMode($priceMode);
        }
    }

    protected function getRequestParameter(RestRequestInterface $restRequest, string $parameterName): string
    {
        /** @var string $response */
        $response = $restRequest->getHttpRequest()->query->get($parameterName, '');

        return $response;
    }
}
