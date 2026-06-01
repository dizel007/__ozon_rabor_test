<?php
/******************************************************************************************************************
****** Функуия для формирования файла для 1С *********************
/******************************************************************************************************************/
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
// Из полученного массива формируем массив данных,$array_art   для создания Заказа в 1С.
function make_array_for_1c_file($res) {
    $kolvo_tovarov = 0;
    foreach ($res as $posts) {
       foreach ($posts['products'] as $prods) 
         {
             $artick_temp2 = $prods['offer_id'];
             if ($artick_temp2 == '82401-чн') { // подмена артикула для второго черного 
                 $artick_temp2 = '82401-Ч';
             }
             if ($artick_temp2 == '82401-чм') { // подмена артикула для второго черного 
                 $artick_temp2 = '82401-Ч';
             }
             
      /////////////////////////////////////////////////////////////////////////////////////////       
             
             if ($artick_temp2 == '82401-ч(fbs)') { // подмена артикула 
                 $artick_temp2 = '82401-Ч';
             }
             if ($artick_temp2 == '82401-з(fbs)') { // подмена артикула 
                 $artick_temp2 = '82401-З';
             }
             if ($artick_temp2 == '82401-к(fbs)') { // подмена артикула 
                 $artick_temp2 = '82401-К';
             }
             if ($artick_temp2 == '85400-ч(fbs)') { // подмена артикула 
                 $artick_temp2 = '85400-ч';
             }



 ///////////////////////////////////////////////////////////////////////////////////////////





             if ($artick_temp2 == '82401-зм') { // подмена артикула для второго черного 
                 $artick_temp2 = '82401-З';
             }
             if ($artick_temp2 == '82401-чи') { // подмена артикула для второго черного 
                 $artick_temp2 = '82401-Ч';
             }
             if ($artick_temp2 == '82400-чи') { // подмена артикула для второго черного 
                 $artick_temp2 = '82400-Ч';
             }
             if ($artick_temp2 == '82401-км') { // подмена артикула для второго черного 
                 $artick_temp2 = '82401-К';
             }
             if ($artick_temp2 == '000882401-brown') { // подмена артикула для второго черного 
                $artick_temp2 = '82401-К';
            }
            if ($artick_temp2 == '000882401-black') { // подмена артикула для второго черного 
                $artick_temp2 = '82401-Ч';
            }

             if ($artick_temp2 == 'ANM.39*59') { // подмена артикула для маленькой решетки
                 $artick_temp2 = '301';
             }
             if ($artick_temp2 == 'ANM.49*99') { // подмена артикула для большой решетки 
                 $artick_temp2 = '302';
             }
 
             $artick_temp2 = mb_strtolower($artick_temp2);
 
            $array_art[$artick_temp2] = @$array_art[$artick_temp2] + $prods['quantity'];
            $kolvo_tovarov = $kolvo_tovarov + $prods['quantity'];
         //    echo $prods['price']."<br>";
           $array_art_price[$artick_temp2] = array("price"    => $prods['price'],
                                                   "quantity" => $array_art[$artick_temp2],
                                                   "name"     => $prods['name']);
        
}

    }
return  $array_art_price;
}

/******************************************************************************************************************
****** Функуия для формирования файла для 1С *********************
/******************************************************************************************************************/



function  make_1c_file($array_for_1C, $date_query_ozon, $nomer_zakaz, $path_temp_docs) {


 if (isset($array_for_1C)) {
    // Создаем файл для 1С
    $spreadsheet = new Spreadsheet();
// 4. Получаем активный лист (по умолчанию он один)
    $sheet = $spreadsheet->getActiveSheet();
    
    $i=1;
   //  echo "<pre>";
        foreach ($array_for_1C as $key => $items) {
    // print_r($items);	
        $sheet->setCellValue("A".$i, $key);
        $sheet->setCellValue("C".$i, $items['quantity']);
        $sheet->setCellValue("D".$i, round($items['price'], 2));
        $i++; // смешение по строкам
    
    }
    
  // 7. Сохраняем файл
$writer = new Xlsx($spreadsheet);
$file_name_1c_list = $path_temp_docs."/".$date_query_ozon." (".$nomer_zakaz.") file_1C.xlsx";
$writer->save($file_name_1c_list);

          
    } 
   
    return     $file_name_1c_list;
   }

