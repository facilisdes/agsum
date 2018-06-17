<?php
require_once 'DB/General.php';
require_once 'settings.php';
$feedback = $_POST['feedback'];
$mysqli = GeneralDBWorker::GetMysqliObject();
$SqlQuery = 'INSERT INTO feedback(result1, result2, query) values(?, ?, ?)';
$queryWorker = $mysqli->prepare($SqlQuery);
$queryWorker->bind_param('iis', $feedback[1], $feedback[2], $feedback['query']);
$queryWorker->execute();
$mysqli->close();