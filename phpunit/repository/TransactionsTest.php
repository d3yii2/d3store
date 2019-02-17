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
        $this->assertEquals(1000,$l1->quantity);

        $l2 = Transactions::load(new \DateTime(),2000,CreateData::$stackToId,CreateData::$refLoadId,778);

        $balance = Transactions::getStackBalance(CreateData::$stackToId, CreateData::$refLoadId,778);
        $this->assertEquals(2000,$balance);

        $u1 = Transactions::unLoadFifo(new \DateTime(),50000,CreateData::$stackToId,CreateData::$refUnLoadId,1);
        $this->assertFalse($u1);

        $u1 = Transactions::unLoadFifo(new \DateTime(),500,CreateData::$stackToId,CreateData::$refUnLoadId,1);
        $this->assertEquals(500,$u1[0]->quantity);

        $balance = Transactions::getStackBalance(CreateData::$stackToId, CreateData::$refLoadId,777);
        $this->assertEquals(500,$balance);

        $balance = Transactions::getStackBalance(CreateData::$stackToId);
        $this->assertEquals(2500,$balance);

        $u1 = Transactions::unLoadFifo(new \DateTime(),500,CreateData::$stackToId,CreateData::$refUnLoadId,1,CreateData::$refLoadId,778);

        $balance = Transactions::getStackBalance(CreateData::$stackToId);
        $this->assertEquals(2000,$balance);

        $balance = Transactions::getStackBalance(CreateData::$stackToId, CreateData::$refLoadId,778);
        $this->assertEquals(1500,$balance);

        $r = Transactions::moveFifo(new \DateTime(),200,CreateData::$stackToId,CreateData::$stackFromId);
        $this->assertTrue($r);

        $balanceTo = Transactions::getStackBalance(CreateData::$stackToId);
        $this->assertEquals(1800,$balanceTo);

        $balance = Transactions::getStackBalance(CreateData::$stackFromId);

        $this->assertEquals(200,$balance);

        $uSt2 = Transactions::unLoadFifo(new \DateTime(),100,CreateData::$stackFromId,CreateData::$refUnLoadId,2);
        $this->assertEquals(100,$uSt2[0]->quantity);

        $balanceFrom = Transactions::getStackBalance(CreateData::$stackFromId);
        $this->assertEquals(100,$balanceFrom);

        $allBalances = Transactions::getAllStacksBalance(CreateData::$storeId);

        foreach($allBalances as $b){
            if($b['id'] == CreateData::$stackFromId){
                $this->assertEquals($balanceFrom,$b['remain_quantity']);
                continue;
            }
            if($b['id'] == CreateData::$stackToId){
                $this->assertEquals($balanceTo,$b['remain_quantity']);
                continue;
            }
        }

    }
    public static function tearDownAfterClass()
    {
        CreateData::clear();
    }
}
