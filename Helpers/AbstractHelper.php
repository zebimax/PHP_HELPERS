<?php
/**
 * Created by PhpStorm.
 * User: ajax
 * Date: 7/28/14
 * Time: 1:17 PM
 */

namespace Helpers;

use Helpers\Exceptions\HelpersException;
use Helpers\Refactor\Arrays\AbstractArrayHelper;

abstract class AbstractHelper
{
    const OPERATION_NOT_RUN = 'There Are No Operations Planned';
    const SUCCESS_MESSAGE = 'success';
    const MESSAGE_SUCCESS_TPL = 'Operation %s was successfully done!';
    const DEFAULT_MESSAGE = 'Unknown operation';
    protected $configs = [];
    protected $configDirectories = [];
    protected $reflection;
    protected $defaultConfigs = [];
    protected $operation;
    protected static $staticDefaultConfigs = [];
    protected $definedMessage = [];

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config($config);
        $this->operation = self::OPERATION_NOT_RUN;
        return $this;
    }

    protected function writeMessage($messageCode)
    {
        switch ($messageCode) {
            case self::SUCCESS_MESSAGE:
                $messageToWrite = $this->getSuccessMessage();
                break;
            case $this->isMessageDefined($messageCode);
                $messageToWrite = $this->getDefinedMessage($messageCode);
                break;
            default :
                $messageToWrite = self::DEFAULT_MESSAGE;
                break;
        }
        echo $messageToWrite . PHP_EOL;
    }

    protected function getSuccessMessage()
    {
        return sprintf(self::MESSAGE_SUCCESS_TPL, $this->getOperation());
    }

    protected function isMessageDefined($messageCode)
    {
        return isset($this->definedMessage[$messageCode]);
    }

    protected function getDefinedMessage($messageCode)
    {
        if ($this->isMethodForDefinedMessageExists($messageCode)) {
            return $this->$this->getDefinedMessageMethod($messageCode);
        }
        return self::DEFAULT_MESSAGE;
    }

    protected function getDefinedMessageMethod($messageCode)
    {
        return $this->definedMessage[$messageCode];
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

    protected function getOperation()
    {
        return $this->operation;
    }

    /**
     * @param string $operation
     */
    public function setOperation($operation)
    {
        $this->operation = $operation;
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
                $dirConfigsWithParent = array_merge(
                    $dirConfigs,
                    $this->getParentConfigs($dirConfigs, $this->getParentClass())
                );
                if (isset($dirConfigsWithParent[$class])) {
                    $this->configs = array_merge(
                        (array)$dirConfigsWithParent[$class],
                        $this->configs
                    );
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
            $parentClassName = $reflectionClass->getParentClass()->getName();
            $parentStaticDefaultConfigs = $this->getParentDefaultStaticProperties($parentClassName);
            $parentDefaultConfigs = array_merge(
                $parentDefaultConfigs,
                $this->getParentDefaultConfigs($reflectionClass->getParentClass()),
                $parentStaticDefaultConfigs
            );
        }
        return $parentDefaultConfigs;
    }

    /**
     * @param $messageCode
     * @return bool
     */
    protected function isMethodForDefinedMessageExists($messageCode)
    {
        return method_exists($this, AbstractArrayHelper::getValue($messageCode, $this->definedMessage));
    }

    /**
     * @param $parentClassName
     * @return array
     */
    private function getParentDefaultStaticProperties($parentClassName)
    {
        return property_exists($parentClassName, 'staticDefaultConfigs')
            ? $parentClassName::$staticDefaultConfigs : [];
    }

}