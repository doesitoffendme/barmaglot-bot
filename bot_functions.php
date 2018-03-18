<?php
/**
 * Created by IntelliJ IDEA.
 * User: verget
 * Date: 15.03.16
 * Time: 13:03
 */
/**
 * Telegram Bot access token и URL.
 */
//header('Content-type: text/html; charset=utf-8');
$access_token = '103707926:AAGhb8dvwzJ7zTcTsb76obbH3z5sY_D49KY';
$api = 'https://api.telegram.org/bot' . $access_token;

/**
 * Задаём основные переменные.
 */
$meduza = 'https://meduza.io';
$meduza_rss = $meduza . '/rss/%s';
$meduza_api = $meduza . '/api/v3/search?chrono=news&page=0&per_page=10&locale=ru';

$gramota = 'http://gramota.ru';
$gramota_api = $gramota . '/slovari/dic/?lop=x&bts=x&word=';

$output = json_decode(file_get_contents('php://input'), TRUE);
$chat_id = $output['message']['chat']['id'];
$first_name = $output['message']['chat']['first_name'];
$message = $output['message']['text'];
//$message = $_GET['command'];

$rita_id = '68239504';
$current_time = date("d.m.y" ,time());
$rita_mod = false;
if ($chat_id == $rita_id)
    $rita_mod = true;
/**
 * Emoji для лучшего визуального оформления.
 */
$emoji = array(
    'preload' => json_decode('"\uD83D\uDE03"'), // Улыбочка.
    'newspaper' => json_decode('"\uD83D\uDCF0"'),// Газета.
    'pen' => json_decode('"\u270F"'), // Ручка
    'kiss' => json_decode('"\uD83D\uDE18"'), //Поцелуй
    'cat_smile' => json_decode('"\uD83D\uDE3A"'),
    'weather' => array(
        'clear' => json_decode('"\u2600"'), // Солнце.
        'clouds' => json_decode('"\u2601"'), // Облака.
        'rain' => json_decode('"\u2614"'), // Дождь.
        'snow' => json_decode('"\u2744"'), // Снег.
    ),
);
/**
 * Получаем команды от пользователя.
 */

$first_time = false;

if (file_exists(dirname(__FILE__).'/'.$chat_id.'.txt')) {
    $text = file_get_contents(dirname(__FILE__) . '/' . $chat_id . '.txt');
    if ($text != $current_time) {
        $first_time = true;
    }
}else{
    file_put_contents(dirname(__FILE__) . '/' . $chat_id . '.txt', date("d.m.y" ,time()));
    $first_time = true;
}

if ($first_time){
    $hello_text = "";
    if (date("H" ,time()) *1 < 9) {
        $hello_text .= "Доброе утро, ";
    }else
        $hello_text .= "Здравствуй, ";
    if ($rita_mod)
        $hello_text .= "Ритушка-лапушка";
    else
        $hello_text .= $first_name;
    sendMessage($chat_id, $hello_text);
}

switch($message) {
    // API погоды предоставлено OpenWeatherMap.
    // @see http://openweathermap.org
    case '/weather':
        // Отправляем приветственный текст.
        sendChatAction($chat_id, 'typing');
        // App ID для OpenWeatherMap.
        $appid = '33dea4cf04325e175c079b3fd2eaabfe';
        // ID для города/района/местности (есть все города РФ).
        $id = '542420'; // Для примера: Петербург, север города.
        // Получаем JSON-ответ от OpenWeatherMap.
        $pogoda = json_decode(file_get_contents('http://api.openweathermap.org/data/2.5/weather?appid=' . $appid . '&id=' . $id . '&units=metric&lang=ru'), TRUE);
        // Определяем тип погоды из ответа и выводим соответствующий Emoji.
        if ($pogoda['weather'][0]['main'] === 'Clear') { $weather_type = $emoji['weather']['clear'] . ' ' . $pogoda['weather'][0]['description']; }
        elseif ($pogoda['weather'][0]['main'] === 'Clouds') { $weather_type = $emoji['weather']['clouds'] . ' ' . $pogoda['weather'][0]['description']; }
        elseif ($pogoda['weather'][0]['main'] === 'Rain') { $weather_type = $emoji['weather']['rain'] . ' ' . $pogoda['weather'][0]['description']; }
        elseif ($pogoda['weather'][0]['main'] === 'Snow') { $weather_type = $emoji['weather']['snow'] . ' ' . $pogoda['weather'][0]['description']; }
        else $weather_type = $pogoda['weather'][0]['description'];
        // Температура воздуха.
        if ($pogoda['main']['temp'] > 0) { $temperature = '+' . sprintf("%u", $pogoda['main']['temp']); }
        else { $temperature = sprintf("%u", $pogoda['main']['temp']); }
        // Направление ветра.
        if ($pogoda['wind']['deg'] >= 0 && $pogoda['wind']['deg'] <= 11.25) { $wind_direction = 'северный'; }
        elseif ($pogoda['wind']['deg'] > 11.25 && $pogoda['wind']['deg'] <= 78.75) { $wind_direction = 'северо-восточный, '; }
        elseif ($pogoda['wind']['deg'] > 78.75 && $pogoda['wind']['deg'] <= 101.25) { $wind_direction = 'восточный, '; }
        elseif ($pogoda['wind']['deg'] > 101.25 && $pogoda['wind']['deg'] <= 168.75) { $wind_direction = 'юго-восточный, '; }
        elseif ($pogoda['wind']['deg'] > 168.75 && $pogoda['wind']['deg'] <= 191.25) { $wind_direction = 'южный, '; }
        elseif ($pogoda['wind']['deg'] > 191.25 && $pogoda['wind']['deg'] <= 258.75) { $wind_direction = 'юго-западный, '; }
        elseif ($pogoda['wind']['deg'] > 258.75 && $pogoda['wind']['deg'] <= 281.25) { $wind_direction = 'западный, '; }
        elseif ($pogoda['wind']['deg'] > 281.25 && $pogoda['wind']['deg'] <= 348.75) { $wind_direction = 'северо-западный, '; }
        else { $wind_direction = ' '; }
        // Формирование ответа.
        $weather_text = 'Сейчас ' . $weather_type . '. Температура воздуха: ' . $temperature . '°C. Ветер ' . $wind_direction . sprintf("%u", $pogoda['wind']['speed']) . ' м/сек.';
        // Отправка ответа пользователю Telegram.
        sendMessage($chat_id, $weather_text);
        break;
    case '/news':
        sendChatAction($chat_id, 'typing');
        $news = json_decode(cURL($meduza_api), true);
        $news_string = "";
        if ($news) {
          foreach ($news['documents'] as $new) {
            $news_string .= $new['title'] . 'm'. $new['url'];
          }
          sendMessage($chat_id, $news_string);
        } else {
          sendMessage($chat_id, "Не могу получить новости =(");
        }
        break;
    case '/help':
        $help_string = "/weather - Погода в Краснодаре ". $emoji['weather']['clear'] . "\n";
        $help_string .= "/news - 10 последних новостей с meduza ". $emoji['newspaper']. "\n";
        $help_string .= "*слово на русском* - бот ищет требуемое на gramota.ru ". $emoji['pen']. "\n";
        sendMessage($chat_id, $help_string);
        break;
    case '/my_id':
        sendMessage($chat_id, $chat_id);
        break;
    case '/time':
        sendMessage($chat_id, date("H:i:s" ,time()));;
        break;
    case '/send_rita':
        sendMessage($chat_id, "Что писать?");
        file_put_contents(dirname(__FILE__) . '/to_rita.txt', 'ready');
        break;
    default:
        if (file_get_contents(dirname(__FILE__) . '/to_rita.txt') == 'ready'){
            sendMessage($rita_id, $message);
            file_put_contents(dirname(__FILE__) . '/to_rita.txt', '');
            sendMessage($chat_id, "Отправил.");
        }else {

            sendChatAction($chat_id, 'typing');
            $message = str_replace(' ', '+', $message);

            $html = cURL($gramota_api . $message);
            $text = str_replace("\n", '', $html);
            preg_match_all('%.*?id="help".*?lop.*?</h2>(.*?)</div>.*?h2.*?bts.*?</h2>(.*?)</div>.*?%im', $text, $mach);

            if (count($mach[0]) == 0) {
                preg_match_all('%.*?id="help".*?<h2>(.*?)</h2>.*?<h2>(.*?)</p>.*?%im', $text, $mach);
            }

            $text = trim(strip_tags($mach[1][0])) . "\n" . trim(strip_tags($mach[2][0]));
            $text = iconv('windows-1251', 'utf-8', $text);

            if ($rita_mod)
                $text .= "\nМур-мур " . $emoji['cat_smile'];

            sendMessage($chat_id, $text);
        }
        break;
}

/**
 * Функция отправки сообщения sendMessage().
 */
function sendMessage($chat_id, $message) {
    error_log($message);
    file_get_contents($GLOBALS['api'] . '/sendMessage?chat_id=' . $chat_id . '&text=' . iconv("UTF-8", "UTF-8//IGNORE", $message));
}
function sendChatAction($chat_id, $action){
    file_get_contents($GLOBALS['api'] . '/sendChatAction?chat_id=' . $chat_id . '&action=' . $action);
}

function cURL($url){
    $ch = curl_init();

    $headers = array();
    $headers[] = 'X-Apple-Tz: 0';
    $headers[] = 'X-Apple-Store-Front: 143444,12';
    $headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';
    $headers[] = 'Accept-Encoding: gzip, deflate';
    $headers[] = 'Accept-Language: en-US,en;q=0.5';
    $headers[] = 'Cache-Control: no-cache';
    $headers[] = 'Content-Type: application/x-www-form-urlencoded; charset=utf-8';
    $headers[] = 'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:28.0) Gecko/20100101 Firefox/28.0';
    $headers[] = 'X-MicrosoftAjax: Delta=true';

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_REFERER, 'http://google.com');
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_ENCODING, '');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $result = curl_exec($ch);
    //var_dump($result);
    curl_close($ch);
    if ($result){
        return $result;
    }else{
        return '';
    }
}

