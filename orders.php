<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php"); ?>

<?php

use Bitrix\Sale;
use Bitrix\Main\Context;

$catalog_id  = 29; // Каталог
$provider_id = 35; // Поставщики

if (CModule::IncludeModule("sale")) {
    $arFilter  = array("@STATUS_ID" => array("N", "S"));
    $rsSales   = CSaleOrder::GetList(array("DATE_INSERT" => "ASC"), $arFilter);
    $providers = array();
    while ($arSales = $rsSales->Fetch()) {
        $order  = Sale\Order::load(intval($arSales["ID"]));
        $basket = $order->getBasket();
        $ids    = array();
        foreach ($basket as $basketItem) {
            $ids[] = $basketItem->getProductId();
        }
        if (!empty($ids)) {
            $res_catalog = CIBlockElement::GetList(
                array(),
                array("IBLOCK_ID" => $catalog_id, "ID" => $ids),
                false,
                array(),
                array("ID", "NAME", "PROPERTY_provider")
            );
            while ($ob = $res_catalog->GetNext()) {
                if (!empty($ob["PROPERTY_PROVIDER_VALUE"]))
                    $providers[$ob["PROPERTY_PROVIDER_VALUE"]]["id"] = $ob["PROPERTY_PROVIDER_VALUE"];
                $providers[$ob["PROPERTY_PROVIDER_VALUE"]]["orders"][$arSales["ID"]] = $arSales["ID"];
            }
        }
    }
    foreach ($providers as $provider) {
        $res_provider = CIBlockElement::GetList(
            array(),
            array("IBLOCK_ID" => $provider_id, "ID" => $provider["id"]),
            false,
            array(),
            array("IBLOCK_ID", "ID", "PROPERTY_manager")
        );
        $user_id = '';
        while ($ob = $res_provider->GetNext()) {
            $user_id = $ob["PROPERTY_MANAGER_VALUE"];
        }

        $orders_info = '';
        foreach ($provider["orders"] as $order_info) {
            $order_info .= 'Заказ №'.$orders_info.' - '.$_SERVER["SERVER_NAME"].'/manager/?order='.$orders_info.'<br>';
        }

        // Send Email
        $arEventFields = array(
            "LINK"                  => $orders_info,
        );
        if (CEvent::Send("NOTICE_MANAGER", 's1', $arEventFields)) {
            echo 'Отправлено!';
        }
    }
}


?>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>