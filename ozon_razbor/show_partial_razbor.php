<?php
// ozon_razbor/show_partial_razbor.php
require_once __DIR__ . '/../init.php';

$shopName = $_GET['shop_name'] ?? '';
if (!in_array($shopName, ['ozon_anmaks', 'ozon_ip_zel'])) {
    die('Некорректный магазин');
}

// Выбираем токены
if ($shopName === 'ozon_anmaks') {
    $token = $token_ozon;
    $clientId = $client_id_ozon;
} else {
    $token = $token_ozon_ip;
    $clientId = $client_id_ozon_ip;
}

$nowDate = date('Y-m-d');
$dateQuery = date('Y-m-d', strtotime($nowDate . ' -15 day'));
$dopDays = 20;

// Запрос к API Ozon
$response = get_all_waiting_posts_for_need_date($token, $clientId, $dateQuery, "awaiting_packaging", $dopDays);

// awaiting_packaging — ожидает упаковки,
// awaiting_deliver — ожидает отгрузки,


// Извлекаем товары
$items = [];
$arr_sum_items['quantity'] = 0;
$arr_sum_items['price'] = 0;
if (!empty($response['result']['postings'])) {
    foreach ($response['result']['postings'] as $posting) {
        foreach ($posting['products'] as $product) {
            $art = $product['offer_id'];
            if (!isset($items[$art])) {
                $items[$art] = [
                    'name'     => $product['name'],
                    'price'    => $product['price'],
                    'quantity' => 0
                ];
            }
            $items[$art]['quantity'] += $product['quantity'];
            $items[$art]['price'] += $product['price'];
            $arr_sum_items['quantity'] += $product['quantity']; 
            $arr_sum_items['price'] += $product['price']; 
        }
    }
}


// echo "<pre>";
// print_r($items);
// die();


// Подключаем шаблон и передаём ему данные
$viewData = [
    'shopName'   => $shopName,
    'dateQuery'  => $dateQuery,
    'dopDays'    => $dopDays,
    'nowDate'    => $nowDate,
    'items'      => $items,
    'formAction' => 'controller/make_iz_dostavki_zakaz_.php'
];

require __DIR__ . '/templates/partial_razbor_form.php';