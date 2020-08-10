<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * @var array $arResult
 */

/*
echo '<pre>';
print_r($arResult);
echo '</pre>';
*/

?><div class="mng-wrapper"><?


if (!empty($arResult["error"])) {
    echo $arResult["error"];
}

if ($arResult["show"] == "order"): ?>
    <div class="mng-order">
        <h3>Заказ № <?=$arResult["id"]?> от <?=date("d.m.Y", strtotime($arResult["date"]))?></h3>
        <form method="post">
            <input type="hidden" name="action" value="write">
            <input type="hidden" name="order" value="<?=$arResult["id"]?>">
            <div class="mng-list">
                <div class="mng-item head">
                    <span class="mng-check"><input type="checkbox"></span>
                    <span class="mng-art">Артикул</span>
                    <span class="mng-name">Наименование</span>
                    <span class="mng-cnt">Кол-во</span>
                    <span class="mng-price">Цена</span>
                    <span class="mng-stock">Наличие</span>
                </div>
                <?php foreach ($arResult["items"] as $item): ?>
                    <div class="mng-item">

                        <input type="hidden" name="product_id[]" value="<? echo $item["id"]; ?>">
                        <input type="hidden" name="cnt[]" value="<? echo $item["cnt"]; ?>">

                        <span class="mng-check"><input type="checkbox"></span>
                        <span class="mng-art">
                            <? echo $item["art"]; ?>
                            <span class="change-field change-name">
                                <input name="new_art[]" value="<?=$item["new_art"]?>" type="text" placeholder="Новый артикул">
                            </span>
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
                    <span class="mng-name"></span>
                    <span class="mng-cnt">Итог:</span>
                    <span class="mng-price">
                        <?=number_format($arResult["sum"], 2, ".", " ")?>
                    </span>
                    <span class="mng-stock"></span>
                </div>
            </div>
            <div class="mng-button"><input class="btn btn-lg btn-default has-ripple" type="submit" value="Отправить"></div>
        </form>
    </div>
    <div class="mng-show-all"><a href="/manager/?action=show_all">Все заказы</a></div>
<? elseif ($arResult["show"] == "all"): ?>
    <? foreach ($arResult["items"] as $item): ?>
        <div>
            <a href="/manager/?order=<?=$item["id"]?>">Заказ №<?=$item["id"]?> от <?=date("d.m.Y", strtotime($item["date"]))?></a>
        </div>
    <? endforeach; ?>
<? endif; ?>
</div>