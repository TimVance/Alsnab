<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Sale;
use Bitrix\Main\Context;

$request = Context::getCurrent()->getRequest();
$get     = $request->getQueryList();
$post    = $request->getPostList();

global $USER;

$catalog_id        = 29;
$order_id          = 37;
$arResult["error"] = '';
//$user_id = $USER->GetId();
$user_id = 15066;

if (!empty($post["action"])) {
    if ($post["action"] == "write") {

        // Проверка на существование заказа
        $order_exist_id = '';
        $test_orders = CIBlockElement::getList(
            array(),
            array("IBLOCK_ID" => $order_id, "PROPERTY_order" => $post["order"]),
            false,
            array(),
            array("ID", "IBLOCK_ID")
        );
        while ($test_order = $test_orders->GetNext()) {
            $order_exist_id = $test_order["ID"];
        }

        if (empty($order_exist_id)) {
            $el = new CIBlockElement;

            $PROP      = array();
            $PROP[655] = $post["order"];

            foreach ($post["product_id"] as $i => $value) {
                $PROP[656][$i]["VALUE"] = $post["product_id"][$i].'|'.$post["stock"][$i];
                if ($post["stock"][$i] == "change") {
                    $PROP[656][$i]["DESCRIPTION"] = $post["new_art"][$i].'|'.$post["new_name"][$i].'|'.$post["new_cnt"][$i]."|".$post["new_price"][$i];
                }
            }

            $arLoadProductArray = array(
                "MODIFIED_BY"     => $USER->GetID(),
                "IBLOCK_ID"       => $order_id,
                "PROPERTY_VALUES" => $PROP,
                "NAME"            => 'Заказ №' . $post["order"],
                "ACTIVE"          => "Y",
            );

            if ($PRODUCT_ID = $el->Add($arLoadProductArray))
                $arResult["error"] = "Заказ успешно сохранен!";
            else
                $arResult["error"] = "Error: " . $el->LAST_ERROR;
        }
        else {
            $el = new CIBlockElement;

            $PROP      = array();
            $PROP[655] = $post["order"];

            foreach ($post["product_id"] as $i => $value) {
                $PROP[656][$i]["VALUE"] = $post["product_id"][$i].'|'.$post["stock"][$i];
                if ($post["stock"][$i] == "change") {
                    $PROP[656][$i]["DESCRIPTION"] = $post["new_art"][$i].'|'.$post["new_name"][$i].'|'.$post["new_cnt"][$i]."|".$post["new_price"][$i];
                }
            }

            $arLoadProductArray = array(
                "MODIFIED_BY"     => $USER->GetID(),
                "IBLOCK_ID"       => $order_id,
                "PROPERTY_VALUES" => $PROP,
                "NAME"            => 'Заказ №' . $post["order"],
                "ACTIVE"          => "Y",
            );

            $PRODUCT_ID = $order_exist_id;
            $res = $el->Update($PRODUCT_ID, $arLoadProductArray);

            if ($res)
                $arResult["error"] = "Заказ успешно обновлен!";
            else
                $arResult["error"] = "Error: " . $res->LAST_ERROR;
        }
    } else $arResult["error"] = 'Неизвестное действие!';
} elseif (!empty($get["order"])) {
    $order = Sale\Order::load(intval($get["order"]));
    if (!empty($order)) {
        $basket = $order->getBasket();

        $arResult["show"] = "order";
        $arResult["id"]   = intval($get["order"]);
        $arResult["sum"]  = $basket->getPrice();
        $arResult["date"] = $order->getDateInsert();

        $ids = [];
        foreach ($basket as $basketItem) {
            $ids[]                                                      = $basketItem->getProductId();
            $arResult["items"][$basketItem->getProductId()]["price"]    = $basketItem->getPrice();
            $arResult["items"][$basketItem->getProductId()]["quantity"] = $basketItem->getQuantity();
        }
        if (!empty($ids)) {
            $res = CIBlockElement::GetList(
                array(),
                array("IBLOCK_ID" => $catalog_id, "ACTIVE" => "Y", "ID" => $ids),
                false,
                array(),
                array(
                    "ID", "NAME", "PROPERTY_provider", "PROPERTY_CML2_ARTICLE",
                    "DETAIL_PAGE_URL"
                )
            );
            while ($ob = $res->GetNext()) {
                if ($user_id == $ob["PROPERTY_PROVIDER_VALUE"]) {
                    $arResult["items"][$ob["ID"]]["id"]   = $ob["ID"];
                    $arResult["items"][$ob["ID"]]["name"] = $ob["NAME"];
                    $arResult["items"][$ob["ID"]]["art"]  = $ob["PROPERTY_CML2_ARTICLE_VALUE"];
                    $arResult["items"][$ob["ID"]]["link"] = $ob["DETAIL_PAGE_URL"];
                    $arResult["items"][$ob["ID"]]["pro"]  = $ob["PROPERTY_PROVIDER_VALUE"];
                } else unset($arResult["items"][$ob["ID"]]);
            }
        }
    } else $arResult["error"] = 'Заказ с таким номером не найден!';
} elseif (!empty($get["action"])) {
    if ($get["action"] == "show_all") {
        echo 'Номера заказов с товарами';
    } else $arResult["error"] = 'Неизвестное действие!';
} else $arResult["error"] = 'Не указан номер заказа!';

$this->IncludeComponentTemplate();