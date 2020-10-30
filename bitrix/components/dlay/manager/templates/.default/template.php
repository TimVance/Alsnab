<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * @var array $arResult
 */

?><div class="mng-wrapper"><?


if (!empty($arResult["error"])) {
    echo $arResult["error"];
}


global $USER;

$user_id           = $USER->GetId();
$is_admin          = $USER->IsAdmin();
$itog = 0;


if ($arResult["show"] == "order"): ?>
    <? if (!empty($arResult["items"])): ?>
        <? $APPLICATION->AddChainItem('Заказ №'.$arResult["id"]); ?>
        <div class="mng-order">
            <div class="mng-show-all"><a href="/manager/">Все заказы</a></div>
            <h3>
                Заказ № <?=$arResult["id"]?> от <?=date("d.m.Y", strtotime($arResult["date"]))?>
                <a title="Скачать заказ" class="download-order" href="/manager/?order=<?=$arResult["id"]?>&download=true"><svg aria-hidden="true" focusable="false" data-prefix="fal" data-icon="download" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="svg-inline--fa fa-download fa-w-16 fa-2x"><path fill="currentColor" d="M452 432c0 11-9 20-20 20s-20-9-20-20 9-20 20-20 20 9 20 20zm-84-20c-11 0-20 9-20 20s9 20 20 20 20-9 20-20-9-20-20-20zm144-48v104c0 24.3-19.7 44-44 44H44c-24.3 0-44-19.7-44-44V364c0-24.3 19.7-44 44-44h99.4L87 263.6c-25.2-25.2-7.3-68.3 28.3-68.3H168V40c0-22.1 17.9-40 40-40h96c22.1 0 40 17.9 40 40v155.3h52.7c35.6 0 53.4 43.1 28.3 68.3L368.6 320H468c24.3 0 44 19.7 44 44zm-261.7 17.7c3.1 3.1 8.2 3.1 11.3 0L402.3 241c5-5 1.5-13.7-5.7-13.7H312V40c0-4.4-3.6-8-8-8h-96c-4.4 0-8 3.6-8 8v187.3h-84.7c-7.1 0-10.7 8.6-5.7 13.7l140.7 140.7zM480 364c0-6.6-5.4-12-12-12H336.6l-52.3 52.3c-15.6 15.6-41 15.6-56.6 0L175.4 352H44c-6.6 0-12 5.4-12 12v104c0 6.6 5.4 12 12 12h424c6.6 0 12-5.4 12-12V364z" class=""></path></svg></a>
                <span class="order-status" title="Статус"><?=$arResult["status"]?></span>
            </h3>
            <? if (!empty($arResult["handle"])) echo '<div>Редактировали:</div>'; ?>
            <? foreach ($arResult["handle"] as $handle): ?>
                <div><? echo $handle["LOGIN"]."(id: ".$handle["ID"].") ".$handle["NAME"]." ".$handle["LAST_NAME"]; ?></div>
            <? endforeach; ?>
            <br>
            <form method="post">
                <input type="hidden" name="action" value="write">
                <input type="hidden" name="order" value="<?=$arResult["id"]?>">
                <div class="mng-list">
                    <div class="mng-item head">
                        <span class="mng-check"><input type="checkbox"></span>
                        <span class="mng-art">Артикул</span>
                        <span class="mng-id">ID</span>
                        <span class="mng-name">Наименование</span>
                        <span class="mng-cnt">Кол-во</span>
                        <span class="mng-price">Цена</span>
                        <span class="mng-weight">Вес (грамм)</span>
                        <span class="mng-stock">Наличие</span>
                    </div>
                    <?php foreach ($arResult["items"] as $item): ?>
                        <? if (empty($item["name"])) continue; ?>
                        <?
                            if (empty($item["stock"]) || $item["stock"] == "available") {
                                $itog += floatval($item["quantity"]) * floatval($item["price"]);
                            }
                            elseif ($item["stock"] == "change") {
                                $itog += floatval($item["new_cnt"]) * floatval($item["new_price"]);
                            }
                        ?>
                        <div class="mng-item">

                            <input type="hidden" name="product_id[]" value="<? echo $item["id"]; ?>">
                            <input type="hidden" name="cnt[]" value="<? echo $item["quantity"]; ?>">

                            <span class="mng-check"><input type="checkbox"></span>
                            <span class="mng-art">
                                <? echo $item["art"]; ?>
                                <span class="change-field change-name">
                                    <input name="new_art[]" value="<?=$item["new_art"]?>" type="text" placeholder="Новый артикул">
                                </span>
                            </span>
                            <span class="mng-id">
                                <? echo $item["id"]; ?>
                            </span>
                            <span class="mng-name">
                                <a title="<?=$item["name"]; ?>" target="_blank" href="<? echo $item["link"]; ?>">
                                    <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="external-link-alt" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="svg-inline--fa fa-external-link-alt fa-w-16 fa-2x"><path fill="currentColor" d="M432,320H400a16,16,0,0,0-16,16V448H64V128H208a16,16,0,0,0,16-16V80a16,16,0,0,0-16-16H48A48,48,0,0,0,0,112V464a48,48,0,0,0,48,48H400a48,48,0,0,0,48-48V336A16,16,0,0,0,432,320ZM488,0h-128c-21.37,0-32.05,25.91-17,41l35.73,35.73L135,320.37a24,24,0,0,0,0,34L157.67,377a24,24,0,0,0,34,0L435.28,133.32,471,169c15,15,41,4.5,41-17V24A24,24,0,0,0,488,0Z" class=""></path></svg>
                                    <? echo $item["name"]; ?>
                                </a>
                                <span class="change-field change-name">
                                    <input name="new_name[]" value="<?=$item["new_name"]?>" type="text" placeholder="Новое имя">
                                </span>
                            </span>
                            <span class="mng-cnt">
                                <? echo $item["quantity"]; ?>
                                <span class="change-field change-cnt">
                                    <input name="new_cnt[]" value="<?=$item["new_cnt"]?>" type="text" value="<? echo $item["quantity"]; ?>" placeholder="Количество">
                                </span>
                            </span>
                            <span class="mng-price">
                                <? echo $item["price"]; ?>
                                <span class="change-field change-price">
                                    <input name="new_price[]" value="<?=$item["new_price"]?>" type="text" placeholder="Новая цена">
                                </span>
                            </span>
                            <span class="mng-weight">
                                <input name="weight[]" type="text" value="<?=$item["weight"]?>" placeholder="Вес">
                                <span class="change-field change-weight">
                                    <input name="new_weight[]" value="<?=$item["new_weight"]?>" type="text" placeholder="Новый вес">
                                </span>
                            </span>
                            <span class="mng-stock">
                                <select name="stock[]" class="js-change-stock">
                                    <option <?=($item["stock"] == "available" ? 'selected' : '')?> value="available">В наличии</option>
                                    <option <?=($item["stock"] == "change" ? 'selected' : '')?> value="change">Заменить</option>
                                    <option <?=($item["stock"] == "no" ? 'selected' : '')?> value="no">Нет в наличии</option>
                                </select>
                                <span class="change-field change-stock">
                                    <svg aria-hidden="true" focusable="false" data-prefix="fal" data-icon="level-down" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 512" class="svg-inline--fa fa-level-down fa-w-8 fa-2x"><path fill="currentColor" d="M252.478 408.503l-99.974 99.975c-4.697 4.697-12.311 4.697-17.008 0l-99.974-99.975c-4.696-4.697-4.696-12.311 0-17.008l8.503-8.503c4.697-4.697 12.311-4.697 17.007 0L126 447.959V36H24.024a11.996 11.996 0 0 1-8.485-3.515l-12-12C-4.021 12.926 1.333 0 12.024 0H138c13.255 0 24 10.745 24 24v423.959l64.967-64.966c4.697-4.697 12.311-4.697 17.007 0l8.503 8.503c4.697 4.696 4.697 12.31.001 17.007z" class=""></path></svg>
                                    замена на
                                </span>
                            </span>
                        </div>
                    <? endforeach; ?>
                    <div class="mng-item head">
                        <span class="mng-check"><input type="checkbox"></span>
                        <span class="mng-art">
                            <select class="js-change-stock-all">
                                <option value="available">В наличии</option>
                                <option value="change">Заменить</option>
                                <option value="no">Нет в наличии</option>
                            </select>
                        </span>
                        <span class="id"></span>
                        <span class="mng-name"></span>
                        <span class="mng-cnt"><? if ($is_admin) echo 'Итог:'; ?></span>
                        <span class="mng-price">
                            <? if ($is_admin)
                                echo number_format($itog, 2, ".", " "); ?>
                        </span>
                        <span class="mng-weight"></span>
                        <span class="mng-stock"></span>
                    </div>
                </div>
                <div class="mng-button">
                    <input
                            class="btn btn-lg btn-default has-ripple"
                            type="submit"
                            value="<?=($arResult["is_admin"] ? "Потвердить заказ" : "Потвердить наличие")?>
                    ">
                </div>
            </form>
        </div>
    <? else: ?>
        <p>Заказ недоступен!</p>
    <? endif; ?>
<? elseif ($arResult["show"] == "all"): ?>
        <div class="mng-list">
            <div class="mng-item">
                <span class="mng-list-name">Заказ</span>
                <span class="mng-list-date">Дата</span>
                <span class="mng-list-stock">Статус</span>
                <span class="mng-list-sum">Сумма</span>
            </div>
            <? foreach ($arResult["items"] as $item): ?>
                <div class="mng-item">
                    <span class="mng-list-name"><a href="/manager/?order=<?=$item["id"]?>">Заказ №<?=$item["id"]?></a></span>
                    <span class="mng-list-date"><?=date("d.m.Y", strtotime($item["date"]))?></span>
                    <span class="mng-list-stock"><?=$item["stock"]?></span>
                    <span class="mng-list-sum"><?=number_format($item["price"], 2, ".", " ")?> руб.</span>
                </div>
            <? endforeach; ?>
        </div>
<? endif; ?>
</div>