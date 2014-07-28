<?php

namespace Helpers\Refactor\Arrays;

use Helpers\AbstractHelper;

class AbstractArrayHelper extends AbstractHelper
{
    const CONFIG_DIR_FOR_GENERATE = 'dirForGenerate';

    protected $array = [];
    protected $dirForGenerate;

    protected static $defaultConfigs = [
        self::CONFIG_DIR_FOR_GENERATE => 'data'
    ];

    /**
     * @param array $array
     * @param array $config
     */
    public function __construct(array $array = [], array $config = [])
    {
        $this->array = $array;
        parent::__construct($config);
    }

    /**
     * @param $key
     * @param array $array
     * @param null $default
     * @return null
     */
    public static function getValue($key, array $array, $default = null)
    {
        $result = $default;
        if(isset($array[$key])) {
            $result = $array[$key];
        }
        return $result;
    }

    /**
     * @param $search
     * @param array $array
     * @param null $default
     * @return null
     */
    public static function getValueRecursive($search, array $array, $default = null)
    {
        $result = $default;
        foreach ($array as $key => $val) {
            if ($key == $search) {
                return $val;
            }
            if (is_array($val)) {
                return self::getValueRecursive($search, $val, $default);
            }
        }
        return $result;
    }

    /**
     * @return $this
     */
    protected function initDirForGenerate()
    {
        $this->dirForGenerate = $this->getOptionWithDefault(
            self::CONFIG_DIR_FOR_GENERATE,
            $this->getDefaultOptionValue(self::CONFIG_DIR_FOR_GENERATE)
        );
        return $this;
    }

    /**
     * @return mixed
     */
    protected function getDirForGenerate()
    {
        return $this->dirForGenerate;
    }

} 