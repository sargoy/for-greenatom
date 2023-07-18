<?php

$eventManager = \Bitrix\Main\EventManager::getInstance();

$eventManager->addEventHandlerCompatible(
    'iblock',
    'OnAfterIBlockElementAdd',
    [
        '\\Atom\\HandleReviews',
        'addRate'
    ]
);
$eventManager->addEventHandlerCompatible(
    'iblock',
    'OnBeforeIBlockElementDelete',
    [
        '\\Atom\\HandleReviews',
        'delRate'
    ]
);
$eventManager->addEventHandlerCompatible(
    'iblock',
    'OnAfterIBlockElementUpdate',
    [
        '\\Atom\\HandleReviews',
        'updateRate'
    ]
);