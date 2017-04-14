<?php
namespace d3yii2\d3store\phpunit\repository\repository;

use d3yii2\d3store\repository\Transactions;
use d3yii2\d3store\phpunit\repository\CreateData;
use PHPUnit\Framework\TestCase;

class TransactionsTest extends TestCase
{

    public static function setUpBeforeClass()
    {
        CreateData::clear();
        CreateData::gen();
    }

    public function testLoad(){
        $l1 = Transactions::load(new \DateTime(),1000,CreateData::$stackToId,CreateData::$refLoadId,777);
        $this->assertEquals(1000,$l1->quantaty);

        $l2 = Transactions::load(new \DateTime(),2000,CreateData::$stackToId,CreateData::$refLoadId,778);
        $this->assertEquals(2000,$l2->quantaty);

        $u1 = Transactions::unLoad(new \DateTime(),1500,CreateData::$stackToId,CreateData::$refUnLoadId,1);
        $this->assertEquals(1500,$u1->quantaty);

        $r = Transactions::writeOffFifo($u1);
        $this->assertTrue($r);

        $balance = Transactions::getStackBalance(CreateData::$stackToId);
        $this->assertEquals(1500,$balance);

        $u1 = Transactions::unLoad(new \DateTime(),500,CreateData::$stackToId,CreateData::$refUnLoadId,1);
        $this->assertEquals(500,$u1->quantaty);

        $r = Transactions::writeOffFifo($u1);
        $this->assertTrue($r);

        $balance = Transactions::getStackBalance(CreateData::$stackToId);
        $this->assertEquals(1000,$balance);
    }
    public static function tearDownAfterClass()
    {
        CreateData::clear();
    }
}
