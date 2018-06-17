<?php
class LinkParser
{
    private static $ua = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13';
    private static $cookie = 'NID=67=pdjIQN5CUKVn0bRgAlqitBk7WHVivLsbLcr7QOWMn35Pq03N1WMy6kxYBPORtaQUPQrfMK4Yo0vVz8tH97ejX3q7P2lNuPjTOhwqaI2bXCgPGSDKkdFoiYIqXubR0cTJ48hIAaKQqiQi_lpoe6edhMglvOO9ynw; PREF=ID=52aa671013493765:U=0cfb5c96530d04e3:FF=0:LD=en:TM=1370266105:LM=1370341612:GM=1:S=Kcc6KUnZwWfy3cOl; OTZ=1800625_34_34__34_; S=talkgadget=38GaRzFbruDPtFjrghEtRw; SID=DQAAALoAAADHyIbtG3J_u2hwNi4N6UQWgXlwOAQL58VRB_0xQYbDiL2HA5zvefboor5YVmHc8Zt5lcA0LCd2Riv4WsW53ZbNCv8Qu_THhIvtRgdEZfgk26LrKmObye1wU62jESQoNdbapFAfEH_IGHSIA0ZKsZrHiWLGVpujKyUvHHGsZc_XZm4Z4tb2bbYWWYAv02mw2njnf4jiKP2QTxnlnKFK77UvWn4FFcahe-XTk8Jlqblu66AlkTGMZpU0BDlYMValdnU; HSID=A6VT_ZJ0ZSm8NTdFf; SSID=A9_PWUXbZLazoEskE; APISID=RSS_BK5QSEmzBxlS/ApSt2fMy1g36vrYvk; SAPISID=ZIMOP9lJ_E8SLdkL/A32W20hPpwgd5Kg1J';
        
    public static function ParseUrlContent($url, &$status)
    {
        $status = false;
        $html = self::GetHtml($url);
        try
        {
            $dom = self::BuildDomDoc($html);
        }
        catch(Exception $ex)
        {
            Logger::LogError($ex->getMessage(), $ex->getCode());
            return array();
        }
        try
        {
            $text = self::ParseContent($dom);
        }
        catch(Exception $e)
        {
            if($e->getCode()==801)
                return array();
            else throw $e;
        }
        $result = $text;
        //if(count($text)>3)
            $status = true;
        return $result;
    }

    private static function BuildDomDoc($html)
    {
        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        @$dom->loadHTML($html);
        libxml_use_internal_errors(false);
        return $dom;
    }

    private static function GetHtml($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL,             $url);
        curl_setopt($curl,  CURLOPT_USERAGENT,      self::$ua);
        curl_setopt($curl,  CURLOPT_COOKIE,         self::$cookie);
        curl_setopt($curl,  CURLOPT_AUTOREFERER,    TRUE);
        curl_setopt($curl,  CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl,  CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($curl,  CURLOPT_MAXREDIRS,      20);
        curl_setopt($curl,  CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl,  CURLOPT_TIMEOUT,        LINK_PARSER_TIMEOUT);
        $html = curl_exec($curl);

        if ($html === FALSE)
        {

            $err = curl_errno($curl);
            curl_close($curl);
            throw new Exception($GLOBALS['phraser']->GetErrorText(802), 802);
        }
        curl_close($curl);
        return $html;
    }

    private static function ParseContent($dom)
    {
        $result = array();
        $xpath = new DOMXPath($dom);
        $nodes = $xpath->query('//div/p | //article//p | //table//tr//td//p');
        foreach($nodes as $node)
        {
            $text = $node->textContent;
            $text = preg_replace("/\[.+\]/", "", $text);
            if(strlen($text)>0) 
            {
                $match = preg_match('/(.*)[\.?!]( *)$/', $text);
                if($match<>0)
                {
                    if(!self::IsInRightEncoding($text))
                    {
                        throw new Exception($GLOBALS['phraser']->GetErrorText(801), 801);
                    }
                    $result[] = $text; 
                }
            }     
        }
        return $result;
    }

    private static function IsInRightEncoding($text)
    {
         $match2 = preg_match('/Ð/', $text);
         if($match2<>0)
         {
             return false;
         }
         return true;
    }
}