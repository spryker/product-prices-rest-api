<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\ProductPricesRestApi\Processor\Currency;

use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;

/**
 * @deprecated Will be removed without replacement.
 */
interface CurrencyUpdaterInterface
{
    public function setCurrentCurrency(RestRequestInterface $restRequest): void;
}
