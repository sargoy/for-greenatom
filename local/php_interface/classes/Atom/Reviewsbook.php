<?php

namespace Atom;

use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\Elements\ElementReviewTable;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;

class Reviewsbook
{
    private static array $arResult;

    public static function getList($limit = false): array
    {
        $bookEntity = IblockTable::compileEntity('book');

        $rsReviewElement = ElementReviewTable::getList([
            'order' => ['ID' => 'ASC'],
            'select' => [
                'TITLE' => 'PROP.NAME',
                'AUTHOR_' => 'PROP.AUTHOR',
                'YEAR_' => 'PROP.YEAR',
                'DATE_CREATE',
                'TEXT_' => 'TEXT',
                'RATE_' => 'RATE',
                'BOOK_' => 'BOOK'
            ],
            'filter' => [
                'WF_PARENT_ELEMENT_ID' => false
            ],
            'limit' => $limit,
            'runtime' => [
                (new Reference(
                    'PROP',
                    $bookEntity,
                    Join::on('this.BOOK_VALUE', 'ref.NAME')
                ))
                    ->configureJoinType(Join::TYPE_LEFT)
            ]
        ]);

        while ($arFields = $rsReviewElement->fetch()) {
            self::$arResult[] = [
                'date' => $arFields['DATE_CREATE']->toString(),
                'text' => $arFields['TEXT_VALUE'],
                'rating' => $arFields['RATE_VALUE'],
                'book' => [
                    'title' => $arFields['TITLE'],
                    'author' => $arFields['AUTHOR_VALUE'],
                    'year' => $arFields['YEAR_IBLOCK_GENERIC_VALUE'],
                ]
            ];
        }

        return self::$arResult;
    }
}