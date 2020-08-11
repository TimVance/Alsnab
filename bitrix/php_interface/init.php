<?php

/*
use Bitrix\Main;
use Bitrix\Main\Entity;

$eventManager = Main\EventManager::getInstance();
$eventManager->addEventHandler("sale", "OnSaleComponentOrderCreated", "sendLinksOrder");

function sendLinksOrder($order)
{
    print_r($order);
    exit();
}