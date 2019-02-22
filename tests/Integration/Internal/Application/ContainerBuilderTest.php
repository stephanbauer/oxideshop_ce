<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Tests\Integration\Internal\Application;

use OxidEsales\EshopCommunity\Internal\Application\ContainerBuilder;
use OxidEsales\EshopCommunity\Internal\Application\Utility\BasicContext;
use OxidEsales\Facts\Edition\EditionSelector;
use PHPUnit\Framework\TestCase;

class ContainerBuilderTest extends TestCase
{
    public function testWhenCeServicesLoaded()
    {
        $context = $this->makeContextStub();
        $context->method('getEdition')->willReturn(EditionSelector::COMMUNITY);
        $context->method('getGeneratedProjectFilePath')->willReturn('not_existing.yaml');
        $container = $this->makeContainer($context);

        $this->assertSame('CE service!', $container->get('oxid_esales.tests.internal.dummy_executor')->execute());
    }

    public function testWhenPeOverwritesMainServices()
    {
        $context = $this->makeContextStub();
        $context->method('getEdition')->willReturn(EditionSelector::PROFESSIONAL);
        $context->method('getGeneratedProjectFilePath')->willReturn('not_existing.yaml');
        $container = $this->makeContainer($context);

        $this->assertSame('Service overwriting for PE!', $container->get('oxid_esales.tests.internal.dummy_executor')->execute());
    }

    public function testWhenEeOverwritesMainServices()
    {
        $context = $this->makeContextStub();
        $context->method('getEdition')->willReturn(EditionSelector::ENTERPRISE);
        $context->method('getGeneratedProjectFilePath')->willReturn('not_existing.yaml');
        $container = $this->makeContainer($context);

        $this->assertSame('Service overwriting for EE!', $container->get('oxid_esales.tests.internal.dummy_executor')->execute());
    }

    public function testWhenProjectOverwritesMainServices()
    {
        $context = $this->makeContextStub();
        $context->method('getEdition')->willReturn(EditionSelector::COMMUNITY);
        $context->method('getGeneratedProjectFilePath')->willReturn(__DIR__ . '/Fixtures/Project/' . BasicContext::GENERATED_PROJECT_FILE_NAME);
        $container = $this->makeContainer($context);

        $this->assertSame('Service overwriting for Project!', $container->get('oxid_esales.tests.internal.dummy_executor')->execute());
    }

    public function testWhenProjectOverwritesEditions()
    {
        $context = $this->makeContextStub();
        $context->method('getEdition')->willReturn(EditionSelector::ENTERPRISE);
        $context->method('getGeneratedProjectFilePath')->willReturn(__DIR__ . '/Fixtures/Project/' . BasicContext::GENERATED_PROJECT_FILE_NAME);
        $container = $this->makeContainer($context);

        $this->assertSame('Service overwriting for Project!', $container->get('oxid_esales.tests.internal.dummy_executor')->execute());
    }

    public function testGraphQLTypeIntegration()
    {
        $context = $this->makeContextStub();
        $context->method('getEdition')->willReturn(EditionSelector::COMMUNITY);
        $context->method('getGeneratedProjectFilePath')->willReturn('not_existing.yaml');
        $container = $this->makeContainer($context);

        $this->assertTrue($container->get('query_type_factory')->verifySubType());
        $this->assertTrue($container->get('mutation_type_factory')->verifySubType());
    }

    /**
     * @param BasicContext $context
     * @return \Symfony\Component\DependencyInjection\Container
     */
    private function makeContainer(BasicContext $context): \Symfony\Component\DependencyInjection\Container
    {
        $containerBuilder = new ContainerBuilder($context);
        $container = $containerBuilder->getContainer();
        $container->compile();
        return $container;
    }

    /**
     * @return BasicContext|\PHPUnit\Framework\MockObject\MockObject
     */
    private function makeContextStub()
    {
        $context = $this->getMockBuilder(BasicContext::class)->getMock();
        $context->method('getCommunityEditionSourcePath')->willReturn(__DIR__ . '/Fixtures/CE');
        $context->method('getProfessionalEditionRootPath')->willReturn(__DIR__ . '/Fixtures/PE');
        $context->method('getEnterpriseEditionRootPath')->willReturn(__DIR__ . '/Fixtures/EE');
        return $context;
    }
}
