<?php
/**
 * Created by PhpStorm.
 * User: Uldis
 * Date: 2017.04.14.
 * Time: 18:43
 */

namespace d3yii2\d3store\phpunit\repository;


use d3yii2\d3store\models\StoreRef;
use d3yii2\d3store\models\StoreStack;
use d3yii2\d3store\models\StoreStore;
use d3yii2\d3store\models\StoreTransactions;
use d3yii2\d3store\models\StoreWoff;

class CreateData
{
    public static $stackToId;
    public static $stackFromId;
    public static $refLoadId;
    public static $refUnLoadId;

    public static function gen()
    {
        $store = new StoreStore();
        $store->name = 'TEST';
        $store->company_id = 777;
        $store->save();

        $stack = new StoreStack();
        $stack->store_id = $store->id;
        $stack->name = 'TEST TO';
        $stack->save();

        self::$stackToId = $stack->id;

        $stack = new StoreStack();
        $stack->store_id = $store->id;
        $stack->name = 'TEST FROM';
        $stack->save();

        self::$stackFromId = $stack->id;

        $ref = new StoreRef();
        $ref->name = 'TEST LOAD';
        $ref->save();
        self::$refLoadId = $ref->id;

        $ref = new StoreRef();
        $ref->name = 'TEST UNLOAD';
        $ref->save();
        self::$refUnLoadId = $ref->id;
    }

    public static function clear()
    {

        foreach(StoreStore::findAll(['company_id' => 777]) as $store) {


            foreach (StoreStack::findAll(['store_id' => $store->id]) as $stack) {
                foreach (StoreTransactions::findAll(['stack_from' => $stack->id]) as $tran) {
                    foreach (StoreWoff::findAll(['load_tran_id' => $tran->id]) as $woff) {
                        $woff->delete();
                    }
                    foreach (StoreWoff::findAll(['unload_tran_id' => $tran->id]) as $woff) {
                        $woff->delete();
                    }
                    $tran->delete();
                }
                foreach (StoreTransactions::findAll(['stack_to' => $stack->id]) as $tran) {
                    foreach (StoreWoff::findAll(['load_tran_id' => $tran->id]) as $woff) {
                        $woff->delete();
                    }
                    foreach (StoreWoff::findAll(['unload_tran_id' => $tran->id]) as $woff) {
                        $woff->delete();
                    }
                    $tran->delete();
                }
                $stack->delete();
            }

            $store->delete();
        }
        foreach(StoreRef::findAll(['name' => 'TEST LOAD']) as $ref){
            $ref->delete();
        }
        foreach(StoreRef::findAll(['name' => 'TEST UNLOAD']) as $ref){
            $ref->delete();
        }
        return true;
    }


}