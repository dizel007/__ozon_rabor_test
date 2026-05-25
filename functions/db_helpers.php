<?php

 function get_tokens($pdo) {
   $stmt = $pdo->prepare("SELECT * FROM `tokens`");
   $stmt->execute();
   $arr_tokens = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($arr_tokens as $tokens) {
   $new_arr_tokens[$tokens['name_market']] = $tokens;
}

return $new_arr_tokens;

}