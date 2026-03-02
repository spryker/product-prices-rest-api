<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\ProductPricesRestApi\Processor\PriceMode;

use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;

interface PriceModeUpdaterInterface
{
    public function switchPriceMode(RestRequestInterface $restRequest): void;
}
