<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Iblock\TypeTable;
use Bitrix\Iblock\IblockTable;

class atom_createib extends CModule
{
    public $MODULE_ID;
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;

    private array $ibParams;

    public function __construct()
    {
        $arModuleVersion = [];

        include(dirname(__FILE__) . '/version.php');

        $this->MODULE_ID = 'atom.createib';
        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        $this->MODULE_NAME = Loc::getMessage('ATOM_CREATEIB_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('ATOM_CREATEIB_DESCRIPTION');
        $this->PARTNER_NAME = Loc::getMessage('ATOM_CREATEIB_PARTNER_NAME');
        $this->PARTNER_URI = Loc::getMessage('ATOM_CREATEIB_PARTNER_URI');

        $this->ibParams = [
            'custom_iblock_type_books' => [
                'code' => 'custom_books',
                'api_code' => 'book',
                'name' => 'Книги',
                'ib_id' => '',
                'property_fields' => [
                    [
                        'NAME' => 'Автор',
                        'ACTIVE' => 'Y',
                        'SORT' => '100',
                        'MULTIPLE' => 'N',
                        'CODE' => 'AUTHOR',
                        'PROPERTY_TYPE' => 'S',
                    ],
                    [
                        'NAME' => 'Год',
                        'ACTIVE' => 'Y',
                        'SORT' => '100',
                        'MULTIPLE' => 'N',
                        'CODE' => 'YEAR',
                        'PROPERTY_TYPE' => 'N',
                    ],
                    [
                        'NAME' => 'Средняя оценка',
                        'ACTIVE' => 'Y',
                        'SORT' => '100',
                        'MULTIPLE' => 'N',
                        'CODE' => 'AVERAGE_RATING',
                        'PROPERTY_TYPE' => 'N',
                    ]
                ],
                'elements' => [
                    [
                        'name' => 'Отцы и дети',
                        'property_value' => [
                            'AUTHOR' => 'Тургенев',
                            'YEAR' => '1862',
                            'AVERAGE_RATING' => '3'
                        ],
                    ],
                    [
                        'name' => 'Дубровский',
                        'property_value' => [
                            'AUTHOR' => 'Пушкин',
                            'YEAR' => '1841',
                            'AVERAGE_RATING' => '3'
                        ]
                    ],
                    [
                        'name' => 'Объекты, шаблоны и методики программирования',
                        'property_value' => [
                            'AUTHOR' => 'Зандстра',
                            'YEAR' => '2021',
                            'AVERAGE_RATING' => '3'
                        ]
                    ],
                ]
            ],
            'custom_iblock_type_reviews' => [
                'code' => 'custom_reviews',
                'api_code' => 'review',
                'name' => 'Отзывы на книги',
                'ib_id' => '',
                'property_fields' => [
                    [
                        'NAME' => 'Текст',
                        'ACTIVE' => 'Y',
                        'SORT' => '100',
                        'MULTIPLE' => 'N',
                        'CODE' => 'TEXT',
                        'PROPERTY_TYPE' => 'S',
                    ],
                    [
                        'NAME' => 'Оценка',
                        'ACTIVE' => 'Y',
                        'SORT' => '100',
                        'MULTIPLE' => 'N',
                        'CODE' => 'RATE',
                        'PROPERTY_TYPE' => 'S',
                    ],
                    [
                        'NAME' => 'Книга',
                        'ACTIVE' => 'Y',
                        'SORT' => '100',
                        'MULTIPLE' => 'N',
                        'CODE' => 'BOOK',
                        'PROPERTY_TYPE' => 'S',
                    ]
                ],
                'elements' => [
                    [
                        'name' => 'отзыв1',
                        'property_value' => [
                            'TEXT' => 'Классная книга1',
                            'RATE' => '5',
                            'BOOK' => 'Отцы и дети',
                        ],
                    ],
                    [
                        'name' => 'отзыв2',
                        'property_value' => [
                            'TEXT' => 'Классная книга2',
                            'RATE' => '5',
                            'BOOK' => 'Дубровский',
                        ]
                    ],
                    [
                        'name' => 'отзыв3',
                        'property_value' => [
                            'TEXT' => 'Классная книга3',
                            'RATE' => '5',
                            'BOOK' => 'Объекты, шаблоны и методики программирования',
                        ]
                    ],
                ]
            ],
        ];
    }

    public function DoInstall()
    {
        ModuleManager::registerModule($this->MODULE_ID);

        $this->createIblockType();
        $this->createIblock();
        $this->addProp($this->ibParams);
        $this->addElements($this->ibParams);
    }

    public function DoUninstall()
    {
        $this->delIblocks();

        ModuleManager::unRegisterModule($this->MODULE_ID);
    }

    //создание типа инфоблока
    private function createIblockType(): void
    {
        global $DB;
        Loader::IncludeModule('iblock');
        $dbIblockTtype = TypeTable::getList([
            'select' => ['ID'],
            'filter' => ['ID' => array_keys($this->ibParams)]
        ])->fetchAll();
        if (!$dbIblockTtype) {
            foreach ($this->ibParams as $type => $arItems) {
                $obBlocktype = new CIBlockType;
                $DB->StartTransaction();
                $arIbType = [
                    'ID' => $type,
                    'SECTIONS' => 'N',
                    'IN_RSS' => 'N',
                    'SORT' => 500,
                    'LANG' => [
                        'ru' => [
                            'NAME' => $arItems['name'],
                        ]
                    ]
                ];
                if (!$obBlocktype->Add($arIbType)) {
                    $DB->Rollback();
                    echo 'Error: ' . $obBlocktype->LAST_ERROR;
                    exit();
                } else {
                    $DB->Commit();
                }
            }
        }
    }

    //создание инфоблока
    private function createIblock(): void
    {
        Loader::IncludeModule('iblock');
        $arIb = IblockTable::GetList([
            'select' => ['ID'],
            'filter' => [
                'IBLOCK_TYPE_ID' => array_keys($this->ibParams),
                [
                    'LOGIC' => 'OR',
                    ['CODE' => $this->ibParams['custom_iblock_type_books']['code']],
                    ['CODE' => $this->ibParams['custom_iblock_type_reviews']['code']]
                ],
            ],
        ])->fetchAll();
        if (!$arIb) {
            $ib = new CIBlock;
            foreach ($this->ibParams as $type => $arItems) {
                $arFields = [
                    'ACTIVE' => 'Y',
                    'NAME' => $arItems['name'],
                    'CODE' => $arItems['code'],
                    'API_CODE' => $arItems['api_code'],
                    'IBLOCK_TYPE_ID' => $type,
                    'SITE_ID' => 's1',
                ];
                $this->ibParams[$type]['ib_id'] = $ib->Add($arFields);
            }
        }
    }

    //создание свойств ИБ
    private function addProp($ibockIds): void
    {
        Loader::IncludeModule('iblock');

        $ibp = new CIBlockProperty;
        foreach ($ibockIds as $arItems) {
            foreach ($arItems['property_fields'] as $arProps) {
                $arProps['IBLOCK_ID'] = $arItems['ib_id'];
                if (!$ibp->Add($arProps)) {
                    echo 'Error: ' . $ibp->LAST_ERROR;
                    exit();
                }
            }
        }
    }

    //добавление элементов в ИБ
    private function addElements($ibParams): void
    {
        Loader::IncludeModule('iblock');

        $el = new CIBlockElement;
        foreach ($ibParams as $arItems) {
            foreach ($arItems['elements'] as $arElements) {
                $arLoadProductArray = [
                    'IBLOCK_ID' => $arItems['ib_id'],
                    'NAME' => $arElements['name'],
                    'ACTIVE' => 'Y',
                    'PROPERTY_VALUES' => $arElements['property_value'],
                ];
                if (!$el->Add($arLoadProductArray)) {
                    echo 'Error: ' . $el->LAST_ERROR;
                    exit();
                }
            }
        }
    }

    //удаление ИБ
    private function delIblocks(): void
    {
        global $DB;
        CModule::IncludeModule('iblock');

        foreach ($this->ibParams as $type => $arItems) {
            $DB->StartTransaction();
            if (!CIBlockType::Delete($type)) {
                $DB->Rollback();

                CAdminMessage::ShowMessage([
                    'TYPE' => 'ERROR',
                    'DETAILS' => '',
                    'HTML' => true
                ]);
            }
            $DB->Commit();
        }
    }
}