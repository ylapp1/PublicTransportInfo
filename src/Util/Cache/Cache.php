<?php
/**
 * @file
 * @version 0.1
 * @copyright 2019 Yannick Lapp
 * @author Yannick Lapp <yannick.lapp@cn-consult.eu
 * @license MIT
 */

namespace PublicTransportInfo\Util\Cache;

/**
 * Provides the possibility to cache data to files.
 * The data must be json encodable.
 *
 * The cache entry's are saved to files matching their entry name.
 */
class Cache
{
    /**
     * The base path to which all entries of this Cache are saved
     * @var string $basePath
     */
    private $basePath;


    /**
     * Cache constructor.
     *
     * @param string $_basePath The base path to which all entries of this Cache are saved
     */
    public function __construct(string $_basePath)
    {
        $this->basePath = $_basePath;
    }


    /**
     * Returns whether this Cache has a entry with a specified name.
     *
     * @param string $_entryName The entry name to search for
     *
     * @return bool True if this Cache has a entry for that name, false otherwise
     */
    public function hasEntry(string $_entryName): bool
    {
        return is_file($this->basePath . "/" . $_entryName);
    }

    /**
     * Returns a specified cache entry.
     *
     * @param string $_entryName The entry name
     *
     * @return CacheEntry The entry
     */
    public function getEntry(string $_entryName): CacheEntry
    {
        $entry = new CacheEntry($this, $_entryName);
        if ($this->hasEntry($_entryName))
        {
            $json = json_decode(file_get_contents($this->basePath . "/" . $_entryName));
            if (is_object($json))
            {
                $entry->loadFromJson($json);
            }
        }

        return $entry;
    }

    /**
     * Saves the contents of a CacheEntry to a file.
     *
     * @param CacheEntry $_entry The entry to save
     */
    public function setEntry(CacheEntry $_entry)
    {
        $cacheFilePath = $this->basePath . "/" . $_entry->getName();
        $outputDirectoryPath = dirname($cacheFilePath);
        if (!is_dir($outputDirectoryPath)) mkdir($outputDirectoryPath, 0777, true);

        file_put_contents($cacheFilePath, json_encode($_entry->toJson()));
    }

    /**
     * Creates and returns a new CacheEntry.
     *
     * @param string $_entryName The entry name
     * @param int $_validForSeconds The number of seconds for which the entry is valid
     *
     * @return CacheEntry The CacheEntry
     */
    public function createEntry(string $_entryName, int $_validForSeconds): CacheEntry
    {
        $entry = new CacheEntry($this, $_entryName);
        $entry->setValidForSeconds($_validForSeconds);

        return $entry;
    }

    /**
     * Returns an existing entry if one exists or creates and returns a new entry otherwise.
     *
     * @param string $_entryName The entry name
     * @param int $_validForSeconds The number of seconds for which the entry is valid
     *
     * @return CacheEntry The CacheEntry
     */
    public function getOrCreateEntry(string $_entryName, int $_validForSeconds): CacheEntry
    {
        if ($this->hasEntry($_entryName)) return $this->getEntry($_entryName);
        else return $this->createEntry($_entryName, $_validForSeconds);
    }
}
