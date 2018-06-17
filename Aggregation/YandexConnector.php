<?php
class YandexConnector
{
    public static function GetXml($query)
    {
        $link = self::BuildSearchLink($query);
        $xmlFile = self::ReadXmlFile($link);
        self::CheckXmlForErrors($xmlFile);
        return $xmlFile;
    }

/*    private static function ReportCaptcha($key, $rep)
    {
        $xml = YandexConnector::GetCaptchaXml($key, $rep);
        return $xml;
    }

    private static function GetCaptchaXml($key, $rep)
    {
        $link = YANDEX_CAPTCHA_URL . '?' . 
            YANDEX_CAPTCHA_KEY_PARAM_NAME . '=' . $key . '&' . 
            YANDEX_CAPTCHA_REP_PARAM_NAME . '=' . $rep; 
        $output = file_get_contents($link);
        $result = simplexml_load_string($output);
        return $result;
    }*/

    private static function BuildSearchLink($query)
    {
        $language = $GLOBALS['phraser']->GetLanguage();

        $fixed_query = urlencode($query);
        $result = SEARCH_URL . '?'. 
        SEARCH_USER_PARAM_NAME . '=' . SEARCH_USER . '&' . 
        SEARCH_KEY_PARAM_NAME . '=' . SEARCH_KEY . '&' .
        SEARCH_LANGUAGE_PARAM_NAME . '=' . $GLOBALS['phraser']->GetLanguage() . '&' .
        SEARCH_SORTBY_PARAM_NAME . '=' . SEARCH_SORTBY . '&' .
        SEARCH_FILTER_PARAM_NAME . '=' . SEARCH_FILTER . '&' . 
        SEARCH_GROUPBY_PARAM_NAME . '=' . SEARCH_GROUPBY . '&' .
		SEARCH_QUERY_PARAM_NAME . '=' . $fixed_query;
        return $result;
    }

    private static function ReadXmlFile($link)
    {
        $curl = curl_init();
        curl_setopt( $curl, CURLOPT_URL, $link);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        $output = curl_exec($curl);
        curl_close($curl);
		
        $result = simplexml_load_string($output);
        return $result;
    }

    
    private static function CheckXmlForErrors($xmlFile)
    {
        $errorCode = $xmlFile->response->error['code'];
        $errorText = $xmlFile->response->error;
        if($errorCode!=null) 
        {
            /*
            $response->set_status($errorCode, $errorText);
            if($errorCode==100)
            {
                $img_url = $xmlFile->captcha-img-url;
                $key = $xmlFile->captcha-key;
                $status = $xmlFile->captcha-status;
                $responce->set_captcha($img_url, $key, $status);
            }
            */
            throw new Exception("Yandex XML: $errorText", (int)$errorCode);
        }
    }
}