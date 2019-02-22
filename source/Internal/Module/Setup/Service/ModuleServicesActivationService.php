<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Internal\Module\Setup\Service;

use OxidEsales\EshopCommunity\Internal\Adapter\ShopAdapterInterface;
use OxidEsales\EshopCommunity\Internal\Application\Dao\ProjectYamlDaoInterface;
use OxidEsales\EshopCommunity\Internal\Application\DataObject\DIConfigWrapper;
use OxidEsales\EshopCommunity\Internal\Application\DataObject\DIServiceWrapper;
use OxidEsales\EshopCommunity\Internal\Application\Exception\NoServiceYamlException;
use OxidEsales\EshopCommunity\Internal\Module\Setup\Event\ServicesYamlConfigurationErrorEvent;
use OxidEsales\EshopCommunity\Internal\Module\Setup\Exception\ServicesYamlConfigurationError;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 */
class ModuleServicesActivationService implements ModuleServicesActivationServiceInterface
{

    /**
     * @var ProjectYamlDaoInterface $dao
     */
    private $dao;

    /**
     * @var ShopAdapterInterface
     */
    private $shopAdapter;

    /**
     * @var EventDispatcherInterface $eventDispatcher
     */
    public $eventDispatcher;

    /**
     * ModuleServicesActivationService constructor.
     *
     * @param ProjectYamlDaoInterface  $dao
     * @param EventDispatcherInterface $eventDispatcher
     * @param ShopAdapterInterface     $shopAdapter
     */
    public function __construct(
        ProjectYamlDaoInterface $dao,
        EventDispatcherInterface $eventDispatcher,
        ShopAdapterInterface $shopAdapter
    ) {
        $this->dao = $dao;
        $this->eventDispatcher = $eventDispatcher;
        $this->shopAdapter = $shopAdapter;
    }

    /**
     * @param string $moduleId
     * @param int    $shopId
     * @return void
     * @throws \OxidEsales\EshopCommunity\Internal\Application\Exception\MissingServiceException
     */
    public function activateModuleServices(string $moduleId, int $shopId)
    {
        $moduleConfigFile = $this->getModuleServicesFilePath($moduleId);
        try {
            $moduleConfig = $this->getModuleConfig($moduleConfigFile);
        } catch (NoServiceYamlException $e) {
            return;
        };

        $projectConfig = $this->dao->loadProjectConfigFile();
        $projectConfig->addImport($moduleConfigFile);

        /** @var DIServiceWrapper $service */
        foreach ($moduleConfig->getServices() as $service) {
            if (!$service->isShopAware()) {
                continue;
            }
            if ($projectConfig->hasService($service->getKey())) {
                $service = $projectConfig->getService($service->getKey());
            }
            $service->addActiveShops([$shopId]);
            $projectConfig->addOrUpdateService($service);
        }

        $this->dao->saveProjectConfigFile($projectConfig);
    }


    /**
     * @param string $moduleId
     * @param int    $shopId
     * @return void
     * @throws \OxidEsales\EshopCommunity\Internal\Application\Exception\MissingServiceException
     */
    public function deactivateModuleServices(string $moduleId, int $shopId)
    {
        $moduleConfigFile = $this->getModuleServicesFilePath($moduleId);
        try {
            $moduleConfig = $this->getModuleConfig($moduleConfigFile);
        } catch (NoServiceYamlException $e) {
            return;
        } catch (ServicesYamlConfigurationError $e) {
            // it could never have been activated so there is nothing to deactivate
            // and we can safely ignore this deactivation request
            return;
        }

        $projectConfig = $this->dao->loadProjectConfigFile();

        /** @var DIServiceWrapper $service */
        foreach ($moduleConfig->getServices() as $service) {
            if (!$service->isShopAware()) {
                continue;
            }
            $service = $projectConfig->getService($service->getKey());
            $service->removeActiveShops([$shopId]);
            $projectConfig->addOrUpdateService($service);
        }

        $this->dao->saveProjectConfigFile($projectConfig);
    }

    /**
     * @param string $moduleConfigFile
     *
     * @return DIConfigWrapper
     * @throws NoServiceYamlException
     */
    private function getModuleConfig(string $moduleConfigFile): DIConfigWrapper
    {
        if (!file_exists($moduleConfigFile)) {
            throw new NoServiceYamlException();
        }

        $moduleConfig = $this->dao->loadDIConfigFile($moduleConfigFile);
        if (! $moduleConfig->checkServiceClassesCanBeLoaded()) {
            $this->eventDispatcher->dispatch(
                ServicesYamlConfigurationErrorEvent::NAME,
                new ServicesYamlConfigurationErrorEvent($moduleConfigFile)
            );
            throw new ServicesYamlConfigurationError();
        }

        return $moduleConfig;
    }

    /**
     * @param string $moduleId
     * @return string
     */
    private function getModuleServicesFilePath(string $moduleId): string
    {
        return $this->shopAdapter->getModuleFullPath($moduleId) . DIRECTORY_SEPARATOR . 'services.yaml';
    }
}
