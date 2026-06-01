<?php
require_once __DIR__ . '/../../init.php';
require_once '../../vendor/autoload.php';
require_once 'make_1c_file.php';



/*****************************************************************************************************************
 ******  Собираем данные ГЕТ запроса 
 ******************************************************************************************************************/
 $ozon_shop = $_GET['ozon_shop']; // название нашего магазина

 if ($ozon_shop == 'ozon_anmaks') {
       $token_ozon = $token_ozon;
       $client_id_ozon = $client_id_ozon;
 
   }
       
elseif ($ozon_shop == 'ozon_ip_zel') {
       $token_ozon =  $token_ozon_ip;
       $client_id_ozon =  $client_id_ozon_ip;
 } else {
       die ('МАГАЗИН НЕ ВЫБРАН');
 }

$number_order = $_GET['number_order'];
$now_date_razbora = $_GET['now_date_razbora'];
$date_query_ozon = $_GET['date_query_ozon'];
$dop_days_query =  $_GET['dop_days_query'];

/*****************************************************************************************************************
 ******  Формируем пути для файлов
 ******************************************************************************************************************/
$start_file_path = "../../!all_razbor/ozon/";
$path_temp_docs = $start_file_path.$now_date_razbora."/".$number_order."/temp_docs";
$path_etiketki = $start_file_path.$now_date_razbora."/".$number_order."/etiketki";

// echo "$path_excel_docs";

$file_name_OTLADKA = $path_temp_docs."/otladka.txt";
$startTime = microtime(true);
$text_otladka = $startTime." ".""."*************************** Перешли в файл ожидания "."\n";
file_put_contents($file_name_OTLADKA, $text_otladka, FILE_APPEND);

/*****************************************************************************************************************
 ******  Берем данные из ДЖЕСОН файла
 ******************************************************************************************************************/
$temp_path_all_order = $path_temp_docs."/".$number_order ."_json_order_for_razbor.json";
$orders_for_razbor = json_decode(file_get_contents($temp_path_all_order),true);


echo "<pre>";
// echo "<br> СЧИТАЛИ МАССИВ ДЛЯ ПЕРЕБОРА";
// print_r($orders_for_razbor);
// echo "<br> СЧИТАЛИ МАССИВ ДЛЯ ПЕРЕБОРА КОНЕЦ";
/*****************************************************************************************************************
 ******  Уходим на формирование этикетоук
 ******************************************************************************************************************/

$startTime = microtime(true);
$text_otladka = $startTime." "."Уходим в файл makeer_etikets "."\n";
file_put_contents($file_name_OTLADKA, $text_otladka, FILE_APPEND);


// die('DIE__make_etikets_for_need_zakaz');

require_once "make_etikets_for_need_zakaz.php";
