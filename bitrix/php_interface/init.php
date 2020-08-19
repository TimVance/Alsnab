<?php


use Bitrix\Sale;
use Bitrix\Main\Context;


function sendMailManager() {
    $catalog_id  = 29; // Каталог
    $provider_id = 35; // Поставщики
    $block_orders = 37; // Заказы

    if (CModule::IncludeModule("sale")) {
        $arFilter  = array("@STATUS_ID" => array("N", "S"));
        $rsSales   = CSaleOrder::GetList(array("DATE_INSERT" => "ASC"), $arFilter);
        $providers = array();
        while ($arSales = $rsSales->Fetch()) {
            $order  = Sale\Order::load(intval($arSales["ID"]));
            $order_iblock_res = CIBlockElement::GetList(
                [],
                ["IBLOCK_ID" => $block_orders, "PROPERTY_order" => intval($arSales["ID"])],
                false,
                [],
                ["ID", "IBLOCK_ID", "PROPERTY_status"]
            );
            while ($order_iblock = $order_iblock_res->GetNext()) {
                $info_order_iblock = $order_iblock;
            }
            $not_sent = false;
            if (empty($info_order_iblock["ID"])) {
                $not_sent = true;
            }
            if ($not_sent) {
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
        }
        foreach ($providers as $provider) {
            $res_provider = CIBlockElement::GetList(
                array(),
                array("IBLOCK_ID" => $provider_id, "ID" => $provider["id"]),
                false,
                array(),
                array("IBLOCK_ID", "ID", "PROPERTY_manager")
            );
            $user_id      = '';
            while ($ob = $res_provider->GetNext()) {
                $user_id = $ob["PROPERTY_MANAGER_VALUE"];
            }

            $rsUser = CUser::GetByID($user_id);
            $arUser = $rsUser->Fetch();

            if (!empty($arUser["EMAIL"])) {
                $orders_info = '';
                foreach ($provider["orders"] as $order_info) {
                    $orders_info .= 'Заказ №' . $order_info . ' - <a href="//' . $_SERVER["SERVER_NAME"] . '/manager/?order=' . $order_info . '">' . $_SERVER["SERVER_NAME"] . '/manager/?order=' . $order_info . '</a><br>';
                }

                // Send Email
                $arEventFields = array(
                    "ORDERS" => $orders_info,
                    "MAIL_MANAGER" => $arUser["EMAIL"]
                );

                if (CEvent::Send("NOTICE_MANAGER", 's1', $arEventFields)) {
                    foreach ($provider["orders"] as $order_info) {
                        $order_iblock_res = CIBlockElement::GetList(
                            [],
                            ["IBLOCK_ID" => $block_orders, "PROPERTY_order" => intval($order_info)],
                            false,
                            [],
                            ["ID", "IBLOCK_ID", "PROPERTY_status"]
                        );
                        while ($order_iblock_item = $order_iblock_res->GetNext()) $order_iblock_item_info = $order_iblock_item;
                        if (!empty($order_iblock_item_info["ID"])) {
                            CIBlockElement::SetPropertyValuesEx($order_iblock_item_info["ID"], false, ["status" => 321]);
                        }
                        else {
                            $el = new CIBlockElement;
                            $PROP = array();
                            $PROP[657] = 321; // Статус - отправлено
                            $PROP[655] = $order_info; // Номер заказа
                            $arLoadProductArray = Array(
                                "MODIFIED_BY"    => $USER->GetID(),
                                "IBLOCK_ID"      => $block_orders,
                                "PROPERTY_VALUES"=> $PROP,
                                "NAME"           => "Заказ №".$order_info,
                                "ACTIVE"         => "Y",
                            );
                            $el->Add($arLoadProductArray);
                        }
                    }
                }
            }
        }
    }
    return "sendMailManager();";
}