<?php

$action = $_POST['action'];

function start($data) {

    $workPhrase = trim(file_get_contents('objective.txt'));

    return array(
        'workPhrase' => $workPhrase,
        'populationSize' => 1000,
        'sharedPopulationSize' => 100,
        'generations' => 10,
        'createPopulation' => true,
        'start' => true
    );
}

$validActions = array('start');

if (in_array($action, $validActions)) {
    echo json_encode(call_user_func_array($action, array($_POST)));
} else {
    echo json_encode(false);
}

