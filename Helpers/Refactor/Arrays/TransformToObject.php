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
    const CONFIG_STRING_INDEXES = true;

    const CONFIG_FILE_FOR_GENERATE = 'fileForGenerate';
    const CONFIG_CLASS_NAME_FOR_GENERATE = 'classNameForGenerate';

    const OPERATION_GENERATE_CLASS = 'Generate Object Data From Array';


    protected static $defaultConfigs = [
        self::CONFIG_STRING_INDEXES          => true,
        self::CONFIG_FILE_FOR_GENERATE       => 'ArrayObject.php',
        self::CONFIG_CLASS_NAME_FOR_GENERATE => 'GeneratedClass'
    ];

    private $fieldNames = [];
    private $fileForGenerate;
    private $classNameForGenerate;


    public function __construct(array $array = [], array $config = [], array $fieldNames = [])
    {
        $this->fieldNames = $fieldNames;
        parent::__construct($array, $config);
    }

    public function generateObjectClass()
    {
        $this->operation = self::OPERATION_GENERATE_CLASS;
        $this->tryConfigureFor($this->operation);
        $this->validateArray();
        $this->doGenerateObjectClass();
    }

    private function tryConfigureFor($operation)
    {
        switch ($operation) {
            case self::OPERATION_GENERATE_CLASS:
                $this->initDirForGenerate();
                $this->initFileForGenerate();
                $this->initClassNameForGenerate();
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
        foreach ($array as $index => $value)
        {
            if ($this->isValidString($index)) {
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
            && $match;
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
        return !$this->isValidStringIndexes($this->array) && $this->getOptionWithDefault(self::CONFIG_STRING_INDEXES);
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
            $f = $this->openGenerateFileDescriptor();
            $classCode = PHPCodeGenerator::openTag()
                . PHPCodeGenerator::classDefinition($this->getClassNameForGenerate())
                . PHPCodeGenerator::openBrace();
        }
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
}