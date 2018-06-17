<?php
class Response
{
    private $file;
    private $is_result_set = false;
    private $is_debug_set = false;

    public function getStatusCode()
    {
        return $this->code;
    }

    private function SetResult()
    {
        if($this->is_result_set==false)
        {
            $this->file-> addChild('result');
            $this->is_result_set = true;
        }
    }

    private function SetDebug()
    {
        if($this->is_debug_set==false)
        {
            $this->file->result-> addChild('debug');
            $this->is_debug_set = true;
        }
    }

    public function __construct()
    {
        $this->code = 0;
        $this->file = new SimpleXMLElement("<?xml version='1.0' encoding='utf-8'?><agsum></agsum>");
    }

    public function SetFoundText($text)
    {
        $this->SetResult();
        $this->file->result->addChild('yandex-found-docs-human', $text);
    }

    public function SetStatus($message, $code)
    {
        $current_node =  $this->file-> addChild('status');
        $current_node-> addChild('message', $message);
        $current_node-> addChild('code', $code);
    }

    public function SetText($url, $title, $articles)
    {
        $this->SetResult();
        $site_name = explode("/", $url)[2];
        $current_node = $this->file->result->addChild('site');
        $current_node-> addAttribute('name', $site_name);
        $current_node-> addChild('link', $url);
        if(strlen($title)<>0) 
        {
            $current_node-> addChild('title', $title);
        }
        $current_node-> addChild('content');
        foreach($articles as $article)
        {
            $p = $current_node->content-> addChild('paragraph', $article);
            $p-> addAttribute('relevance', 0);
        }
    }

    public function SetSummary($summary)
    {
        $this->SetResult();
        $currentNode = $this->file->result->addChild('summary');
        foreach($summary as $paragraph)
        {
            $paragraphText = "";
            foreach($paragraph as $text) $paragraphText.="$text ";
            $currentNode->addChild('paragraph', $paragraphText);
        }
    }

    public function SetQuery($query)
    {
        $this->SetResult();
        $this->file->result->addChild('query', $query);
    }

    public function SetUrls($urls)
    {
        $this->SetResult();
        $current_node = $this->file->result->addChild('sources');
        foreach($urls as $url)
        {
            $current_node->addChild("url", $url);
        }
    }

    public function SetKeywords($keywords)
    {
        $this->SetResult();
        $current_node = $this->file->result->addChild('tags');
        foreach($keywords as $keyword=>$relevance)
        {
            $current_node->addChild("tag", $keyword);
        }
    }

    public function SetExecutionTime($execTime)
    {
        $this->SetResult();
        $this->file->result->addChild('execution_time', $execTime);
    }

    public function DisplayTimes($times)
    {
        $this->SetDebug();
        $current_node = $this->file->result->debug->addChild('times');
        foreach($times as $text => $time)
        {
            $current_node->addChild($text, round($time, 2));
        }
    }

//    public function SetCaptcha($img_url, $key, $status)
//    {
//        $this->file-> addChild('captcha');
//        $this->file->captcha-> addChild('captcha-img-url', $img_url);
//        $this->file->captcha-> addChild('captcha-key', $key);
//        $this->file->captcha-> addChild('captcha-status', $status);
//    }

    public function asXML()
    {
        return $this->file->asXML();
    }

    public function LoadFromString($string)
    {
        $this->file = simplexml_load_string($string);
    }
}
