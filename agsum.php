<?php
require_once "Main.php";
require_once 'settings.php';
require_once "Locals/PhrasePicker.php";

class agsum
{
    public static function Search($parameters)
    {
        self::Initialize($parameters);
        $handler = new MainHandler();
        $response = $handler->Search($parameters);
        self::DisplayResult($response);
    }

    private static function DisplayResult($response)
    {
        Header('Content-type: text/xml');
        echo $response;
    }

    private static function Initialize($parameters)
    {
        if(isset($parameters['lang']))
            $lang = $parameters['lang'];
        else
            $lang = "ru";
        $language = LANGUAGES["ru"];
        $phraser = new PhrasePicker($language);
        $GLOBALS['phraser'] = $phraser;
    }

    public static function WipeCache()
    {
        CachingWorker::ClearCache();
    }

    public static function WipeCacheForAQuery($query)
    {
        CachingWorker::DeleteCacheForQuery($query);
    }

}
error_reporting(E_ALL);
ini_set('display_errors','Off');

if(isset($_REQUEST['wipe_cache']))
{
   if($_REQUEST['wipe_cache']==1 | $_REQUEST['wipe_cache'] = 'true')
   {
       agsum::WipeCache();
   }
}
if(isset($_REQUEST['wipe_cache_for']))
{
   agsum::WipeCacheForAQuery($_REQUEST['wipe_cache_for']);
}
if(isset($_GET['query']))
{
    agsum::Search($_GET);
}
