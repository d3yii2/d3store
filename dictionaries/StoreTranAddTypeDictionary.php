<?php

namespace d3yii2\d3store\dictionaries;

use d3yii2\d3store\models\StoreTranAddType;
use Exception;
use Yii;
use yii\helpers\ArrayHelper;

class StoreTranAddTypeDictionary
{

    private const CACHE_KEY_LIST_NAME = 'StoreTranAddDictionaryNameList';
    private const CACHE_KEY_LIST_CODE = 'StoreTranAddDictionaryCodeList1';
    /**
     * @var false|mixed|string
     */
    private static ?array $_codeList = null;
    private static ?array $_nameList = null;

    /**
     * @return string[]
     */
    public static function getNameList(): array
    {
        return self::$_nameList ?? (
        self::$_nameList = Yii::$app->cache->getOrSet(
            self::CACHE_KEY_LIST_NAME,
            static function () {
                return ArrayHelper::map(
                    StoreTranAddType::find()
                        ->select([
                            'id' => '`store_tran_add_type`.`id`',
                            'name' => '`store_tran_add_type`.`name`',
                        ])
                        ->orderBy([
                            '`store_tran_add_type`.`name`' => SORT_ASC,
                        ])
                        ->asArray()
                        ->all(),
                    'id',
                    'name'
                );
            },
            60 * 60
        )
        );
    }

    /**
     * @throws Exception
     */
    public static function getIdByCode(string $code): ?int
    {
        return array_search($code, self::getCodeList(), true);
    }

    public static function addCode(?string $code, int $id): void
    {
        self::$_codeList[$id] = $code;
        self::clearCache();
    }

    public static function getCodeList(): array
    {
        return self::$_codeList
            ?? (
            self::$_codeList = Yii::$app->cache->getOrSet(
                self::CACHE_KEY_LIST_CODE,
                static function () {
                    return ArrayHelper::map(
                        StoreTranAddType::find()
                            ->select([
                                'id' => '`store_tran_add_type`.`id`',
                                'name' => '`store_tran_add_type`.`code`',
                            ])
                            ->orderBy([
                                '`store_tran_add_type`.`code`' => SORT_ASC,
                            ])
                            ->asArray()
                            ->all(),
                        'id',
                        'code'
                    );
                },
                60 * 60
            )
            );
    }


    /**
     * get label
     * @param int $id
     * @return string|null
     */
    public static function getNameLabel(int $id): ?string
    {
        return self::getNameList()[$id] ?? null;
    }

    public static function clearCache(): void
    {
        Yii::$app->cache->delete(self::CACHE_KEY_LIST_NAME);
        Yii::$app->cache->delete(self::CACHE_KEY_LIST_CODE);
    }
}
