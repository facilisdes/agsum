<?php
class XmlParser
{
    public static function ParseXml($xmlFile)
    {
        $result = array();
        //found-docs-human
        foreach($xmlFile->response->results->grouping->group as $group)
        {
            $isGood = true;
            $url = $group->doc->url;
            foreach(SEARCH_SITES_TO_IGNORE as $siteToIgnore)
            {
                //if(count($result)>MAX_DOCUMENTS_COUNT) break;
                if(strpos($url, $siteToIgnore)!=false)
                {
                    $isGood = false;
                    break;
                }
            }
            if($isGood==true) array_push($result, $url);
        }
        $yandexText = $xmlFile->response->results->grouping->{'found-docs-human'};
        return array($result, $yandexText);
    }
}