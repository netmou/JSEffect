<?php
!defined(IN_MY_PHP) && die(0);
/**
 * 异常捕获及错误处理类
 * @author netmou <leiyanfo@sina.com>
 */
class Guard extends Exception {

    private static $errorLevel = null;

    public static function errorGuard($errno, $msg, $file, $line) {
        switch ($errno) {
            case E_ERROR:
            case E_USER_ERROR:
                self::$errorLevel = 'Fatal Error';
                break;
            case E_WARNING:
            case E_USER_WARNING:
                self::$errorLevel = 'Warning Error';
                break;
            case E_NOTICE:
            case E_USER_NOTICE:
                self::$errorLevel = 'Notice Error';
                break;
            default:
                self::$errorLevel = 'Unknown Error';
        }
        throw new ErrorException($msg, 0, $errno, $file, $line);
    }

    public static function exceptDesc(\Exception $e) {
        $desc = "you program has a problem\n";
        $desc = $desc . "ExceptionType:" . get_class($e) . "\n";
        $desc = $desc . "ErrorLevel:" . self::$errorLevel . "\n";
        $desc = $desc . "ErrorMessage:" . $e->getMessage() . "\n";
        $desc = $desc . "ErrorFile:" . $e->getFile() . "\n";
        $desc = $desc . "ErrorLine:" . $e->getLine() . "\n";
        $desc = $desc . "ErrorTrace:\n" . $e->getTraceAsString() . "\n";
        print nl2br(htmlentities($desc));
        exit(0);
    }
}
set_exception_handler('Guard::exceptDesc');
set_error_handler('Guard::errorGuard', error_reporting());
?>
