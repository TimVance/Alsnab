<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Sale;
use Bitrix\Main\Context;

$request = Context::getCurrent()->getRequest();
$get     = $request->getQueryList();

global $USER;

$catalog_id = 29;
$arResult["error"] = '';
//$user_id = $USER->GetId();
$user_id = 15066;

if (!empty($get["order"])) {
    $order = Sale\Order::load(intval($get["order"]));
    if (!empty($order)) {
        $basket = $order->getBasket();

        $arResult["show"] = "order";
        $arResult["id"] = intval($get["order"]);
        $arResult["sum"] = $basket->getPrice();
        $arResult["date"] = $order->getDateInsert();

        $ids    = [];
        foreach ($basket as $basketItem) {
            $ids[] = $basketItem->getProductId();
            $arResult["items"][$basketItem->getProductId()]["price"] = $basketItem->getPrice();
            $arResult["items"][$basketItem->getProductId()]["quantity"] = $basketItem->getQuantity();
        }
        if (!empty($ids)) {
            $res = CIBlockElement::GetList(
                array(),
                array("IBLOCK_ID" => $catalog_id, "ACTIVE" => "Y", "ID" => $ids),
                false,
                array(),
                array("ID", "NAME", "PROPERTY_provider", "PROPERTY_CML2_ARTICLE",
                    "DETAIL_PAGE_URL")
            );
            while ($ob = $res->GetNext()) {
                if ($user_id == $ob["PROPERTY_PROVIDER_VALUE"]) {
                    $arResult["items"][$ob["ID"]]["name"] = $ob["NAME"];
                    $arResult["items"][$ob["ID"]]["art"] = $ob["PROPERTY_CML2_ARTICLE_VALUE"];
                    $arResult["items"][$ob["ID"]]["link"] = $ob["DETAIL_PAGE_URL"];
                    $arResult["items"][$ob["ID"]]["pro"] = $ob["PROPERTY_PROVIDER_VALUE"];
                }
                else unset($arResult["items"][$ob["ID"]]);
            }
        }
    }
    else $arResult["error"] = 'Заказ с таким номером не найден!';
}
elseif (!empty($get["action"])) {
    if ($get["action"] == "show_all") {
        echo 'Номера заказов с товарами';
    }
    else $arResult["error"] = 'Неизвестное действие!';
}
else $arResult["error"] = 'Не указан номер заказа!';

$this->IncludeComponentTemplate();