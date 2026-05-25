<?php
// init.php - единая точка загрузки всех зависимостей

// 1. Конфигурация
require_once __DIR__ . '/config/db.php';      // возвращает $db_config = ['host'=>'...','db'=>'...']
// require_once __DIR__ . '/config/paths.php';   // define('PATH_EXCEL_DOCS', '...') и т.п.

// 2. Подключение всех функций (по группам)
require_once __DIR__ . '/functions/db_helpers.php';
require_once __DIR__ . '/functions/ozon_helpers.php';
// require_once __DIR__ . '/functions/excel_helpers.php';
require_once __DIR__ . '/functions/general_helpers.php';


// КОнстанты 

define('PROJECT_ROOT', realpath(__DIR__ . ''));
define('RAZBOR_BASE_PATH', PROJECT_ROOT . '/!all_razbor/ozon');



// 3. Подключение библиотек (PHPExcel, ZipArchive и т.д. – если не через Composer)
//    Лучше использовать Composer автозагрузку, но можно и так:

// ... остальные requires для PHPExcel

// 4. Создание подключения PDO
try {
    $pdo = new PDO(
        "mysql:host={$db_config['host']};dbname={$db_config['db']};charset=utf8",
        $db_config['user'],
        $db_config['password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB connection error: " . $e->getMessage());
}

// 5. Инициализация пользователя (куки, сессии, получение токенов)
//    Выносим эту логику тоже в функцию, но можно оставить здесь
if (isset($_COOKIE['id']) && isset($_COOKIE['hash'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_hash = ? LIMIT 1");
    $stmt->execute([$_COOKIE['hash']]);
    $userdata = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($userdata && $userdata['user_hash'] === $_COOKIE['hash'] && $userdata['user_id'] == $_COOKIE['id']) {
        // успешная авторизация
        setcookie("id", $userdata['user_id'], time() + 86400, "/");
        setcookie("hash", $userdata['user_hash'], time() + 86400, "/", null, null, true);
    } else {
        header("Location: login.php?error=1");
        exit;
    }
} else {
    header("Location: login.php?need_auth=1");
    exit;
}

// 6. Получение токенов и маркетов (функция get_tokens из db_helpers.php)
$tokens = get_tokens($pdo);   // возвращает все токены по магазинам

// Распаковываем в переменные для удобства (или оставляем массив)
  // Получаем все токены
    $arr_tokens = get_tokens($pdo);
    // ВБ АНМАКС
    $token_wb = $arr_tokens['wb_anmaks']['token'];
    // ВБ ZEL
    $token_wb_ip = $arr_tokens['wb_ip_zel']['token'];
    // ОЗОН АНМКАС
    $client_id_ozon = $arr_tokens['ozon_anmaks']['id_market'];
    $token_ozon = $arr_tokens['ozon_anmaks']['token'];
    // озон ИП зел
    $client_id_ozon_ip = $arr_tokens['ozon_ip_zel']['id_market'];
    $token_ozon_ip = $arr_tokens['ozon_ip_zel']['token'];

    // Яндекс ООО склад FBS
    $yam_token =  $arr_tokens['ya_anmaks_fbs']['token'];
    $campaignId_FBS =  $arr_tokens['ya_anmaks_fbs']['id_market'];
    // леруа
    $token_lerua = $arr_tokens['lerua']['token'];
    $lerua_limit_items = $arr_tokens['lerua']['limit_count'];
