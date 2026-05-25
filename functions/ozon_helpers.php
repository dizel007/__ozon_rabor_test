<?php

/*************************************************************************************************
 * Функция вычитывает все заказы с заданным состоянием
 *************************************************************************************************/
function get_all_waiting_posts_for_need_date($token, $client_id, $date_query_ozon, $send_status, $dop_days_query){
    // awaiting_packaging - заказы ожидают сборку
    // awaiting_deliver   - заказы ожидают отгрузку 

$temp_dop_day = "+".$dop_days_query.' day';
$date_query_ozon_end = date('Y-m-d', strtotime($temp_dop_day, strtotime($date_query_ozon)));

$send_data=  array(
    "dir" => "ASC",
    "filter" => array(
    "cutoff_from" => $date_query_ozon."T00:00:00Z",
    "cutoff_to" =>   $date_query_ozon_end."T23:59:59Z",
    "delivery_method_id" => [ ],
    "provider_id" => [ ],
    "status" => $send_status,
    "warehouse_id" => [ ]
    ),
    "limit" => 1000,
    "offset" => 0,
    "with" => array(
    "analytics_data"  => true,
    "barcodes"  => true,
    "financial_data" => true,
    "translit" => true
    )
    );

$send_data = json_encode($send_data, JSON_UNESCAPED_UNICODE)  ;  

$ozon_dop_url = "v3/posting/fbs/unfulfilled/list";

// запустили запрос на озона
$res = send_injection_on_ozon($token, $client_id, $send_data, $ozon_dop_url );
return $res;
}




/************************************************************************************************
 * 
 *  ФУНЦКИЯ РАЗБИВАЕТ ЗАКАЗ НА ЕДИНИЧНЫЕ ОТПРАВЛЕНИЯ
 *  И переводит заказ в состояния ожидает отгрузки
 * 
 ***********************************************************************************************/

 function make_packeges_for_one_post_2($token, $client_id, $one_post_arr_for_zakaz) {

    $posting_number = $one_post_arr_for_zakaz["posting_number"];
    
    $send_data_arr  = array  (
        "packages"=> array(
        ),
        "posting_number" => $posting_number, // НОмер отправления
        "with" => array(
        "additional_data"=> true
        )
    );
    
    
    // формируем массив для каждой позиции товара
    foreach ($one_post_arr_for_zakaz['products'] as $key => $products) {
    
        for ($i=0; $i < $products['quantity']; $i++) {
                 $product = array(
                    "products" => array(
                        array(
                        "product_id" => $products['sku'],
                        "quantity" => 1 
                        )
                    )
                );
                // Готовим лист подбора
                $new_arr_list_podbora[$key][]= array ("sky" => $products['sku'] , "name" => $products['name'] , "quantity" => 1);
                 // готовим данные для разбивки заказа по отправлениям
                $send_data_arr['packages'][] =  $product;
        }
    }
    
    // echo "<pre>";
    // print_r($send_data_arr);
    // echo "<pre>";
    $res['list_podbora'] = $new_arr_list_podbora;
    // echo "<br>***********************************************************************************<br>";
    
    // die('make_new_ZAAKZ_DIE');
    
    $send_data_arr_js = json_encode($send_data_arr);
    $ozon_dop_url = "v4/posting/fbs/ship";
    
    ///////        НЕПОСРЕДСТВЕННЫЙ ЗАПУСК ИНЪЕКЦИИ НА САЙТЕ ОЗОН (перевод заказа в собранный)
    /* раскоментировать для работы */
    
    $res['obmen'] = send_injection_on_ozon($token, $client_id, $send_data_arr_js, $ozon_dop_url );
    return $res;
    };
    
    




/************************************************************************
 * Достаем штрих коды массива заказов (отправления)
 * РАБОЧАЯ ВЕРСИЯ 
 *************************************************************************/
function get_all_barcodes_for_all_sending ($token, $client_id, $string_etiket, $date_send, $path_etiketki,$wait_time_etikets) {

    // Данные запроса
    $send_data='
    {
        "posting_number": ['.
        $string_etiket.'
        ]
      }
    ';
 
    // Метод запрос на подготовку этикетки 
    $ozon_dop_url ="v2/posting/fbs/package-label/create"; // маленькая этикетка (новый метод)
    $res = send_injection_on_ozon($token, $client_id, $send_data, $ozon_dop_url );
    
// Если товаров много, то увеличиваем время ожидания формирования этикеток;

    sleep($wait_time_etikets);
    // Получаем task_id на скачивание файла с штрих кодами
    $task_id = $res['result']['tasks'][0]['task_id'];// маленькая этикетка (новый метод)
    

    // echo "<br> Задание на скачивание отправлено : ";
    // print_r($task_id);
    // die();


    $send_data='{"task_id":'.$task_id.'}';
    
    $ozon_dop_url ="v1/posting/fbs/package-label/get";
    $res = send_injection_on_ozon($token, $client_id, $send_data, $ozon_dop_url );
//  echo "<br>******* Ссылка на скачивание ***************************************************<br>";

    // print_r($res);
    $url = $res['result']['file_url']; // получаем информацию в формате PDF 

            
    // НАзвание файла с этикеткой	
        $file = $date_send.".pdf";
    // echo "<br>************** FILE ****************************************************************<br>";
    // print_r($file);

        if (file_put_contents($path_etiketki."/".$file, file_get_contents($url)))
        {
            // echo "Файл со штрихкодам получен";
        }
        else
        {
            echo "Ошибка скачивания файла со штрихкодами.";
        }
    
       return $file;
 }  


 
/*******************************************************************
 * Непосредственный запрос данных в озон
 ***********************************************************************/

function send_injection_on_ozon($token, $client_id, $send_data, $ozon_dop_url ) {
 
	$ch = curl_init('https://api-seller.ozon.ru/'.$ozon_dop_url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Api-Key:' . $token,
		'Client-Id:' . $client_id, 
		'Content-Type:application/json'
	));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $send_data); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_HEADER, false);
	$res = curl_exec($ch);

    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Получаем HTTP-код

	curl_close($ch);
	
	$res = json_decode($res, true);
        if (intdiv($http_code,100) <>2 ) {
        echo     'Результат обмена ОЗОН('.$ozon_dop_url.'): '.$http_code. "<br>";
        }
   
    return($res);	

}



/**
 * Решение задачи о рюкзаке (0/1) для выбора заказов.
 * Выбирает подмножество заказов, чтобы суммарное количество целевого товара
 * было максимальным, но не превышало вместимость паллеты.
 *
 * @param array $orders Массив заказов, каждый с ключами 'id' и 'target_qty'
 * @param int $capacity Вместимость паллеты (максимальное суммарное количество)
 * @return array ['selected_ids' => int[], 'total_qty' => int]
 */
function knapsackSelectOrders(array $orders, int $capacity): array
{
    // Убираем заказы с нулевым или отрицательным количеством
    $orders = array_filter($orders, fn($o) => ($o['target_qty'] ?? 0) > 0);
    $orders = array_values($orders); // переиндексируем

    $n = count($orders);
    if ($n === 0 || $capacity <= 0) {
        return ['selected_ids' => [], 'total_qty' => 0];
    }

    // dp[c] = максимальная сумма target_qty при вместимости c
    $dp = array_fill(0, $capacity + 1, -1);
    $dp[0] = 0;
    // Для восстановления ответа: храним [индекс_заказа, предыдущая_вместимость]
    $prev = array_fill(0, $capacity + 1, null);

    foreach ($orders as $idx => $order) {
        $qty = $order['target_qty'];
        if ($qty <= 0) continue;

        for ($c = $capacity; $c >= $qty; $c--) {
            if ($dp[$c - $qty] !== -1 && $dp[$c - $qty] + $qty > $dp[$c]) {
                $dp[$c] = $dp[$c - $qty] + $qty;
                $prev[$c] = ['idx' => $idx, 'prev_cap' => $c - $qty];
            }
        }
    }

    // Находим лучшую достижимую сумму (не превышающую capacity)
    $bestCap = 0;
    for ($c = 1; $c <= $capacity; $c++) {
        if ($dp[$c] > $dp[$bestCap]) {
            $bestCap = $c;
        }
    }
    $totalQty = $dp[$bestCap];

    // Восстанавливаем выбранные заказы
    $selected = [];
    $cur = $bestCap;
    while ($cur > 0 && $prev[$cur] !== null) {
        $selected[] = $orders[$prev[$cur]['idx']]['id'];
        $cur = $prev[$cur]['prev_cap'];
    }

    return [
        'selected_ids' => array_reverse($selected),
        'total_qty' => $totalQty,
    ];
}