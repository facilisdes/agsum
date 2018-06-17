<?php
include_once 'settings.php';
include_once 'DB/General.php';
$mysqli = GeneralDBWorker::GetMysqliObject();

echo "<html><head></head><body><h1>Результаты опроса:</h1>";
echo "<table><tr><td>Запрос</td><td>Оценка 1</td><td>Оценка 2</td><td>Оценка 1 в %</td><td>Оценка 2 в %</td></tr>";

    $SqlQuery = 'SELECT query, result1 as Mark1, result2 as Mark2, 100*(result1-1)/4 as Mark1p, 100*(result2-1)/4 as Mark2p from feedback;';
    $queryWorker = $mysqli->prepare($SqlQuery);
    $queryWorker->execute();
    $queryWorker->bind_result($query, $mark1, $mark2, $mark1p, $mark2p);
    while($queryWorker->fetch())
    {
        $mark1p = round($mark1p, 2);
        $mark2p = round($mark2p, 2);
        echo "<tr><td>$query</td><td>$mark1</td><td>$mark2</td><td>$mark1p</td><td>$mark2p</td></tr>";
    }


    $SqlQuery = 'SELECT count(id) as CountOfVotes, avg(result1), avg(result2), 100*avg(result1-1)/4 as Mark1, 100*avg(result2-1)/4 as Mark2 from feedback;';
    $queryWorker = $mysqli->prepare($SqlQuery);
    $queryWorker->execute();
    $queryWorker->bind_result($count, $mark1, $mark2, $mark1p, $mark2p);
    if($queryWorker->fetch())
    {
        $mark1 = round($mark1, 2);
        $mark2 = round($mark2, 2);
        $mark1p = round($mark1p, 2);
        $mark2p = round($mark2p, 2);
        echo "<tr><td>Всего: $count</td><td>$mark1</td><td>$mark2</td><td>$mark1p</td><td>$mark2p</td></tr>";
    }


echo "</table></body></html>";