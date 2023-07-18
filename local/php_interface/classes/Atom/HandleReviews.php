<?php

namespace Atom;

use Bitrix\Iblock\Elements\ElementBookTable;
use Bitrix\Iblock\Elements\ElementReviewTable;

class HandleReviews
{
    private static int $reviewIbclokId = 27;
    private static int $bookIbclokId = 26;
    private static int $reviewPropertyBookId = 99;

    public static function addRate($arFields): void
    {
        if ($arFields['ID'] == $arFields['WF_PARENT_ELEMENT_ID'] && $arFields['IBLOCK_ID'] == self::$reviewIbclokId) {
            $arElement = ElementBookTable::getList([
                'select' => ['ID', 'AVERAGE_RATING_' => 'AVERAGE_RATING'],
                'filter' => ['NAME' => $arFields['PROPERTY_VALUES'][self::$reviewPropertyBookId]['n0']['VALUE']]
            ])->fetch();
            if ($arElement) {
                $updatedProp = $arElement['AVERAGE_RATING_IBLOCK_GENERIC_VALUE'] + 1;
                \CIBlockElement::SetPropertyValuesEx($arElement['ID'], self::$bookIbclokId, ['AVERAGE_RATING' => $updatedProp]);
            }
        }
    }

    public static function delRate($id): void
    {
        $rsReviewElement = ElementReviewTable::getList([
            'select' => ['ID', 'NAME', 'BOOK_' => 'BOOK'],
            'filter' => ['ID' => $id]
        ])->fetch();
        $arElement = ElementBookTable::getList([
            'select' => ['ID', 'NAME', 'AVERAGE_RATING_' => 'AVERAGE_RATING'],
            'filter' => ['NAME' => $rsReviewElement['BOOK_VALUE']]
        ])->fetch();
        if ($arElement) {
            $updatedProp = $arElement['AVERAGE_RATING_IBLOCK_GENERIC_VALUE'] - 1;
            if ($updatedProp) {
                \CIBlockElement::SetPropertyValuesEx($arElement['ID'], self::$bookIbclokId, ['AVERAGE_RATING' => $updatedProp]);
            }
        }
    }

    public static function updateRate($arFields): void
    {
        if ($arFields['IBLOCK_ID'] == self::$reviewIbclokId) {
            $name = '';
            foreach ($arFields['PROPERTY_VALUES'][self::$reviewPropertyBookId] as $arValue) {
                $name = $arValue['VALUE'];
            }
            $arElement = ElementBookTable::getList([
                'select' => ['ID', 'AVERAGE_RATING_' => 'AVERAGE_RATING'],
                'filter' => ['NAME' => $name]
            ])->fetch();
            if ($arElement) {
                $updatedProp = $arElement['AVERAGE_RATING_IBLOCK_GENERIC_VALUE'] + 1;
                \CIBlockElement::SetPropertyValuesEx($arElement['ID'], self::$bookIbclokId, ['AVERAGE_RATING' => $updatedProp]);
            }
        }
    }
}