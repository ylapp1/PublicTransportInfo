<?php
/**
 * @file
 * @version 0.1
 * @copyright 2019 Yannick Lapp
 * @author Yannick Lapp <yannick.lapp@cn-consult.eu
 * @license MIT
 */

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PublicTransportInfo\Util\Cache\Cache;
use PublicTransportInfo\Util\Cache\CacheEntry;
use PublicTransportInfo\Util\GermanDateTime;

/**
 * Checks that the Cache class works as expected.
 */
class CacheTest extends TestCase
{
    /**
     * The "/tmp" directory that will be used as root for the Cache base directories
     * @var vfsStreamDirectory $tmpDirectory
     */
    private $tmpDirectory;


    /**
     * Method that is called before a test is executed.
     * Sets up the "/tmp" directory.
     */
    protected function setUp()
    {
        $this->tmpDirectory = vfsStream::setup("/tmp");
    }

    /**
     * Method that is called after a test was executed.
     * Unsets the "/tmp" directory.
     */
    protected function tearDown()
    {
        unset($this->cacheDirectory);
    }


    /**
     * Checks that the Cache can detect existing cache entry files as expected.
     */
    public function testCanReturnIfEntryExists()
    {
        // Prepare the cache directory
        $cacheDirectory = vfsStream::newDirectory("public-transport-info");
        $cacheDirectoryStructure = array(
            "Rmv" => array(
                "152341" => array(
                    "last-result.json" => "{}",
                ),
                "3458u29" => array(
                    "last-result.json" => "{\"someKey\": \"someValue\"}"
                )
            ),
            "global-cache" => "{}"
        );

        vfsStream::create($cacheDirectoryStructure, $cacheDirectory);
        $this->tmpDirectory->addChild($cacheDirectory);

        $cache = new Cache($cacheDirectory->url());


        // Check that existing files are detected as cache entries
        $this->assertTrue($cache->hasEntry("Rmv/152341/last-result.json"));
        $this->assertTrue($cache->hasEntry("Rmv/3458u29/last-result.json"));
        $this->assertTrue($cache->hasEntry("global-cache"));

        // Check that the directories are not detected as cache entries
        $this->assertFalse($cache->hasEntry("Rmv/152341"));
        $this->assertFalse($cache->hasEntry("Rmv/3458u29"));
        $this->assertFalse($cache->hasEntry("Rmv"));

        // Check that files that do not exist are not detected as cache entries
        $this->assertFalse($cache->hasEntry("random"));
        $this->assertFalse($cache->hasEntry("Rmv/nothere"));
        $this->assertFalse($cache->hasEntry("Rmv/152341/last-result"));
        $this->assertFalse($cache->hasEntry("Rmv/3458u29/LAST-RESULT.json"));
    }

    /**
     * Checks that a CacheEntry can be created from a cache file as expected.
     */
    public function testCanReturnCacheEntry()
    {
        // Prepare the cache directory
        $cacheDirectory = vfsStream::newDirectory("public-transport-info");
        $cacheDirectoryStructure = array(
            "Rmv" => array(
                "blablub" => array(
                    // 2019-09-20 10:00:00
                    "last-result.json" => "{
                                              \"createTimestamp\": \"1568966400\",
                                              \"validForSeconds\": 600,
                                              \"data\": \"no complex data\",
                                              \"for\": \"nothing\"
                                              }"
                ),
                "unused" => array(
                    "last-result.json" => "{\"no\": \"content\"}"
                )
            )
        );

        vfsStream::create($cacheDirectoryStructure, $cacheDirectory);
        $this->tmpDirectory->addChild($cacheDirectory);

        $cache = new Cache($cacheDirectory->url());
        $entry = $cache->getEntry("Rmv/blablub/last-result.json");

        $this->assertInstanceOf(CacheEntry::class, $entry);
        $this->assertTrue($entry->isFor("nothing"));
        $this->assertEquals("no complex data", $entry->getData());

        $this->assertTrue($entry->isValid(new GermanDateTime("2019-09-20 10:10:00")));
        $this->assertFalse($entry->isValid(new GermanDateTime("2019-09-20 10:10:01")));

        $this->assertTrue($entry->isValid(new GermanDateTime("2019-09-19 10:10:01")), "Day before but after valid time limit reached");
        $this->assertTrue($entry->isValid(new GermanDateTime("2019-09-20 09:00:00")), "Same day and before valid time limit reached");
        $this->assertFalse($entry->isValid(new GermanDateTime("2019-09-21 09:00:00")), "Next day and before valid time limit reached");
    }

    /**
     * Checks that the expected directories and files are created when setting a Cache entry.
     */
    public function testCanSetEntry()
    {
        $cache = new Cache($this->tmpDirectory->url() . "/public-transport-info");

        // Prepare some entries
        $entryMockAJson = (object)array(
            "complex" => (object)(array("hallo" => "welt")),
            "simple" => 87232
        );
        $entryMockA = $this->getEntryMock("Rmv/fake-station/last-result.json", $entryMockAJson);

        $entryMockBJson = (object)array("val" => "23947");
        $entryMockB = $this->getEntryMock("Rmv/another-fake/last-result.json", $entryMockBJson);
        $entryMockC = $this->getEntryMock("global-cache", (object)array("1234" => 5678));


        // Check the structure before any entry was added
        $expectedFileStructure = array(
            "tmp" => array()
        );
        $actualFileStructure = vfsStream::inspect(new vfsStreamStructureVisitor(), $this->tmpDirectory)->getStructure();
        $this->assertEquals($expectedFileStructure, $actualFileStructure);

        // Add the first entry
        $cache->setEntry($entryMockA);
        $expectedFileStructure = array(
            "tmp" => array(
                "public-transport-info" => array(
                    "Rmv" => array(
                        "fake-station" => array(
                            "last-result.json" => "{\"complex\":{\"hallo\":\"welt\"},\"simple\":87232}"
                        )
                    )
                )
            )
        );
        $actualFileStructure = vfsStream::inspect(new vfsStreamStructureVisitor(), $this->tmpDirectory)->getStructure();
        $this->assertEquals($expectedFileStructure, $actualFileStructure);

        // Add the second entry
        $cache->setEntry($entryMockB);
        $expectedFileStructure = array(
            "tmp" => array(
                "public-transport-info" => array(
                    "Rmv" => array(
                        "fake-station" => array(
                            "last-result.json" => "{\"complex\":{\"hallo\":\"welt\"},\"simple\":87232}"
                        ),
                        "another-fake" => array(
                            "last-result.json" => "{\"val\":\"23947\"}"
                        )
                    )
                )
            )
        );
        $actualFileStructure = vfsStream::inspect(new vfsStreamStructureVisitor(), $this->tmpDirectory)->getStructure();
        $this->assertEquals($expectedFileStructure, $actualFileStructure);

        // Add the third entry
        $cache->setEntry($entryMockC);
        $expectedFileStructure = array(
            "tmp" => array(
                "public-transport-info" => array(
                    "Rmv" => array(
                        "fake-station" => array(
                            "last-result.json" => "{\"complex\":{\"hallo\":\"welt\"},\"simple\":87232}"
                        ),
                        "another-fake" => array(
                            "last-result.json" => "{\"val\":\"23947\"}"
                        )
                    ),
                    "global-cache" => "{\"1234\":5678}"
                )
            )
        );
        $actualFileStructure = vfsStream::inspect(new vfsStreamStructureVisitor(), $this->tmpDirectory)->getStructure();
        $this->assertEquals($expectedFileStructure, $actualFileStructure);
    }

    /**
     * Checks that new CacheEntry's can be created as expected.
     */
    public function testCanCreateEntry()
    {
        $cache = new Cache($this->tmpDirectory->url() . "/public-transport-info");
        $cacheEntry = $cache->createEntry("test-entry", 234);

        $this->assertInstanceOf(CacheEntry::class, $cacheEntry);
        $this->assertEquals("test-entry", $cacheEntry->getName());

        $cacheEntry->setCreateTimestamp(new DateTime("2019-05-10 10:00:00"))
                   ->setData((object)array("type" => 1));

        // The entry should be valid until 10:03:54
        $this->assertTrue($cacheEntry->isValid(new DateTime("2019-05-10 10:03:54")));
        $this->assertFalse($cacheEntry->isValid(new DateTime("2019-05-10 10:03:55")));
    }

    /**
     * Checks that an entry can be created if required.
     */
    public function testCanGetOrCreateEntry()
    {
        // Prepare the cache directory
        $cacheDirectory = vfsStream::newDirectory("public-transport-info");
        $cacheDirectoryStructure = array(
            "Rmv" => array(
                "station-a" => array(
                    // 2019-10-31 12:41:00
                    "last-result.json" => "{
                                              \"createTimestamp\": \"1572522060\",
                                              \"validForSeconds\": 200,
                                              \"data\": \"just some text\",
                                              \"for\": \"atest\"
                                              }"
                )
            )
        );

        vfsStream::create($cacheDirectoryStructure, $cacheDirectory);
        $this->tmpDirectory->addChild($cacheDirectory);

        $cache = new Cache($cacheDirectory->url());

        // Existing entry
        $entry = $cache->getOrCreateEntry("Rmv/station-a/last-result.json", 500);
        $this->assertEquals("just some text", $entry->getData());
        $entry->setCreateTimestamp(new GermanDateTime("12:00:00"));
        $this->assertTrue($entry->isValid(new GermanDateTime("12:03:20")));
        $this->assertFalse($entry->isValid(new GermanDateTime("12:03:21")));

        // New entry
        $entry = $cache->getOrCreateEntry("Rmv/station-b/last-result.json", 500);
        $this->assertNull($entry->getData());
        $entry->setCreateTimestamp(new GermanDateTime("12:00:00"))
              ->setFor("something")
              ->setData((object)array("value" => "key"));
        $this->assertTrue($entry->isValid(new GermanDateTime("12:08:20")));
        $this->assertFalse($entry->isValid(new GermanDateTime("12:08:21")));
    }

    /**
     * Checks that a entry can be saved and restored as expected.
     */
    public function testCanRestoreSavedEntry()
    {
        // Prepare the cache directory
        $cacheDirectory = vfsStream::newDirectory("public-transport-info");
        $this->tmpDirectory->addChild($cacheDirectory);

        $cache = new Cache($cacheDirectory->url());

        // Create and save a new entry
        $entry = $cache->createEntry("Rmv/stationinfo/last-result.json", 1000);
        $entry->setCreateTimestamp(new GermanDateTime("2019-10-05 15:01:00"))
              ->setFor("restoretest")
              ->setData((object)array("a simple" => "object"));
        $cache->setEntry($entry);

        // Now load the entry from the file
        $restoreCache = new Cache($cacheDirectory->url());
        $restoredEntry = $restoreCache->getEntry("Rmv/stationinfo/last-result.json");

        $this->assertEquals($entry->toJson(), $restoredEntry->toJson());
    }


    /**
     * Returns a CacheEntry mock that expects the methods "getName()" and "toJson()" to be called.
     *
     * @param string $_name The name to return on the expected "getName()" call
     * @param stdClass $_jsonMock The json to return on the expected "toJson()" call
     *
     * @return MockObject|CacheEntry The CacheEntry mock
     */
    private function getEntryMock(string $_name, stdClass $_jsonMock): MockObject
    {
        $entryMock = $this->getMockBuilder(CacheEntry::class)
                          ->setMethods(array("getName", "toJson"))
                          ->disableOriginalConstructor()
                          ->getMock();

        $entryMock->expects($this->once())
                  ->method("getName")
                  ->will($this->returnValue($_name));
        $entryMock->expects($this->once())
                  ->method("toJson")
                  ->will($this->returnValue($_jsonMock));

        return $entryMock;
    }
}
