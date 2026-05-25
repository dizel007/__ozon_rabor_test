<?php
 function make_new_dir_z($dir, $append) {
//    echo "<br>Создаем папку: $dir";
    if (!is_dir($dir)) {
        mkdir($dir, 0777, True);
    } 

}

function make_rigth_file_name($temp_file_name) {
    $temp_file_name=str_replace('*','_',$temp_file_name);
    $temp_file_name=str_replace('/','_',$temp_file_name);
    $temp_file_name=str_replace('\'','_',$temp_file_name);
    $temp_file_name=str_replace(':','_',$temp_file_name);
    $temp_file_name=str_replace('?','_',$temp_file_name);
    $temp_file_name=str_replace('>','_',$temp_file_name);
    $temp_file_name=str_replace('<','_',$temp_file_name);
    $temp_file_name=str_replace('|','_',$temp_file_name);
    $right_file_name=str_replace('"','_',$temp_file_name);
    return $right_file_name;
    }