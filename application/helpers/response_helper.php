<?php defined('BASEPATH') or exit('No direct script access allowed');
function throwError($constant, $msg = null)
{
    $message = $msg ?? $constant['msg'];
    setStatuseader($constant['status']);
    $errorMsg = [
        'error' => true, 'code' => $constant['code'], 'message' => $message, 'data' => null
    ];
    sendResponse($errorMsg);
}
function returnResponse($data = null, $msg = 'Ok', $code = 200)
{
    $response = ['error' => false, 'code' => $code];
    if (gettype($data) === 'string') {
        $response["message"] = $data;
        $response["data"] = null;
    } else {
        $response["message"] = $msg;
        $response["data"] = $data;
    }

    sendResponse($response);
}
function sendResponse($data, $locale = 'en')
{
    header("Content-type: application/json");
    header("Content-Language: {$locale}");
    $response = json_encode($data);
    setSessionValue('tmp_response', $response);
    echo $response;
    exit;
}
function setHeader($header)
{
    header($header);
}
function setStatuseader($code = 200, $text = '')
{
    if (empty($text)) {
        is_int($code) or $code = (int) $code;
        $stati = array(
            100    => 'Continue',
            101    => 'Switching Protocols',

            200    => 'OK',
            201    => 'Created',
            202    => 'Accepted',
            203    => 'Non-Authoritative Information',
            204    => 'No Content',
            205    => 'Reset Content',
            206    => 'Partial Content',

            300    => 'Multiple Choices',
            301    => 'Moved Permanently',
            302    => 'Found',
            303    => 'See Other',
            304    => 'Not Modified',
            305    => 'Use Proxy',
            307    => 'Temporary Redirect',

            400    => 'Bad Request',
            401    => 'Unauthorized',
            402    => 'Payment Required',
            403    => 'Forbidden',
            404    => 'Not Found',
            405    => 'Method Not Allowed',
            406    => 'Not Acceptable',
            407    => 'Proxy Authentication Required',
            408    => 'Request Timeout',
            409    => 'Conflict',
            410    => 'Gone',
            411    => 'Length Required',
            412    => 'Precondition Failed',
            413    => 'Request Entity Too Large',
            414    => 'Request-URI Too Long',
            415    => 'Unsupported Media Type',
            416    => 'Requested Range Not Satisfiable',
            417    => 'Expectation Failed',
            422    => 'Unprocessable Entity',
            426    => 'Upgrade Required',
            428    => 'Precondition Required',
            429    => 'Too Many Requests',
            431    => 'Request Header Fields Too Large',

            500    => 'Internal Server Error',
            501    => 'Not Implemented',
            502    => 'Bad Gateway',
            503    => 'Service Unavailable',
            504    => 'Gateway Timeout',
            505    => 'HTTP Version Not Supported',
            511    => 'Network Authentication Required',
        );

        if (isset($stati[$code])) {
            $text = $stati[$code];
        } else {
            $code = 500;
            $text = 'No status text available.';
        }
    }

    if (strpos(PHP_SAPI, 'cgi') === 0) {
        header('Status: ' . $code . ' ' . $text, TRUE);
        return;
    }

    $server_protocol = (isset($_SERVER['SERVER_PROTOCOL']) && in_array($_SERVER['SERVER_PROTOCOL'], array('HTTP/1.0', 'HTTP/1.1', 'HTTP/2'), TRUE))
        ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';
    header($server_protocol . ' ' . $code . ' ' . $text, TRUE, $code);
}
