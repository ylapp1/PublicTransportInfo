<?php
/**
 * @file
 * @version 0.1
 * @copyright 2019 Yannick Lapp
 * @author Yannick Lapp <yannick.lapp@cn-consult.eu
 * @license MIT
 */

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PublicTransportInfo\Util\Cache\Cache;
use PublicTransportInfo\Util\Cache\CacheEntry;
use PublicTransportInfo\Util\GermanDateTime;

/**
 * Checks if the CacheEntry class works as expected.
 */
class CacheEntryTest extends TestCase
{
    /**
     * The Cache mock that may be passed to CacheEntry test instances
     * @var MockObject|Cache $cacheMock
     */
    private $cacheMock;


    /**
     * Method that is called before a test is executed.
     * Initializes the Cache mock.
     */
    protected function setUp()
    {
        $this->cacheMock = $this->getMockBuilder(Cache::class)
                                ->setMethods(array("setEntry"))
                                ->disableOriginalConstructor()
                                ->getMock();
    }

    /**
     * Method that is called after a test was executed.
     * Unsets the Cache mock.
     */
    protected function tearDown()
    {
        unset($this->cacheMock);
    }


    /**
     * Checks if the CacheEntry's subject can be configured as expected.
     */
    public function testCanConfigureSubject()
    {
        $cacheEntry = new CacheEntry($this->cacheMock, "test");
        $this->assertEquals("test", $cacheEntry->getName());

        $cacheEntry->setFor("numbers");
        $this->assertFalse($cacheEntry->isFor("characters"));
        $this->assertTrue($cacheEntry->isFor("numbers"));

        $cacheEntry->setFor("characters");
        $this->assertTrue($cacheEntry->isFor("characters"));
        $this->assertFalse($cacheEntry->isFor("numbers"));
    }

    /**
     * Checks if the CacheEntry can tell if its content is valid as expected.
     */
    public function testCanReturnIfContentIsValid()
    {
        $referenceTime = new DateTime("2019-03-01 15:00:00");

        // No data, no create timestamp and no valid for seconds set
        $cacheEntry = new CacheEntry($this->cacheMock, "new-entry");
        $this->assertFalse($cacheEntry->isValid($referenceTime));

        // No data and no create timestamp set
        $cacheEntry = new CacheEntry($this->cacheMock, "new-entry");
        $cacheEntry->setValidForSeconds(100);
        $this->assertFalse($cacheEntry->isValid($referenceTime));

        // No data and no valid for seconds set
        $cacheEntry = new CacheEntry($this->cacheMock, "new-entry");
        $cacheEntry->setCreateTimestamp(new DateTime("1999-12-31 23:59:59"));
        $this->assertFalse($cacheEntry->isValid($referenceTime));

        // No create timestamp and no valid for seconds set
        $cacheEntry = new CacheEntry($this->cacheMock, "new-entry");
        $cacheEntry->setData((object)array("key" => "val"));
        $this->assertFalse($cacheEntry->isValid($referenceTime));

        // No data set
        $cacheEntry = new CacheEntry($this->cacheMock, "new-entry");
        $cacheEntry->setValidForSeconds(200)
                   ->setCreateTimestamp(new DateTime("2000-01-01 03:00:00"));
        $this->assertFalse($cacheEntry->isValid($referenceTime));

        // No create timestamp set
        $cacheEntry = new CacheEntry($this->cacheMock, "new-entry");
        $cacheEntry->setValidForSeconds(400)
                   ->setData((object)array("number" => 3));
        $this->assertFalse($cacheEntry->isValid($referenceTime));

        // No valid for seconds set
        $cacheEntry = new CacheEntry($this->cacheMock, "new-entry");
        $cacheEntry->setCreateTimestamp(new DateTime("2004-05-02 15:00:00"))
                   ->setData((object)array("gg" => "end", "secondValue" => 2));
        $this->assertFalse($cacheEntry->isValid($referenceTime));


        // Everything set and cache is not expired yet
        $cacheEntry = new CacheEntry($this->cacheMock, "new-entry");
        $cacheEntry->setCreateTimestamp(new DateTime("2019-03-01 14:58:00"))
                   ->setValidForSeconds(300) // 5 minutes
                   ->setData((object)array("no number" => true));

        $this->assertTrue($cacheEntry->isValid($referenceTime));
    }

    /**
     * Checks that the Cache can detect if its content is expired.
     */
    public function testCanDetectIfContentIsExpired()
    {
        $cacheEntry = new CacheEntry($this->cacheMock, "new-entry");
        $cacheEntry->setCreateTimestamp(new DateTime("2013-06-07 13:12:00"))
                   ->setValidForSeconds(360) // 6 minutes
                   ->setData((object)array("my val" => false, "your val" => 54));

        $this->assertTrue($cacheEntry->isValid(new DateTime("2013-06-07 10:07:00")), "Time before create timestamp");
        $this->assertTrue($cacheEntry->isValid(new DateTime("2013-06-07 13:11:59")), "Time just before create timestamp");
        $this->assertTrue($cacheEntry->isValid(new DateTime("2013-06-07 13:12:00")), "Time exactly at create timestamp");
        $this->assertTrue($cacheEntry->isValid(new DateTime("2013-06-07 13:12:01")), "Time just after create timestamp");
        $this->assertTrue($cacheEntry->isValid(new DateTime("2013-06-07 13:13:40")), "Time between create timestamp and expire timestamp");
        $this->assertTrue($cacheEntry->isValid(new DateTime("2013-06-07 13:17:59")), "Time just before expire timestamp");
        $this->assertTrue($cacheEntry->isValid(new DateTime("2013-06-07 13:18:00")), "Time exactly on expire timestamp");
        $this->assertFalse($cacheEntry->isValid(new DateTime("2013-06-07 13:18:01")), "Time just after expire timestamp");
        $this->assertFalse($cacheEntry->isValid(new DateTime("2013-06-07 16:53:00")), "Time after expire timestamp");
        $this->assertFalse($cacheEntry->isValid(new DateTime("2013-06-08 10:00:00")), "Time on next day but before expire time");
    }

    /**
     * Checks if a CacheEntry can be converted to json and be restored from json.
     */
    public function testCanBeConvertedToJsonAndBack()
    {
        $cacheEntry = new CacheEntry($this->cacheMock, "new-entry");
        $cacheEntry->setCreateTimestamp(new GermanDateTime("today 13:12:00"))
                   ->setValidForSeconds(330) // 5:30 minutes
                   ->setData((object)array("secret" => "gsf", "notsecret" => 29843))
                   ->setFor("stuff");

        $json = $cacheEntry->toJson();
        $this->assertTrue(is_integer($json->createTimestamp));
        $this->assertEquals(330, $json->validForSeconds);
        $this->assertEquals((object)array(
            "secret" => "gsf",
            "notsecret" => 29843
        ), $json->data);
        $this->assertEquals("stuff", $json->for);


        $restoredCacheEntry = new CacheEntry($this->cacheMock, "new-entry");
        $restoredCacheEntry->loadFromJson($json);

        $this->assertEquals($cacheEntry, $restoredCacheEntry);

        $this->assertEquals((object)array(
            "secret" => "gsf",
            "notsecret" => 29843
        ), $restoredCacheEntry->getData());
    }

    /**
     * Checks if a CacheEntry can be saved.
     */
    public function testCanBeSaved()
    {
        $cacheEntry = new CacheEntry($this->cacheMock, "saveme");

        $this->cacheMock->expects($this->once())
                        ->method("setEntry")
                        ->with($cacheEntry);
        $cacheEntry->save();
    }
}
