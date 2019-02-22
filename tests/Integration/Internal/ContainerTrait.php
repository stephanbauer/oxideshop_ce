<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Tests\Integration\Internal;

/**
 * @internal
 */
trait ContainerTrait
{
    private $container;

    protected function get(string $serviceId)
    {
        if ($this->container === null) {
            $this->container = (new TestContainerFactory())->create();
            $this->container->compile();
        }

        return $this->container->get($serviceId);
    }
}
