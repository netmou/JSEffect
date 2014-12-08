<?php
IN_MY_PHP||die(0);
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
    public static function formatTrace($e){
        $traceLog=null;
        $traces = $e->getTrace();
        $xh=0;
        foreach ($traces as $k => $trace) {
            if (empty($trace['file'])) {
                try {
                    if (isset($trace['class'])) {
                        $reflection = new ReflectionMethod($trace['class'], $trace['function']);
                    } else {
                        $reflection = new ReflectionFunction($trace['function']);
                    }
                    $trace['file'] = $reflection->getFileName();
                    $trace['line'] = $reflection->getStartLine();
                } catch (Exception $e) {
                    continue;
                }
            }
            $traceLog.='#'. (++$xh).' ';
            if(isset($trace['class'])){
                $traceLog.=$trace['class'].$trace['type'].$trace['function'];
            }else{
                $traceLog.=$trace['function'];
            }
            $file = str_replace(RTPATH, '', $trace['file']);
            $traceLog.='() On line ' . $trace['line'] . ' In file "' . $file . '"';
            if(isset($trace['args']) && is_array($trace['args'])){
                $traceLog.=' With the args: "'.implode(',',$trace['args']).'"';
            }
            $traceLog.="\n";
        }
        return $traceLog;
    }

    public static function exceptDesc(Exception $e) {
        $desc = "Your program has a problem\n";
        $desc = $desc . "ExceptionType:" . get_class($e) . "\n";
        $desc = $desc . "ErrorLevel:" . self::$errorLevel . "\n";
        $desc = $desc . "ErrorMessage:" . $e->getMessage() . "\n";
        $desc = $desc . "ErrorFile:" . str_replace(RTPATH, '', $e->getFile()) . "\n";
        $desc = $desc . "ErrorLine:" . $e->getLine() . "\n";
        $desc = $desc . "ErrorTrace:\n" . self::formatTrace($e) . "\n";
        print nl2br(htmlentities($desc));
        exit(0);
    }
}
set_exception_handler('Guard::exceptDesc');
set_error_handler('Guard::errorGuard', error_reporting());
?>
