<?php
namespace Helpers\Refactor\Arrays;

use Helpers\Exceptions\Arrays\ArraysException;
use Helpers\Exceptions\Arrays\TransformToObjectException;
use Helpers\Exceptions\HelpersException;
use Internal\PHPCodeGenerator;

class TransformToObject extends AbstractArrayHelper
{
    const CONFIG_NOTATION = 'notation';
    const CONFIG_FLUENT = 'fluent';
    const CONFIG_STRING_INDEXES = 'indexesAsProperties';

    const CONFIG_FILE_FOR_GENERATE       = 'fileForGenerate';
    const CONFIG_CLASS_NAME_FOR_GENERATE = 'classNameForGenerate';
    const CONFIG_VALUES_AS_DEFAULT       = 'valuesAsDefault';
    const CONFIG_PROPERTY_VISIBILITY     = 'propertyVisibility';
    const CONFIG_ARRAY_VALUES_AS_DEFAULT = 'arrayValuesAsDefault';
    const CONFIG_DEFAULT_PROPERTY_VALUE  = 'defaultPropertyValue';


    const OPERATION_GENERATE_CLASS       = 'Generate Object Data From Array';

    const MESSAGE_CODE_SUCCESS_GENERATE = 'successGenerate';

    const SUCCESS_GENERATE_MESSAGE_TPL = 'Class %s was successfully generated in file $s';

    protected static $staticDefaultConfigs = [
        self::CONFIG_STRING_INDEXES          => true,
        self::CONFIG_FILE_FOR_GENERATE       => 'ArrayObject.php',
        self::CONFIG_CLASS_NAME_FOR_GENERATE => 'GeneratedClass',
        self::CONFIG_VALUES_AS_DEFAULT       => false,
        self::CONFIG_PROPERTY_VISIBILITY     => PHPCodeGenerator::VISIBILITY_PRIVATE,
        self::CONFIG_ARRAY_VALUES_AS_DEFAULT => false
    ];

    protected $definedMessage = [
        self::MESSAGE_CODE_SUCCESS_GENERATE => 'getSuccessGenerateMessage'
    ];

    private $fieldNames = [];
    private $fileForGenerate;
    private $classNameForGenerate;
    private $classCode;

    public function __construct(array $array = [], array $config = [], array $fieldNames = [])
    {
        $this->fieldNames = $fieldNames;
        parent::__construct($array, $config);
    }

    public function generateObjectClass()
    {
        $this->setOperation(self::OPERATION_GENERATE_CLASS);
        $this->configureFor($this->getOperation());
        $this->validateArray();
        $this->doGenerateObjectClass();
        $f = $this->openGenerateFileDescriptor();
        $this->writeCode($f);
        $this->writeMessage(self::SUCCESS_MESSAGE);
    }

    private function writeCode($f)
    {
        if (@get_resource_type($f) === 'stream') {
            fputs($f, $this->getClassCode());
        }
    }

    private function configureFor($operation)
    {
        switch ($operation) {
            case self::OPERATION_GENERATE_CLASS:
                $this->initDirForGenerate()
                    ->initFileForGenerate()
                    ->initClassNameForGenerate();
                break;
            default:
                break;

        }
    }

    protected function validateArray()
    {
        if ($this->isCanUseStringIndexes()) {
             throw ArraysException::arrayNotValid(
                 $this->array,
                 $this->getReflection()->getName(),
                 $this->operation
             );
        } elseif ($this->isCanUseFieldNames()) {
            throw TransformToObjectException::fieldNamesArrayNotValid($this->fieldNames);
        };
    }

    private function isValidStringIndexes(array $array)
    {
        $result = true;
        foreach ($array as $index => $value) {
            if (!$this->isValidString($index)) {
                return false;
            }
        }
        return $result;
    }

    /**
     * @param $index
     * @return bool
     */
    private function isValidString($index)
    {
        $regex = '|\W|';
        $replaced = preg_replace($regex, '', $index);
        $match = preg_match('|[a-z]i|', $replaced, $matches, PREG_OFFSET_CAPTURE);
        return is_string($replaced)
            && (int)$match;
    }

    /**
     * @return bool
     */
    protected function isValidFieldNames()
    {
        return (sizeof($this->fieldNames) !== sizeof($this->array) || !$this->isValidStringIndexes($this->fieldNames));
    }

    /**
     * @return bool
     */
    protected function isCanUseFieldNames()
    {
        return !$this->getOptionWithDefault(self::CONFIG_STRING_INDEXES) &&
        $this->isValidFieldNames();
    }

    /**
     * @return bool
     */
    protected function isCanUseStringIndexes()
    {
        return $this->getOptionWithDefault(self::CONFIG_STRING_INDEXES) && $this->isValidStringIndexes($this->array);
    }

    /**
     * @return $this
     */
    private function initFileForGenerate()
    {
        $this->fileForGenerate = $this->getOptionWithDefault(
            self::CONFIG_FILE_FOR_GENERATE,
            $this->getDefaultOptionValue(self::CONFIG_FILE_FOR_GENERATE)
        );
        return $this;
    }

    private function initClassNameForGenerate()
    {
        $this->classNameForGenerate = $this->getOptionWithDefault(
            self::CONFIG_CLASS_NAME_FOR_GENERATE,
            $this->getDefaultOptionValue(self::CONFIG_CLASS_NAME_FOR_GENERATE)
        );
        return $this;
    }

    private function doGenerateObjectClass()
    {
        if ($this->isCanGenerateObject()) {
            $classCode = PHPCodeGenerator::openTag()
                . PHPCodeGenerator::classDefinition($this->getClassNameForGenerate())
                . PHPCodeGenerator::openBrace();
            if($this->getDefaultOptionValue(self::CONFIG_STRING_INDEXES)) {
                $classCode .= $this->generatePropertiesFromArray();
            } else {
                $classCode .= $this->generatePropertiesFromFieldsArray();
            }
            $classCode .= PHPCodeGenerator::closeBrace();
            $this->setClassCode($classCode) ;
        }
    }

    private function generatePropertiesFromArray()
    {
        return $this->generateProperties($this->array);
    }

    private function getAvailableOptionValue($value, $option)
    {
        $result = $this->getIndividualOption($value, $option);
        if (!$result) {
            $result = $this->getDefaultOptionValue($option);
        }
        return $result;
    }

    private function getIndividualOption($value, $option)
    {
        return AbstractArrayHelper::
        getValue($option, (array)$value);
    }

    private function generatePropertiesFromFieldsArray()
    {
        $array = $this->fieldNames;
        if ($this->getDefaultOptionValue(self::CONFIG_ARRAY_VALUES_AS_DEFAULT)) {
            $array = $this->makeValuesFromArray();
        }
        return $this->generateProperties($array);
    }

    private function makeValuesFromArray()
    {
        $result = [];
        for ($i = 0; $i < sizeof($this->fieldNames); $i++) {
            $result[$this->fieldNames[$i]] = [
                self::CONFIG_ARRAY_VALUES_AS_DEFAULT => true,
                self::CONFIG_DEFAULT_PROPERTY_VALUE  =>  AbstractArrayHelper::getValue($i, $this->array)
            ];
        }
        return $result;
    }

    private function generateProperties(array $array)
    {
        $propertiesCode = '';
        foreach ($array as $key => $value) {
            $initValue = $this->getAvailableOptionValue($value, self::CONFIG_VALUES_AS_DEFAULT)
                ? null: $this->getDefaultPropertyValue($value);
            $propertyVisibility = $this->getAvailableOptionValue($value, self::CONFIG_PROPERTY_VISIBILITY);
            $propertiesCode .= PHPCodeGenerator::property($this->normalise($key), $propertyVisibility, $initValue);
        }
        return $propertiesCode;
    }

    /**
     * @return mixed
     */
    private function getFileForGenerate()
    {
        return $this->fileForGenerate;

    }

    /**
     * @return resource
     * @throws \Helpers\Exceptions\HelpersException
     */
    private function openGenerateFileDescriptor()
    {
        $this->makeDirForGenerate();
        $this->dirForGenerateMustExists();

        $file = $this->getDirForGenerate() . DIRECTORY_SEPARATOR . $this->getFileForGenerate();
        $f =  fopen($file, 'w');
        if (!$f) {
            throw HelpersException::failOpenFile($file, $this->operation);
        }
        return $f;
    }

    /**
     * @return mixed
     */
    public function getClassNameForGenerate()
    {
        return $this->classNameForGenerate;
    }

    /**
     * @return bool
     */
    private function isCanGenerateObject()
    {
        return $this->getDirForGenerate()
            && $this->getFileForGenerate()
            && $this->getClassNameForGenerate();
    }

    /**
     * @param $value
     * @return null
     */
    private function getDefaultPropertyValue($value)
    {
        return is_array($value)
            ? AbstractArrayHelper::getValue(self::CONFIG_DEFAULT_PROPERTY_VALUE, $value)
            : $value;
    }

    /**
     * @return mixed
     */
    public function getClassCode()
    {
        return $this->classCode;
    }

    /**
     * @param mixed $classCode
     */
    public function setClassCode($classCode)
    {
        $this->classCode = $classCode;
    }

    protected function getSuccessGenerateMessage()
    {
        return sprintf(
            self::SUCCESS_GENERATE_MESSAGE_TPL,
            $this->getClassNameForGenerate(),
            $this->getFileForGenerate()
        );
    }

    protected function normalise($value)
    {
        $normalizeValue = preg_replace('|^/W|', '', $value);
        preg_match('|[a-z]|', $value, $matches, PREG_OFFSET_CAPTURE);
        foreach ($matches as $key => $match) {
            $normalizeValue[$match[1]] = ucfirst($match[0]);
        }
        return $normalizeValue;
    }
}