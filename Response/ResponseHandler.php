<?php
require_once 'Response.php';
class ResponseHandler
{
    private $response;
    private $code;

    public function getStatusCode()
    {
        return $this->code;
    }

    public function __construct()
    {
        $this->response = new response();
    }

    public function GenerateResponse($search_data, $times, $settings)
    {
        $valid_search_count = count($search_data['urls']);
        if($valid_search_count==0)
        {
            $this->SetStatus('По запросу не нашлось ни одной страницы, из которой удалось бы получить информацию.', 1001);
            return false;
        }
        if($this->response->GetStatusCode()==null)
        {
            $this->SetStatus('OK', 1000);
        }
        $this->response->SetSummary($search_data['summary']);
        $this->response->SetKeywords($search_data['keywords']);
        $this->response->SetUrls($search_data['urls']);
        foreach($settings as $parameter => $value)
        {
            switch($parameter)
            {
                case 'query': $this->response->SetQuery($value); break;
                case 'debug_disabled': if($value='y') $this->SetDebugInfo($times); break;
            }
        }
        $execTime = $times['aggregation'] + $times['summarization'];
        $this->response->SetExecutionTime($execTime);
        return true;
    }

    private function SetDebugInfo($times)
    {
        $this->response->DisplayTimes($times);
    }

    public function SetFoundText($text)
    {
        $this->response->SetFoundText($text);
    }

    public function SetStatus($message, $code)
    {
        $this->response->SetStatus($message, $code);
        $this->code = $code;
    }

    public function ToString()
    {
        return $this->response->asXML();
    }

    public function LoadFromString($string)
    {
        $this->response->LoadFromString($string);
    }
}