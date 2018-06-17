<?php
class Logger
{    
    public static function LogAction($action, $message)
    {
        $datetime =  date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'];
        $string =  "[{$datetime}] {$action}: {$message}, addr:{$ip}\r\n";
        $fh = fopen(LOG_REGULAR_LOGFILE_PATH, 'a');
        fwrite($fh, $string);
        fclose($fh);        
    }

    public static function LogError($message, $code=null)
    {
        $datetime =  date('Y-m-d H:i:s');
        $string =  "[$datetime] ERROR: $message" . ($code==null? ", code: $code" :'') . "\r\n";
        $fh = fopen(LOG_ERROR_LOGFILE_PATH, 'a');
        fwrite($fh, $string);
        fclose($fh);        
    }
}
