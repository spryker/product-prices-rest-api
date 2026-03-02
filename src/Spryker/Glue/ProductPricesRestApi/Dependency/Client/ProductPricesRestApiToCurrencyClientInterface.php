<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\ProductPricesRestApi\Dependency\Client;

use Generated\Shared\Transfer\CurrencyTransfer;

interface ProductPricesRestApiToCurrencyClientInterface
{
    /**
     * @return \Generated\Shared\Transfer\CurrencyTransfer
     */
    public function getCurrent();

    public function setCurrentCurrencyIsoCode(string $currencyIsoCode): void;

    public function fromIsoCode(string $isoCode): CurrencyTransfer;

    /**
     * @return array<string>
     */
    public function getCurrencyIsoCodes(): array;
}
