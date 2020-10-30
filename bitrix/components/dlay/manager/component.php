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


if (!empty($get["order"])) {


    // Запись заказа
    if ($post["action"] == "write") {


        // Записываем счет
        $id_element_file = '';
        if (!empty($_FILES["file"]["tmp_name"])) {
            $uploads_dir = $_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/orders_bills/';

            if (!file_exists($uploads_dir)) {
                mkdir($uploads_dir, 0777, true);
            }

            $name = $uploads_dir . rand(1, 9999) . "_" . time();

            move_uploaded_file($_FILES["file"]["tmp_name"], $name);

            $el_file   = new CIBlockElement;
            $PROP      = array();
            $PROP[669] = CFile::MakeFileArray($name);

            $arLoadProductArray = array(
                "MODIFIED_BY"     => $USER->GetID(),
                "IBLOCK_ID"       => 39,
                "PROPERTY_VALUES" => $PROP,
                "NAME"            => $user_id,
                "ACTIVE"          => "Y",
            );

            if ($PRODUCT_ID = $el_file->Add($arLoadProductArray)) {
                $id_element_file = $PRODUCT_ID;
            }
        }

        // Записываем счет


        // Проверка на существование заказа
        $order_exist_id     = '';
        $order_exist_params = [];
        $test_orders        = CIBlockElement::getList(
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

            $PROP        = array();
            $PROP[655]   = $post["order"];
            $PROP[667][] = $user_id;

            if ($is_admin) $PROP[657] = 322;
            else $PROP[657] = 318;

            foreach ($post["product_id"] as $i => $value) {

                Cmodule::IncludeModule('catalog');
                CCatalogProduct::Update($post["product_id"][$i], ["WEIGHT" => floatval($post["weight"][$i])]);

                $PROP[656][$i]["VALUE"] = $post["product_id"][$i] . '|' . $post["stock"][$i];
                if ($post["stock"][$i] == "change") {
                    $PROP[656][$i]["DESCRIPTION"] = $post["new_art"][$i] . '|' . $post["new_name"][$i] . '|' . $post["new_cnt"][$i] . "|" . $post["new_price"][$i] . "|" . $post["new_weight"][$i];
                }
            }

            if (!empty($id_element_file)) {
                $PROP[668][] = [
                    "VALUE"       => $user_id,
                    "DESCRIPTION" => $id_element_file
                ];
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

            $handle = [];
            $res    = CIBlockElement::GetProperty($order_id, $order_exist_id, "sort", "asc", array("CODE" => "handle"));
            while ($ob = $res->GetNext()) {
                $handle[$ob["VALUE"]] = $ob["VALUE"];
            }

            if (!isset($handle[$user_id])) {
                $handle[$user_id] = $user_id;
            }

            $PROP[667] = $handle;

            if ($is_admin) $PROP[657] = 322;
            else $PROP[657] = 318;

            foreach ($post["product_id"] as $i => $value) {

                Cmodule::IncludeModule('catalog');
                CCatalogProduct::Update($post["product_id"][$i], ["WEIGHT" => floatval($post["weight"][$i])]);

                $PROP[656][$i]["VALUE"] = $post["product_id"][$i] . '|' . $post["stock"][$i];
                if ($post["stock"][$i] == "change") {
                    $PROP[656][$i]["DESCRIPTION"] = $post["new_art"][$i] . '|' . $post["new_name"][$i] . '|' . $post["new_cnt"][$i] . "|" . $post["new_price"][$i] . "|" . $post["new_weight"][$i];
                }
            }

            // Запись файлов
            $prop_files = [];
            $res        = CIBlockElement::GetProperty($order_id, $order_exist_id, "sort", "asc", array("CODE" => "bills"));
            while ($ob_f = $res->GetNext()) {
                $prop_files[$ob_f["VALUE"]] = $ob_f["DESCRIPTION"];
            }

            if (!empty($id_element_file))
                $prop_files[$user_id] = $id_element_file;

            foreach ($prop_files as $i_f => $prop_file_desc) {
                $PROP[668][] = [
                    "VALUE"       => $i_f,
                    "DESCRIPTION" => $prop_file_desc
                ];
            }
            // Запись файлов

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
                $arResult["error"] = "Заказ успешно сохранен!";
            else
                $arResult["error"] = "Error: " . $res->LAST_ERROR;
        }
        // Запись заказа
    }


    $arResult["status"]  = 'Новый';
    $res_orders          = CIBlockElement::GetList(
        array(),
        array("IBLOCK_ID" => $order_id, "ACTIVE" => "Y", "PROPERTY_order" => intval($get["order"])),
        false,
        array(),
        array("ID", "IBLOCK_ID", "PROPERTY_status")
    );
    $res_orders_item_res = array();
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

                    $res_providers     = CIBlockElement::GetList(
                        array(),
                        array("IBLOCK_ID" => $providers_id, "ACTIVE" => "Y", "ID" => $ob["PROPERTY_PROVIDER_VALUE"]),
                        false,
                        array(),
                        array("ID", "IBLOCK_ID", "PROPERTY_manager")
                    );
                    $res_provider_info = array();
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
                        "DETAIL_PAGE_URL", "CATALOG_WEIGHT"
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
                        $arResult["items"][$ob["ID"]]["id"]     = $ob["ID"];
                        $arResult["items"][$ob["ID"]]["name"]   = $ob["NAME"];
                        $arResult["items"][$ob["ID"]]["art"]    = $ob["PROPERTY_CML2_ARTICLE_VALUE"];
                        $arResult["items"][$ob["ID"]]["link"]   = $ob["DETAIL_PAGE_URL"];
                        $arResult["items"][$ob["ID"]]["pro"]    = $ob["PROPERTY_PROVIDER_VALUE"];
                        $arResult["items"][$ob["ID"]]["weight"] = $ob["CATALOG_WEIGHT"];
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
            $handle         = [];
            $upload_bills   = [];
            while ($ob_order = $res_order->GetNextElement()) {
                $ob_props       = $ob_order->GetProperties();
                $order_elements = $ob_props["elements"];
                $handle         = $ob_props["handle"]["VALUE"];
                $upload_bills   = $ob_props["bills"];
            }

            $upload_bills_array = [];
            if (!empty($upload_bills["VALUE"])) {
                foreach ($upload_bills["VALUE"] as $bill_user_index => $bill_user_user) {
                    $upload_bills_array[$bill_user_user] = $upload_bills["DESCRIPTION"][$bill_user_index];
                }
            }

            if (!empty($handle)) {
                foreach ($handle as $us) {
                    $rsUser = CUser::GetByID($us);
                    $arUser = $rsUser->Fetch();
                    if (!empty($arUser["ID"])) {
                        $arResult["handle"][$arUser["ID"]]["user"] = $arUser;
                        if (!empty($upload_bills_array[$arUser["ID"]])) {
                            $res_file_prop        = CIBlockElement::GetProperty(39, $upload_bills_array[$arUser["ID"]], "sort", "asc", array("CODE" => "bill"));
                            $id_file_bill = '';
                            while ($ob_file_prop = $res_file_prop->GetNext()) {
                                $id_file_bill = $ob_file_prop["VALUE"];
                            }
                            if (!empty($id_file_bill)) {
                                $arResult["handle"][$arUser["ID"]]["bill"] = CFile::GetPath($id_file_bill);
                            }
                        }
                    }
                }
            }
            foreach ($order_elements["VALUE"] as $i => $order_el) {
                $item_info = explode("|", $order_el);
                if (!empty($arResult["items"][$item_info[0]])) {
                    $arResult["items"][$item_info[0]]["stock"] = $item_info[1];
                    $description                               = $order_elements["DESCRIPTION"][$i];
                    if (!empty($description)) {
                        $desc_info                                      = explode("|", $description);
                        $arResult["items"][$item_info[0]]["new_art"]    = (!empty($desc_info[0]) ? $desc_info[0] : '');
                        $arResult["items"][$item_info[0]]["new_name"]   = (!empty($desc_info[1]) ? $desc_info[1] : '');
                        $arResult["items"][$item_info[0]]["new_cnt"]    = (!empty($desc_info[2]) ? $desc_info[2] : '');
                        $arResult["items"][$item_info[0]]["new_price"]  = (!empty($desc_info[3]) ? $desc_info[3] : '');
                        $arResult["items"][$item_info[0]]["new_weight"] = (!empty($desc_info[4]) ? $desc_info[4] : '');
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

            $line = array("ID", "Арт.", "Наименование", "Кол-во", "Цена", "Вес", "Наличие");
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
                    $item["weight"],
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
                        $item["new_weight"],
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
        $rsSales  = CSaleOrder::GetList(
            array("DATE_INSERT" => "DESC"),
            $arFilter,
            false,
            array("nTopCount" => 30)
        );
        while ($arSales = $rsSales->Fetch()) {
            $order = Sale\Order::load(intval($arSales["ID"]));
            if (!empty($order)) {
                $basket = $order->getBasket();
                $ids    = array();
                foreach ($basket as $basketItem) {
                    $ids[] = $basketItem->getProductId();
                }
                if (!empty($ids)) {
                    $providers_res = CIBlockElement::GetList(
                        array(),
                        array("IBLOCK_ID" => $providers_id, "PROPERTY_provider" => $user_id),
                        false,
                        false,
                        array("ID", "BLOCK_ID", "PROPERTY_manager")
                    );
                    $provider_id   = '';
                    while ($provider_res = $providers_res->GetNext()) {
                        $provider_id = $provider_res;
                    }
                    if (!empty($provider_id)) {
                        $cnt = CIBlockElement::GetList(
                            array(),
                            array("IBLOCK_ID" => $catalog_id, "ID" => $ids, "PROPERTY_provider" => $provider_id),
                            array(),
                            false,
                            array()
                        );
                        if (!empty($cnt) || $is_admin) {

                            // Проверка на существование заказа в инфоблоке
                            $res_orders          = CIBlockElement::GetList(
                                array(),
                                array(
                                    "IBLOCK_ID"      => $order_id,
                                    "ACTIVE"         => "Y",
                                    "PROPERTY_order" => intval($arSales["ID"])
                                ),
                                false,
                                array(),
                                array("ID", "IBLOCK_ID", "PROPERTY_status")
                            );
                            $res_orders_item_res = array();
                            while ($res_orders_item = $res_orders->GetNext()) {
                                $res_orders_item_res = $res_orders_item;
                            }
                            if (empty($res_orders_item_res["ID"])) {
                                $arResult["items"][$arSales["ID"]]["price"] = $basket->getPrice();
                                $arResult["items"][$arSales["ID"]]["stock"] = 'Новый';
                            } else {
                                $arResult["items"][$arSales["ID"]]["price"] = 0;
                                $arResult["items"][$arSales["ID"]]["stock"] = 'Новый';

                                if (!empty($res_orders_item_res["PROPERTY_STATUS_VALUE"]))
                                    $arResult["items"][$arSales["ID"]]["stock"] = $res_orders_item_res["PROPERTY_STATUS_VALUE"];

                                // Получаем информацию о товарах и заказе
                                $price   = 0;
                                $arGoods = [];
                                foreach ($basket as $basketItem) {
                                    $arGoods[$basketItem->getProductId()]["price"]    = $basketItem->getPrice();
                                    $arGoods[$basketItem->getProductId()]["quantity"] = $basketItem->getQuantity();
                                }

                                $res_order      = CIBlockElement::GetList(
                                    array(),
                                    array("IBLOCK_ID" => $order_id, "ACTIVE" => "Y", "PROPERTY_order" => $arSales["ID"]),
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
                                    $item_info                       = explode("|", $order_el);
                                    $arGoods[$item_info[0]]["stock"] = $item_info[1];
                                    $description                     = $order_elements["DESCRIPTION"][$i];
                                    if (!empty($description)) {
                                        $desc_info                           = explode("|", $description);
                                        $arGoods[$item_info[0]]["new_cnt"]   = (!empty($desc_info[2]) ? $desc_info[2] : '');
                                        $arGoods[$item_info[0]]["new_price"] = (!empty($desc_info[3]) ? $desc_info[3] : '');
                                    }
                                }
                                foreach ($arGoods as $arGood) {
                                    if (!empty($arGood["stock"])) {
                                        if ($arGood["stock"] == "available") {
                                            $price += floatval($arGood["price"]) * floatval($arGood["quantity"]);
                                        } elseif ($arGood["stock"] == "change") {
                                            $price += floatval($arGood["new_price"]) * floatval($arGood["new_cnt"]);
                                        }
                                    } else {
                                        $price += floatval($arGood["price"]) * floatval($arGood["quantity"]);
                                    }
                                }
                                $arResult["items"][$arSales["ID"]]["price"] = $price;
                            }
                            $arResult["items"][$arSales["ID"]]["id"]   = $arSales["ID"];
                            $arResult["items"][$arSales["ID"]]["date"] = $order->getDateInsert();
                        }
                    }
                }
            }
        }
    endif;
}

$this->IncludeComponentTemplate();