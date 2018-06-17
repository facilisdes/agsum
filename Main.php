<?php
require_once 'Aggregation/AggregationHandler.php';
require_once 'Summarization/SummarizationHandler.php';
require_once 'Response/ResponseHandler.php';
require_once 'DB/CachingWorker.php';
require_once 'Logger.php';

class MainHandler
{
    private $cacher, $response, $query, $settings;
    public function Search($settings)
    {
        try
        {
            $this->Initialize($settings);
            $cacheStatus = $this->cacher->FindCache($this->query);
            if ($cacheStatus == true) {
                $content = $this->cacher->GetCurrentCache();
            } else {
                $this->BuildSummary();
                $content = $this->response->ToString();
                if ($this->response->getStatusCode() == 1000) {
                    CachingWorker::Cache($this->query, $content);
                }
            }
        }
        catch (Exception $e)
        {
            $this->response->SetStatus($e->getMessage(), $e->getCode());
            Logger::LogError($e->getMessage());
            $content = $this->response->ToString();
        }
        return $content;
    }

    private function Initialize($settings)
    {
        $this->response = new ResponseHandler();
        $this->cacher = new CachingWorker();
        $this->settings = $settings;
        $this->query =mb_strtolower($settings['query']);
        Logger::LogAction('searching', $this->query);
    }

    private function BuildSummary()
    {
        $times = array();
        try
        {
            $a = microtime(true);/*timer*/
            $searchDataAr = AggregationHandler::Aggregate($this->query, $times);
            $searchData = $searchDataAr[0];
            $this->response->SetFoundText($searchDataAr[1]);
            $b = microtime(true);/*timer*/
            $times["aggregation"] = $b - $a;
            $resultData = SummarizationHandler::Summarize($this->query, $searchData, $times);
            $c = microtime(true);/*timer*/
            $times["summarization"] = $c - $b;
        }
        catch(Exception $e)
        {
            $this->response->SetStatus($e->getMessage(), $e->getCode());
            Logger::LogError($e->getMessage());
            return;
        }
        $this->response->GenerateResponse($resultData, $times, $this->settings);
    }
}