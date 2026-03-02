<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\ProductPricesRestApi\Processor\AbstractProductPrices;

use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;

interface AbstractProductPricesReaderInterface
{
    public function findAbstractProductPrices(RestRequestInterface $restRequest): RestResponseInterface;

    public function findAbstractProductPricesBySku(string $sku, RestRequestInterface $restRequest): ?RestResourceInterface;
}
