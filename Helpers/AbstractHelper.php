<?php
/**
 * Created by PhpStorm.
 * User: ajax
 * Date: 7/28/14
 * Time: 1:17 PM
 */

namespace Helpers;

use Helpers\Exceptions\HelpersException;

abstract class AbstractHelper
{
    const OPERATION_NOT_RUN = 'There Are No Operations Planned';

    protected $configs = [];
    protected $configDirectories = [];
    protected $reflection;
    protected $defaultConfigs = [];
    protected $operation;
    protected static $staticDefaultConfigs = [];

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config($config);
        $this->operation = self::OPERATION_NOT_RUN;
    }

    /**
     * @param array $config
     */
    protected function config(array $config = [])
    {
        $namespaces = explode('\\', $this->getReflection()->getNamespaceName());
        $this->makeConfigDirectoriesByNamespaces($namespaces);
        $this->mergeConfigs($config);
        $this->makeDefaultConfigs();
    }

    /**
     * @param $optionName
     * @param null $default
     * @return null
     */
    protected function getOption($optionName, $default = null)
    {
        $option = $default;
        if (isset($this->getConfigs()[$optionName])) {
            $option = $this->getConfigs()[$optionName];
        }
        return $option;
    }

    /**
     * @return array
     */
    protected function getConfigs()
    {
        return $this->configs;
    }

    protected function getDefaultOptionValue($optionName)
    {
        $optionValue = null;
        if (isset($this->defaultConfigs[$optionName])) {
            $optionValue = $this->defaultConfigs[$optionName];
        }
        return $optionValue;
    }

    protected function getOptionWithDefault($optionName)
    {
        $option = $this->getOption(
            $optionName,
            $this->getDefaultOptionValue($optionName)
        );
        if(!$option) {
            throw HelpersException::optionNotFound(
                $optionName,
                $this->getReflection()->getName());
        }
        return $option;
    }

    /**
     * @return \ReflectionClass
     */
    protected final function getReflection()
    {
        if (!$this->reflection instanceof \ReflectionClass) {
            $this->reflection = new \ReflectionClass($this);
        }
        return $this->reflection;
    }

    /**
     * @param array $namespaces
     */
    private final function makeConfigDirectoriesByNamespaces(array $namespaces)
    {
        foreach ($namespaces as $dirName) {
            $this->configDirectories[] = $this->createConfigDirPath($dirName);
        }
    }

    /**
     * @param $dirName
     * @return string
     */
    private final function createConfigDirPath($dirName)
    {
        return $this->getBaseConfigDirPath()
        . DIRECTORY_SEPARATOR
        . $dirName;
    }

    /**
     * @param array $config
     */
    private final function mergeConfigs(array $config)
    {
        foreach ($this->configDirectories as $configDir) {

            $filename = $this->getBaseConfigsPath($configDir);
            if (file_exists($filename)) {
                $dirConfigs = include_once $this->getBaseConfigsPath($configDir);
                $class = $this->getReflection()->getShortName();
                $parentConfig = $this->getParentConfigs($dirConfigs, $this->getParentClass());
                if (isset($dirConfigs[$class])) {
                    $this->configs = array_merge(
                        $parentConfig,
                        (array)$dirConfigs[$class],
                        $this->configs);
                }
            }
        }
        $this->configs = array_merge($config, $this->configs);
    }

    /**
     * @param $directory
     * @return string
     */
    private final function getBaseConfigsPath($directory)
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '..'
            . $directory . DIRECTORY_SEPARATOR . 'configs.php';
    }

    /**
     * @return int
     */
    private function getBaseIndex()
    {
        return sizeof($this->configDirectories) - 1;
    }

    /**
     * @return string
     */
    private function getBaseConfigDirPath()
    {
        return isset($this->configDirectories[$this->getBaseIndex()])
            ? $this->configDirectories[$this->getBaseIndex()]
            : '';
    }

    private final function getParentConfigs(array $configs, \ReflectionClass $reflectionClass = null)
    {
        $parentConfigs = [];

        if($reflectionClass) {
            if (isset($configs[$reflectionClass->getShortName()])) {
                $parentConfigs = array_merge(
                    (array) $configs[$reflectionClass->getShortName()],
                    $this->getParentConfigs($configs, $reflectionClass->getParentClass())
                );
            }
        }
        return $parentConfigs;
    }

    /**
     * @return \ReflectionClass
     */
    private function getParentClass()
    {
        return $this->getReflection()->getParentClass();
    }

    private final function makeDefaultConfigs()
    {
        $this->defaultConfigs = array_merge(
            $this::$staticDefaultConfigs,
            $this->getParentDefaultConfigs($this->getReflection())
        );
    }

    private final function getParentDefaultConfigs(\ReflectionClass $reflectionClass)
    {
        $parentDefaultConfigs = [];
        if ($reflectionClass->getParentClass()) {
            $parentDefaultConfigs = array_merge(
                $parentDefaultConfigs,
                $this->getParentDefaultConfigs($reflectionClass->getParentClass())
            );
        }
        return $parentDefaultConfigs;
    }
}