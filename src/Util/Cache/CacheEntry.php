<?php
/**
 * @file
 * @version 0.1
 * @copyright 2019 Yannick Lapp
 * @author Yannick Lapp <yannick.lapp@cn-consult.eu
 * @license MIT
 */

namespace PublicTransportInfo\Util\Cache;

use DateTime;
use PublicTransportInfo\Util\GermanDateTime;
use stdClass;

/**
 * Represents a entry of the Cache.
 */
class CacheEntry
{
    /**
     * The parent Cache to which this Cache entry belongs
     * @var Cache $parentCache
     */
    private $parentCache;

    /**
     * The name of this entry
     * @var string $name
     */
    private $name;

    /**
     * The number of seconds for which this entry is valid relative to the create timestamp
     * @var int $validForSeconds
     */
    private $validForSeconds;

    /**
     * The timestamp when the data of this entry was created
     * @var DateTime $createTimestamp
     */
    private $createTimestamp;

    /**
     * The data that is stored by this CacheEntry
     * This data must be a json object
     *
     * @var stdClass $data
     */
    private $data;

    /**
     * The subject of this CacheEntry
     * This can be used to identify if this CacheEntry stores the expected type of data at the moment
     *
     * @var string $for
     */
    private $for;


    /**
     * CacheEntry constructor.
     *
     * @param Cache $_parentCache The parent Cache
     * @param string $_name The entry name
     */
    public function __construct(Cache $_parentCache, string $_name)
    {
        $this->parentCache = $_parentCache;
        $this->name = $_name;
    }


    /**
     * Returns this entry's name.
     * @return string The name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets the number of seconds for which this entry is valid.
     *
     * @param int $_validForSeconds The number of seconds for which this entry is valid
     *
     * @return CacheEntry The CacheEntry instance to be able to chain other method calls
     */
    public function setValidForSeconds(int $_validForSeconds): CacheEntry
    {
        $this->validForSeconds = $_validForSeconds;
        return $this;
    }

    /**
     * Sets this CacheEntry's data create timestamp.
     *
     * @param DateTime $_createTimestamp The data create timestamp
     *
     * @return CacheEntry The CacheEntry instance to be able to chain other method calls
     */
    public function setCreateTimestamp(DateTime $_createTimestamp): CacheEntry
    {
        $this->createTimestamp = $_createTimestamp;
        return $this;
    }

    /**
     * Returns this CacheEntry's current data.
     * @return stdClass|null The current data
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Sets this CacheEntry's data.
     * @param stdClass|array $_data The data
     *
     * @return CacheEntry The CacheEntry instance to be able to chain other method calls
     */
    public function setData($_data): CacheEntry
    {
        $this->data = $_data;
        return $this;
    }

    /**
     * Sets this CacheEntry's subject.
     *
     * @param string $_for The subject
     *
     * @return CacheEntry The CacheEntry instance to be able to chain other method calls
     */
    public function setFor(string $_for): CacheEntry
    {
        $this->for = $_for;
        return $this;
    }


    /**
     * Returns whether this CacheEntry stores data for a specified type.
     *
     * @param string $_for The type name
     *
     * @return bool True if this CacheEntry stores data for the specified type, false otherwise
     */
    public function isFor(string $_for): bool
    {
        return ($this->for == $_for);
    }

    /**
     * Returns whether the cached data is valid.
     *
     * @param DateTime $_referenceTime The time to compare the caches validity duration to
     *
     * @return bool True if the cached data is valid, false otherwise
     */
    public function isValid(DateTime $_referenceTime): bool
    {
        if (!isset($this->data) || !isset($this->createTimestamp) || !isset($this->validForSeconds)) return false;
        else
        {
            $maximumTimestamp = $this->createTimestamp->getTimestamp() + $this->validForSeconds;
            return ($_referenceTime->getTimestamp() <= $maximumTimestamp);
        }
    }


    /**
     * Converts this CacheEntry's contents to a json object.
     *
     * @return stdClass The json object
     */
    public function toJson(): stdClass
    {
        return (object)array(
            "createTimestamp" => $this->createTimestamp->format("H:i:s"),
            "validForSeconds" => $this->validForSeconds,
            "data" => $this->data,
            "for" => $this->for
        );
    }

    /**
     * Loads this CacheEntry's contents from a json object.
     *
     * @param stdClass $_json The json object
     */
    public function loadFromJson(stdClass $_json)
    {
        $this->createTimestamp = GermanDateTime::createFromFormat("H:i:s", $_json->createTimestamp);
        $this->validForSeconds = $_json->validForSeconds;
        $this->data = $_json->data;
        $this->for = $_json->for;
    }

    /**
     * Saves this CacheEntry to the parent Cache.
     */
    public function save()
    {
        $this->parentCache->setEntry($this);
    }
}
