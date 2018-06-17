<?php
require_once 'General.php';
class CachingWorker
{
    private $mysqli;
    private $xmlString;

    public function GetCurrentCache()
    {
        return $this->xmlString;
    }

    public function __construct()
    {
        $this->mysqli = DBWorker::GetMysqliObject();
    }

    public static function ClearCache()
    {
        DBWorker::Delete('xml_cache');
    }

    public static function Cache($query, $string)
    {
        $mysqli = DBWorker::GetMysqliObject();
        $SqlQuery = 'INSERT INTO xml_cache(query, xml) values(?, ?)';
        $queryWorker = $mysqli->prepare($SqlQuery);
        $queryWorker->bind_param('ss', $query, $string);
        $queryWorker->execute();
        $mysqli->close();
    }

    public function FindCache($query)
    {
        $SqlQuery = 'SELECT xml, creation_date FROM xml_cache WHERE query= ? ';
        $queryWorker = $this->mysqli->prepare($SqlQuery);
        $queryWorker->bind_param('s', $query);
        $queryWorker->execute();
        $queryWorker->bind_result($xml, $date);
        if($queryWorker->fetch())
        {
            $dateObject = DateTime::createFromFormat('Y-m-d H:i:s', $date);
            $now=new DateTime();
            $shift = 2;
            $now->add(new DateInterval('PT' . $shift . 'H'));
            $dd = $now->diff($dateObject, true)->format('%a');
            if($dd>CACHER_LIFESPAN_DAYS)
            {
                //cache is too old, rebuild
                self::DeleteCacheForQuery($query);
                $this->xmlString = null;
                return false;
            }
            else
            {
                //use that cache
                $this->xmlString = $xml;
                return true;
            }
        }
        else
        {
            //no cache at all
            $this->xmlString = null;
            return false;
        }
    }

    public static function DeleteCacheForQuery($query)
    {
        DBWorker::Delete('xml_cache', "", array('s' => array('query=', $query)));
    }
}
