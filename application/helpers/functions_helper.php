<?php

function validateInput($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
function validateFormData($arr)
{
    if (!is_array($arr)) return null;
    foreach ($arr  as $key => $val) {
        $arr[$key] = validateInput($val);
    }
    return $arr;
}
function strRemoveExtraSpace($str)
{
    return implode(DEAL_CONFIG_SLUG_SEPARATOR, explode(',', str_replace(' ', '', $str)));
}
function setSessionValue($var, $value = '')
{
    $_SESSION[$var] = $value;
    return true;
}
function getSessionValue($var)
{
    return $_SESSION[$var] ?? null;
}
function getAcronym($string)
{
    if (empty($string)) return $string;
    preg_match_all('/\b\w/', $string, $matches);
    return implode('', $matches[0]);
}
function resetSession()
{
    unset($_SESSION);
}

function startSession()
{
    $CI = &get_instance();
    $CI->load->library('session');
}
function getTotalLoanAmount($loan)
{
    return $loan['approved'] + $loan['approved'] * $loan['service_charge'] + $loan['approved'] * $loan['interest_rate'];
}
function isInProduction()
{
    return !in_array(ENVIRONMENT, ['staging', 'development']) ? true : false;
}
function getPhoneNumberWithCountryCode($num, $code = '+88')
{
    $phoneNumberLength = 11;
    $len = strlen($num);
    if ($len == 11) {
        if ($num[0] !== '0') return false;
        else $validNo = $num;
    } else if ($len == 13) {
        $sub = substr($num, 0, 2);
        if ($sub != '88') return false;
        else $validNo = substr($num, -$phoneNumberLength, $phoneNumberLength);
    } else if ($len == 14) {
        if (substr($num, 0, 3) !== '+88') return false;
        else $validNo = substr($num, -$phoneNumberLength, $phoneNumberLength);
    } else {
        return false;
    }
    return $code . $validNo;
}
function checkRequestMethod($method = 'POST')
{
    if ($_SERVER['REQUEST_METHOD'] !== $method) {
        throwError(REQUEST_METHOD_NOT_VALID);
    }
}
// check if array keys are not empty
// indices are '|' separated
function arrayHasValues($arr = [], $indices = '')
{
    $keys = explode('|', $indices);
    foreach ($keys as $key)
        if (empty($arr[$key]))
            return false;
    return true;
}
function getRawInput()
{
    $handler = fopen('php://input', 'r');
    return json_decode(stream_get_contents($handler), true);
}
function checkUploadedImage($field, $maxSize = 5120, $allowed = '*')
{
    $response = ['error' => true, 'msg' => ''];
    if (!isset($_FILES[$field])) {
        $response['msg'] = "{$field} missing.";
        return $response;
    }
    $image = $_FILES[$field];
    if ($image["size"] == 0) {
        $response['msg'] = "{$field} - Empty file.";
        return $response;
    }
    // if (!getimagesize($image["tmp_name"])) {
    //     $response['msg'] = "{$field} - File is not an image.";
    //     return $response;
    // }
    if ($image["size"] > $maxSize * 1024) {
        $response['msg'] = "{$field} - Max allowed size {$maxSize} KB";
        return $response;
    }

    $target_file = basename($image["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    if ($allowed !== '*') {
        $types = explode('|', $allowed);
        if (!in_array($imageFileType, $types)) {
            $response['msg'] = "Allowed file type: {$allowed}";
            return $response;
        }
    }
    $response = ['error' => false, 'msg' => 'Valid'];
    return $response;
}
function removeComma($val)
{
    return str_replace(',', '', $val);
}
function getHeader($name)
{
    $header = null;
    if (isset($_SERVER[$name])) {
        $header = trim($_SERVER[$name]);
    } else if (isset($_SERVER['HTTP_' . strtoupper($name)])) { //Nginx or fast CGI
        $header = trim($_SERVER['HTTP_' . strtoupper($name)]);
    } elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
        $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
        if (isset($requestHeaders[$name])) {
            $header = trim($requestHeaders[$name]);
        }
    }
    return $header;
}
function generateRandomString($length = 6)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
function removeCountryCode($phone)
{
    $code = '+88';
    return str_replace($code, '', $phone);
}
function getLoanStatusFromText($txt)
{
    if ($txt === 'pending') return LOAN_PENDING;
    else if ($txt === 'accepted') return LOAN_ACCEPTED;
    else if ($txt === 'approved') return LOAN_APPROVED;
    else if ($txt === 'disbursed') return LOAN_DISBURSED;
    else if ($txt === 'paying') return LOAN_PAYING;
    else if ($txt === 'returned') return LOAN_RETURNED;
    else if ($txt === 'declined') return LOAN_DECLINED;
    else null;
}
function getRepayDate($count, $date = null, $format = 'M d, Y')
{
    $prevTime = strtotime($date);
    $month = intval(date('m', $prevTime)) + intval($count);
    $year = date('Y', $prevTime);
    $day = date('j', $prevTime);
    if ($month > 12) {
        $year++;
        // starting from Jan
        $month = $month - 12;
    }
    $numDaysinCurrentMonth = date('t', strtotime("{$year}-{$month}-01"));
    if ($day > $numDaysinCurrentMonth) $day = $numDaysinCurrentMonth;
    $newDate = $year . '-' . $month . '-' . $day;
    return date($format, strtotime($newDate));
}

function lastQuery()
{
    $CI = &get_instance();
    return $CI->db->last_query();
}
function getLanguageHeader()
{
    $headers = null;
    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $headers = trim($_SERVER["HTTP_ACCEPT_LANGUAGE"]);
    } else if (isset($_SERVER['HTTP_HTTP_ACCEPT_LANGUAGE'])) { //Nginx or fast CGI
        $headers = trim($_SERVER["HTTP_HTTP_ACCEPT_LANGUAGE"]);
    } elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for HTTP_ACCEPT_LANGUAGE)
        $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
        if (isset($requestHeaders['HTTP_ACCEPT_LANGUAGE'])) {
            $headers = trim($requestHeaders['HTTP_ACCEPT_LANGUAGE']);
        }
    }
    if ($headers != 'en' && $headers != 'bn') $headers = 'en';
    return $headers;
}
function sendSMS($phone, $msg)
{
    $curl = curl_init();
    $msg = urlencode($msg);
    curl_setopt_array($curl, array(
        CURLOPT_URL => "http://api.boom-cast.com/boomcast/WebFramework/boomCastWebService/externalApiSendTextMessage.php?masking=NOMASK&userName=DanaFintech&password=16c646fcc183552a845a10c3d5dde855&MsgType=TEXT&receiver={$phone}&message={$msg}",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return $response;
}
function pp($arr)
{
    echo '<pre>';
    print_r($arr);
    echo '</pre>';
}
function ppd($arr)
{
    pp($arr);
    die();
}
function content_url()
{
    return config_item('content_url');
}
function uploadPath()
{
    return config_item('upload_path');
}
function get_uuid()
{
    $bytes = random_bytes(8);
    $bytes[4] = chr((ord($bytes[4]) & 0x0f) | 0x40);
    $bytes[6] = chr((ord($bytes[6]) & 0x3f) | 0x80);
    $uuid = time() . sprintf('_%s', bin2hex($bytes));
    return $uuid;
}
function upload_base64($data, $path)
{
    if ($data == '' || $data == null) return '';
    if (count(explode(';', $data)) != 2) {
        return '';
    }
    list($type, $data) = explode(';', $data);
    list(, $data)      = explode(',', $data);
    $data = base64_decode($data);
    // check proper extension
    $types = explode('/', $type);
    if (count($types) != 2) {
        return '';
    }
    $ext = $types[1];
    $fileName = get_uuid() . '.' . $ext;
    if (file_put_contents($path . $fileName, $data) === FALSE) {
        return '';
    } else {
        return $fileName;
    }
}
function getAgeFromDateOfBirth($dob)
{
    $from = new DateTime($dob);
    $to   = new DateTime('today');
    return $from->diff($to)->y;
}
function en2bn($number)
{

    $bn = array("১", "২", "৩", "৪", "৫", "৬", "৭", "৮", "৯", "০");
    $en = array("1", "2", "3", "4", "5", "6", "7", "8", "9", "0");
    return str_replace($en, $bn, $number);
}
function bn2en($number)
{
    $bn = array("১", "২", "৩", "৪", "৫", "৬", "৭", "৮", "৯", "০");
    $en = array("1", "2", "3", "4", "5", "6", "7", "8", "9", "0");
    return str_replace($bn, $en, $number);
}
function formatDate($date = '', $formate = 'M d, Y')
{
    if (!validateDate($date)) return '';
    $date = $date == '' ? date('Y-m-d') : $date;
    return date($formate, strtotime($date));
}
function validateDate($date, $format = 'Y-m-d')
{
    $d = DateTime::createFromFormat($format, $date);
    // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
    return $d && $d->format($format) === $date;
}
function repaymentDate($applied, $tenor, $format = 'Y-m-d')
{
    return date($format, strtotime("+{$tenor} day", strtotime($applied)));
}
function hasData($var)
{
    return $var === '' || $var === null || $var == 'null' ? 'No' : 'Yes';
}
function loanEmi($p, $n, $r)
{
    if ($r == 0) return round($p / $n);
    // EMI = P R I / I -1
    // I = (1 + R) ^ n
    // R = r / (12 * 100)
    $R = $r / 12;
    $I = pow((1 + $R), $n);
    return round($p * $R * $I / ($I - 1));
}
function calculateDownPayment($amount, $charge, $downpayment)
{
    if ($downpayment['method'] == CHARGE_INCLUDE) {
        return calculateChargeWithPercentageOrFlat($amount + $charge, $downpayment['rate']);
    } else {
        return calculateChargeWithPercentageOrFlat($amount, $downpayment['rate']);
    }
}
function calculateChargeWithPercentageOrFlat($amount, $charge)
{
    if (strpos($charge, '%') !== false) {
        $pct = floatval($charge);
        return round(($amount * $pct) / 100);
    }
    return intval($charge);
}
function calculateSimpleEmi($amount, $tenor, $inr)
{
    if (strpos($inr, '%') !== false) {
        $r = intval($inr) / 100;
        $n = $tenor / 12;
        return round(($amount + $amount * $r * $n) / $tenor);
    }
    return round($amount / $tenor);
}
function calculateCompoundEmi($amount, $tenor, $inr)
{
    if (strpos($inr, '%') !== false) {
        $r = intval($inr) / 100;
        return loanEmi($amount, $tenor, $r);
    }
    return round(($amount / $tenor));
}
function calculateSimpleInterest($amount, $tenor, $inr)
{
    if (strpos($inr, '%') !== false) {
        $r = intval($inr) / 100;
        $n = $tenor / 12;
        return round($amount * $r * $n);
    }
    return $inr;
}
function calculateCompoundInterest($amount, $tenor, $inr)
{
    if (strpos($inr, '%') !== false) {
        $r = intval($inr) / 100;
        $emi = loanEmi($amount, $tenor, $r);
        return $emi * $tenor - $amount;
    }
    return $inr;
}
function removeInvalidIndices($arr, $validIndices = [])
{
    foreach ($arr as $key => $value) {
        if (!in_array($key, $validIndices))
            unset($arr[$key]);
    }
    return $arr;
}

function removeArrayFields($arr, $indices = ['id', 'created_at', 'updated_at'])
{
    foreach ($indices as $i) {
        $pos = array_search($i, $arr);
        unset($arr[$pos]);
    }
    return $arr;
}
// 1. remove [xyz] keep others 
// 2. keep [xyz] remove others

function filterArrayFields($arr, $fields = ['id', 'created_at', 'updated_at'], $keepFields = false)
{
    return $keepFields ? array_intersect_key($arr, array_flip($fields)) : array_diff_key($arr, array_flip($fields));
}
function calculateEmiWithPercentageOrFlat($amount, $tenor, $inr)
{
    if (strpos($inr, '%') !== false) {
        $r = intval($inr) / 100;
        return loanEmi($amount, $tenor, $r);
    }
    return round($amount / $tenor);
}
function csn($num)
{
    if (strlen($num) < 4) return $num;
    $decimal_flag = false;
    if (strpos($num, '.')) {
        $tmp = explode('.', $num);
        $num = $tmp[0];
        $decimal = $tmp[1];
        $decimal_flag = true;
    }
    $negative = false;
    if (intval($num) < 0) {
        $negative = true; // negative
        $num *= -1;
    }
    $num = (string)$num;
    $num = strrev($num);
    $csn = '';
    for ($i = 0; $i < strlen($num); $i++) {
        if ($i == 3) {
            $csn .= ',';

            $csn .= $num[$i];
        } else if ($i > 3 && ($i + 1) % 2 == 0) {
            $csn .= ',';
            $csn .= $num[$i];
        } else {
            $csn .= $num[$i];
        }
    }
    $csn = strrev($csn);
    if ($decimal_flag) $csn = $csn . '.' . $decimal;
    if ($negative) $csn = '-' . $csn;
    return $csn;
}

function numberToOrdinal($num)
{
    $ordinalSuffixList = ['th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th'];
    return $num . ($num % 100 >= 11 && $num % 100 <= 13 ?  'th' : $ordinalSuffixList[$num % 10]);
}
function isRepayable($status)
{
    return in_array($status, [LOAN_DISBURSED, LOAN_PAYING]);
}
