<?php
/**
 * @file
 * @version 0.1
 * @copyright 2019 Yannick Lapp
 * @author Yannick Lapp <yannick.lapp@cn-consult.eu
 * @license MIT
 */

namespace PublicTransportInfo\Util;

/**
 * Provides methods to fetch config values from an associative array of configuration values.
 */
class ConfigParser
{
    /**
     * The associative array of configuration values
     *
     * @mixed[] $config
     */
    private $config;


    /**
     * ConfigParser constructor.
     *
     * @param mixed[] $_config The configuration array
     */
    public function __construct(array $_config)
    {
        $this->config = $_config;
    }


    /**
     * Returns one of the configuration values by configuration name.
     * Returns the passed default value if the configuration value is not set.
     *
     * @param string $_key The name of the config value
     * @param mixed $_defaultValue The default value to return if the config value is not set
     *
     * @return mixed The config value
     */
    public function get(string $_key, $_defaultValue = null)
    {
        if (isset($this->config[$_key])) return $this->config[$_key];
        else return $_defaultValue;
    }
}
