<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Sale;
use Bitrix\Main\Context;

$request = Context::getCurrent()->getRequest();
$get     = $request->getQueryList();
$post    = $request->getPostList();

global $USER;

$catalog_id        = 29;
$order_id          = 37;
$providers_id      = 35;
$arResult["error"] = '';
$user_id           = $USER->GetId();
$is_admin          = $arResult["is_admin"] = $USER->IsAdmin();


if (!empty($post["action"])) {
    if ($post["action"] == "write") {

        // Проверка на существование заказа
        $order_exist_id = '';
        $test_orders    = CIBlockElement::getList(
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

            if ($is_admin) $PROP[657] = 322;
            else $PROP[657] = 318;

            foreach ($post["product_id"] as $i => $value) {
                $PROP[656][$i]["VALUE"] = $post["product_id"][$i] . '|' . $post["stock"][$i];
                if ($post["stock"][$i] == "change") {
                    $PROP[656][$i]["DESCRIPTION"] = $post["new_art"][$i] . '|' . $post["new_name"][$i] . '|' . $post["new_cnt"][$i] . "|" . $post["new_price"][$i];
                }
            }

            $arLoadProductArray = array(
                "MODIFIED_BY"     => $user_id,
                "IBLOCK_ID"       => $order_id,
                "PROPERTY_VALUES" => $PROP,
                "NAME"            => 'Заказ №' . $post["order"],
                "ACTIVE"          => "Y",
            );

            if ($PRODUCT_ID = $el->Add($arLoadProductArray))
                $arResult["error"] = "Заказ успешно сохранен!";
            else
                $arResult["error"] = "Error: " . $el->LAST_ERROR;
        } else {
            $el = new CIBlockElement;

            $PROP      = array();
            $PROP[655] = $post["order"];

            if ($is_admin) $PROP[657] = 322;
            else $PROP[657] = 318;

            foreach ($post["product_id"] as $i => $value) {
                $PROP[656][$i]["VALUE"] = $post["product_id"][$i] . '|' . $post["stock"][$i];
                if ($post["stock"][$i] == "change") {
                    $PROP[656][$i]["DESCRIPTION"] = $post["new_art"][$i] . '|' . $post["new_name"][$i] . '|' . $post["new_cnt"][$i] . "|" . $post["new_price"][$i];
                }
            }

            $arLoadProductArray = array(
                "MODIFIED_BY"     => $user_id,
                "IBLOCK_ID"       => $order_id,
                "PROPERTY_VALUES" => $PROP,
                "NAME"            => 'Заказ №' . $post["order"],
                "ACTIVE"          => "Y",
            );

            $PRODUCT_ID = $order_exist_id;
            $res        = $el->Update($PRODUCT_ID, $arLoadProductArray);

            if ($res)
                $arResult["error"] = "Заказ успешно обновлен!";
            else
                $arResult["error"] = "Error: " . $res->LAST_ERROR;
        }
    } else $arResult["error"] = 'Неизвестное действие!';
} elseif (!empty($get["order"])) {
    $arResult["status"] = 'Новый';
    $res_orders         = CIBlockElement::GetList(
        array(),
        array("IBLOCK_ID" => $order_id, "ACTIVE" => "Y", "PROPERTY_id" => intval($get["order"])),
        false,
        array(),
        array("ID", "IBLOCK_ID", "PROPERTY_status")
    );
    while ($res_orders_item = $res_orders->GetNext()) {
        $res_orders_item_res = $res_orders_item;
    }
    if (empty($res_orders_item_res["ID"])) {
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

                    $res_providers = CIBlockElement::GetList(
                        array(),
                        array("IBLOCK_ID" => $providers_id, "ACTIVE" => "Y", "ID" => $ob["PROPERTY_PROVIDER_VALUE"]),
                        false,
                        array(),
                        array("ID", "IBLOCK_ID", "PROPERTY_manager")
                    );
                    while ($res_provider = $res_providers->GetNext())
                        $res_provider_info = $res_provider;

                    if ($user_id == $res_provider_info["PROPERTY_MANAGER_VALUE"] || $is_admin) {
                        $arResult["items"][$ob["ID"]]["id"]   = $ob["ID"];
                        $arResult["items"][$ob["ID"]]["name"] = $ob["NAME"];
                        $arResult["items"][$ob["ID"]]["art"]  = $ob["PROPERTY_CML2_ARTICLE_VALUE"];
                        $arResult["items"][$ob["ID"]]["link"] = $ob["DETAIL_PAGE_URL"];
                        $arResult["items"][$ob["ID"]]["pro"]  = $ob["PROPERTY_PROVIDER_VALUE"];
                    } else unset($arResult["items"][$ob["ID"]]);
                }
            }
        } else $arResult["error"] = 'Заказ с таким номером не найден!';
    } else {
        $order = Sale\Order::load(intval($get["order"]));
        $ids   = [];
        if (!empty($order)) {
            $basket = $order->getBasket();

            $arResult["show"] = "order";
            $arResult["id"]   = intval($get["order"]);
            $arResult["sum"]  = $basket->getPrice();
            $arResult["date"] = $order->getDateInsert();

            if (!empty($res_orders_item_res["PROPERTY_STATUS_VALUE"]))
                $arResult["status"] = $res_orders_item_res["PROPERTY_STATUS_VALUE"];

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

                    $res_providers = CIBlockElement::GetList(
                        array(),
                        array("IBLOCK_ID" => $providers_id, "ACTIVE" => "Y", "ID" => $ob["PROPERTY_PROVIDER_VALUE"]),
                        false,
                        array(),
                        array("ID", "IBLOCK_ID", "PROPERTY_manager")
                    );
                    while ($res_provider = $res_providers->GetNext())
                        $res_provider_info = $res_provider;

                    if ($user_id == $res_provider_info["PROPERTY_MANAGER_VALUE"] || $is_admin) {
                        $arResult["items"][$ob["ID"]]["id"]   = $ob["ID"];
                        $arResult["items"][$ob["ID"]]["name"] = $ob["NAME"];
                        $arResult["items"][$ob["ID"]]["art"]  = $ob["PROPERTY_CML2_ARTICLE_VALUE"];
                        $arResult["items"][$ob["ID"]]["link"] = $ob["DETAIL_PAGE_URL"];
                        $arResult["items"][$ob["ID"]]["pro"]  = $ob["PROPERTY_PROVIDER_VALUE"];
                    } else unset($arResult["items"][$ob["ID"]]);
                }
            }
            $res_order      = CIBlockElement::GetList(
                array(),
                array("IBLOCK_ID" => $order_id, "ACTIVE" => "Y", "PROPERTY_order" => $arResult["id"]),
                false,
                array(),
                array()
            );
            $order_elements = [];
            while ($ob_order = $res_order->GetNextElement()) {
                $ob_props       = $ob_order->GetProperties();
                $order_elements = $ob_props["elements"];
            }
            foreach ($order_elements["VALUE"] as $i => $order_el) {
                $item_info = explode("|", $order_el);
                if (!empty($arResult["items"][$item_info[0]])) {
                    $arResult["items"][$item_info[0]]["stock"] = $item_info[1];
                    $description                               = $order_elements["DESCRIPTION"][$i];
                    if (!empty($description)) {
                        $desc_info                                     = explode("|", $description);
                        $arResult["items"][$item_info[0]]["new_art"]   = (!empty($desc_info[0]) ? $desc_info[0] : '');
                        $arResult["items"][$item_info[0]]["new_name"]  = (!empty($desc_info[1]) ? $desc_info[1] : '');
                        $arResult["items"][$item_info[0]]["new_cnt"]   = (!empty($desc_info[2]) ? $desc_info[2] : '');
                        $arResult["items"][$item_info[0]]["new_price"] = (!empty($desc_info[3]) ? $desc_info[3] : '');
                    }
                }
            }
        }
    }
    if ($get["download"] == true) {
        if (!empty($arResult["items"])) {
            header('Content-Type: application/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="order' . $arResult["id"] . '.csv";');

            ob_end_clean();

            $f = fopen('php://output', 'w');
            fputs($f, chr(0xEF) . chr(0xBB) . chr(0xBF));

            $line = array("ID", "Арт.", "Наименование", "Кол-во", "Цена", "Наличие");
            fputcsv($f, $line, ";");

            $stocks = array(
                "available" => "В наличии",
                "change"    => "Замена",
                "no"        => "Нет в наличии"
            );

            foreach ($arResult["items"] as $item) {
                $line = array(
                    $item["id"],
                    $item["art"],
                    $item["name"],
                    $item["quantity"],
                    $item["price"],
                    $stocks[$item["stock"]]
                );
                fputcsv($f, $line, ";");
                if ($item["stock"] == "change") {
                    $line = array(
                        '',
                        $item["new_art"],
                        $item["new_name"],
                        $item["new_cnt"],
                        $item["new_price"],
                        ''
                    );
                    fputcsv($f, $line, ";");
                }
            }

            fclose($f);
            ob_flush();
            exit();
        }
    }
} else {
    $arResult["show"] = "all";
    if (CModule::IncludeModule("sale")):
        $arFilter = array("@STATUS_ID" => array("N", "S"));
        $rsSales  = CSaleOrder::GetList(array("DATE_INSERT" => "DESC"), $arFilter, false, array("nTopCount" => 30));
        while ($arSales = $rsSales->Fetch()) {
            $order = Sale\Order::load(intval($arSales["ID"]));
            if (!empty($order)) {
                $basket = $order->getBasket();
                $ids    = array();
                foreach ($basket as $basketItem) {
                    $ids[] = $basketItem->getProductId();
                }
                if (!empty($ids)) {
                    $cnt = CIBlockElement::GetList(
                        array(),
                        array("IBLOCK_ID" => $catalog_id, "ID" => $ids, "PROPERTY_provider" => $user_id),
                        array(),
                        false,
                        array()
                    );
                    if (!empty($cnt) || $is_admin) {
                        $arResult["items"][$arSales["ID"]]["id"]    = $arSales["ID"];
                        $arResult["items"][$arSales["ID"]]["date"]  = $order->getDateInsert();
                        $arResult["items"][$arSales["ID"]]["price"] = $basket->getPrice();
                        $arResult["items"][$arSales["ID"]]["stock"] = 'Новый';
                    }
                }
            }
        }
    endif;
}

$this->IncludeComponentTemplate();