<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Internal\Module\Setup\Service;

use OxidEsales\EshopCommunity\Internal\Module\Configuration\DataObject\ModuleConfiguration;
use OxidEsales\EshopCommunity\Internal\Module\Configuration\DataObject\ModuleSetting;
use OxidEsales\EshopCommunity\Internal\Module\Setup\Exception\ModuleSettingHandlerNotFoundException;
use OxidEsales\EshopCommunity\Internal\Module\Setup\Handler\ModuleSettingHandlerInterface;
use OxidEsales\EshopCommunity\Internal\Module\Setup\Validator\ModuleSettingValidatorInterface;

/**
 * @internal
 */
class ModuleSettingsHandlingService implements ModuleSettingsHandlingServiceInterface
{
    /**
     * @var array
     */
    private $moduleSettingHandlers = [];

    /**
     * @var array
     */
    private $moduleSettingValidators = [];

    /**
     * @param ModuleConfiguration $moduleConfiguration
     * @param int                 $shopId
     */
    public function handleOnActivation(ModuleConfiguration $moduleConfiguration, int $shopId)
    {
        $this->validateModuleSettings($moduleConfiguration, $shopId);

        foreach ($moduleConfiguration->getSettings() as $setting) {
            $handler = $this->getHandler($setting);
            $handler->handleOnModuleActivation($setting, $moduleConfiguration->getId(), $shopId);
        }
    }

    /**
     * @param ModuleConfiguration $moduleConfiguration
     * @param int                 $shopId
     */
    public function handleOnDeactivation(ModuleConfiguration $moduleConfiguration, int $shopId)
    {
        foreach ($moduleConfiguration->getSettings() as $setting) {
            $handler = $this->getHandler($setting);
            $handler->handleOnModuleDeactivation($setting, $moduleConfiguration->getId(), $shopId);
        }
    }

    /**
     * @param ModuleSettingHandlerInterface $moduleSettingHandler
     */
    public function addHandler(ModuleSettingHandlerInterface $moduleSettingHandler)
    {
        $this->moduleSettingHandlers[] = $moduleSettingHandler;
    }

    /**
     * @param ModuleSettingValidatorInterface $moduleSettingValidator
     */
    public function addValidator(ModuleSettingValidatorInterface $moduleSettingValidator)
    {
        $this->moduleSettingValidators[] = $moduleSettingValidator;
    }

    /**
     * @param ModuleSetting $setting
     * @return ModuleSettingHandlerInterface
     * @throws ModuleSettingHandlerNotFoundException
     */
    private function getHandler(ModuleSetting $setting): ModuleSettingHandlerInterface
    {
        foreach ($this->moduleSettingHandlers as $moduleSettingHandler) {
            /** @var ModuleSettingHandlerInterface $moduleSettingHandler */
            if ($moduleSettingHandler->canHandle($setting)) {
                return $moduleSettingHandler;
            }
        }

        throw new ModuleSettingHandlerNotFoundException(
            'Handler for the setting with name "' . $setting->getName() . '" wasn\'t found.'
        );
    }

    /**
     * @param ModuleConfiguration $moduleConfiguration
     * @param int                 $shopId
     */
    private function validateModuleSettings(ModuleConfiguration $moduleConfiguration, int $shopId)
    {
        foreach ($moduleConfiguration->getSettings() as $setting) {
            foreach ($this->moduleSettingValidators as $validator) {
                if ($validator->canValidate($setting)) {
                    $validator->validate($setting, $moduleConfiguration->getId(), $shopId);
                }
            }
        }
    }
}
