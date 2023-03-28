<?php

namespace Drupal\rocketship_core;

use Drupal\Core\Config\ConfigInstallerInterface;
use Drupal\Core\Config\PreExistingConfigException;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Extension\ExtensionList;

/**
 * Decorator for ConfigInstaller service.
 *
 * Decorates the ConfigInstaller with checkConfigurationToInstall() modified
 * to allow pre-existing configuration to be installed for Rocketship modules.
 */
class RocketshipConfigInstaller implements ConfigInstallerInterface {

  /**
   * The configuration installer.
   *
   * @var \Drupal\Core\Config\ConfigInstallerInterface
   */
  protected $configInstaller;

  /**
   * The module extension list.
   *
   * @var \Drupal\Core\Extension\ExtensionList
   */
  protected $extensionList;

  /**
   * Constructs the configuration installer.
   *
   * @param \Drupal\Core\Config\ConfigInstallerInterface $config_installer
   *   The configuration installer.
   * @param \Drupal\Core\Extension\ExtensionList $extensionList
   *   The module extension list.
   */
  public function __construct(ConfigInstallerInterface $config_installer, ExtensionList $extensionList) {
    $this->configInstaller = $config_installer;
    $this->extensionList = $extensionList;
  }

  /**
   * {@inheritdoc}
   */
  public function checkConfigurationToInstall($type, $name) {
    try {
      $this->configInstaller->checkConfigurationToInstall($type, $name);
    }
    catch (PreExistingConfigException $e) {
      // Ignore pre-existing config exceptions for Rocketship packages.
      if ($type === 'module') {
        $all_info = $this->extensionList->getAllAvailableInfo();
        $module = $all_info[$name];
        if (isset($module['package']) && $module['package'] == 'Rocketship') {
          return;
        }
      }
      // Rethrow for others.
      throw PreExistingConfigException::create($e->getExtension(), $e->getConfigObjects());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function installDefaultConfig($type, $name) {
    return $this->configInstaller->installDefaultConfig($type, $name);
  }

  /**
   * {@inheritdoc}
   */
  public function getSourceStorage() {
    return $this->configInstaller->getSourceStorage();
  }

  /**
   * {@inheritdoc}
   */
  public function installOptionalConfig(StorageInterface $storage = NULL, $dependency = []) {
    return $this->configInstaller->installOptionalConfig($storage, $dependency);
  }

  /**
   * {@inheritdoc}
   */
  public function installCollectionDefaultConfig($collection) {
    return $this->configInstaller->installCollectionDefaultConfig($collection);
  }

  /**
   * {@inheritdoc}
   */
  public function setSourceStorage(StorageInterface $storage) {
    return $this->configInstaller->setSourceStorage($storage);
  }

  /**
   * {@inheritdoc}
   */
  public function setSyncing($status) {
    return $this->configInstaller->setSyncing($status);
  }

  /**
   * {@inheritdoc}
   */
  public function isSyncing() {
    return $this->configInstaller->isSyncing();
  }

  /**
   * Implements magic __call method.
   *
   * Seeing as core didn't think it was necessary to add all their public
   * methods to the interface we can't be sure what else they'll add without
   * adding it to their interface.
   *
   * @param string $name
   *   The method to call.
   * @param mixed $arguments
   *   The arguments.
   *
   * @return mixed
   *   Whatever the called method returns.
   */
  public function __call($name, $arguments) {
    return call_user_func_array(
      [$this->configInstaller, $name],
      $arguments
    );
  }

}
