<?php
require_once  'LinkParser.php';
require_once './settings.php';
class WikipediaSearcher
{
    public static function SearchForQuery($query)
    {
        if(isset($GLOBALS['phraser']))
            $lang = $GLOBALS['phraser']->GetLanguage();
        else
            $lang = "ru";
        $url = self::BuildUrl($lang, $query);
        $file = self::GetResponse( $url);
        $url = self::ParseXml($file);
        $text = self::GetContent($url);
        return array("url" => $url, "content" => $text);
    }

    private static function GetResponse($url)
    {
        $file = file_get_contents($url);
        $xml = simplexml_load_string($file);
        return $xml;
    }

    private static function BuildUrl($lang, $query)
    {
        $langPrefix = LANGUAGES[$lang];
        $url = "https://$langPrefix.wikipedia.org/w/api.php";
		$url.="?action=opensearch&format=xml&search=".urlencode($query);
        return $url;
    }

    private static function ParseXml($file)
    {
        if(count($file->Section->Item)>0)
        {
            $result = $file->Section->Item[0]->Url;
        }
        else
        {
            throw new Exception($GLOBALS['phraser']->GetErrorText(803), 803);
        }
        return $result;
    }

    private static function GetContent($url)
    {
        $text = LinkParser::ParseUrlContent($url, $status);
        if($status) return $text;
        else throw new Exception($GLOBALS['phraser']->GetErrorText(803), 803);
    }
}