<?php
namespace d3yii2\d3store\phpunit\repository;

use d3system\exceptions\D3ActiveRecordException;
use d3yii2\d3store\dictionaries\StoreTranAddTypeDictionary;
use d3yii2\d3store\models\StoreTranAdd;
use d3yii2\d3store\models\StoreTranAddType;
use d3yii2\d3store\repository\Transactions;
use DateTime;
use PHPUnit\Framework\TestCase;
use Throwable;
use yii\db\Exception;
use yii\db\StaleObjectException;

class TransactionsTest extends TestCase
{

    public const ADD_TYPE_CODE = 'T08';

    /**
     * @throws Throwable
     * @throws StaleObjectException
     */
    public static function setUpBeforeClass(): void
    {
        CreateData::clear();
        CreateData::gen();
    }

    /**
     * @throws Exception
     * @throws \yii\base\Exception
     * @throws \Exception
     */
    public function testLoad(): void
    {
        /** load 1000 */
        $l1 = Transactions::load(new DateTime(),1000,CreateData::$stackToId,CreateData::$refLoadId,777);
        $this->assertEquals(1000,$l1->quantity);

        /** load 2000 to 778 */
        $l2 = Transactions::load(new DateTime(),2000,CreateData::$stackToId,CreateData::$refLoadId,778);

        /** balance 2000 to 778 */
        $balance = Transactions::getStackBalance(CreateData::$stackToId, CreateData::$refLoadId,778);
        $this->assertEquals(2000,$balance);

        /** try so many */
        $u1 = Transactions::unLoadFifo(new DateTime(),50000,CreateData::$stackToId,CreateData::$refUnLoadId,1);
        $this->assertFalse($u1);

        /** unload 500 */
        $u1 = Transactions::unLoadFifo(new DateTime(),500,CreateData::$stackToId,CreateData::$refUnLoadId,777);
        $this->assertEquals(500,$u1[0]->quantity);

        $balance = Transactions::getStackBalance(CreateData::$stackToId, CreateData::$refLoadId,777);
        $this->assertEquals(500,$balance);

        $balance = Transactions::getStackBalance(CreateData::$stackToId);
        $this->assertEquals(2500,$balance);

        $u1 = Transactions::unLoadFifo(new DateTime(),500,CreateData::$stackToId,CreateData::$refUnLoadId,1,CreateData::$refLoadId,[778]);

        $balance = Transactions::getStackBalance(CreateData::$stackToId);
        $this->assertEquals(2000,$balance);

        $balance = Transactions::getStackBalance(CreateData::$stackToId, CreateData::$refLoadId,778);
        $this->assertEquals(1500,$balance);

        $r = Transactions::moveFifo(new DateTime(),200,CreateData::$stackToId,CreateData::$stackFromId);
        $this->assertEquals(200,$r[0]->quantity);

        $balanceTo = Transactions::getStackBalance(CreateData::$stackToId);
        $this->assertEquals(1800,$balanceTo);

        $balance = Transactions::getStackBalance(CreateData::$stackFromId);

        $this->assertEquals(200,$balance);

        $uSt2 = Transactions::unLoadFifo(new DateTime(),100,CreateData::$stackFromId,CreateData::$refUnLoadId,2);
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

    /**
     * @throws Exception
     * @throws Throwable
     * @throws StaleObjectException
     */
    public static function tearDownAfterClass(): void
    {
        CreateData::clear();
    }
}
