<?php
class DBWorker
{
    private static $mysqli;
    public static function GetMysqliObject()
    {
        if(self::$mysqli==null)
        {
            self::$mysqli = new mysqli(DB_HOST, DB_LOGIN, DB_PASSWORD, DB_NAME);
            if(self::$mysqli->connect_errno)
            {
                throw new Exception("Ошибка соединения с БД", 701);
            }
        }
        return self::$mysqli;
    }

    public static function Delete($from, $what="", $condition=null)
    {
        $conditioning = false;
        if($condition!=null)
        {
            $conditioning = true;
            if (is_array($condition))
            {
                $type = current(array_keys($condition));
                $value = $condition[$type];
                $condField = " WHERE " . $value[0] . '?';
                $prepareValues[] = $value[1];
                $prepareTypes[] = $type;
                foreach (array_slice($condition, 1) as $type => $value)
                {
                    $condField.=" AND " . $value[0] . '?';
                    $prepareValues[] = $value[1];
                    $prepareTypes[] = $type;
                }
            }
            else
            {
                throw new Exception("Неправильно сформирован запрос", 702);
            }
        }

        $mysqli = self::GetMysqliObject();
        $query = "DELETE $what FROM $from";
        if($conditioning)
            $query.=$condField;
        $queryWorker = $mysqli->prepare("$query;");
        if($conditioning) {
            for($i=0;$i<count($prepareValues);$i++)
            {
                $queryWorker->bind_param($prepareTypes[$i], $prepareValues[$i]);
            }
        }
        $queryWorker->execute();
    }
}