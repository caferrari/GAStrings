<?php

class WorkLoad
{

    protected $db;
    protected $workPhrase;
    protected $data;

    public function __construct($workPhrase, $data=array()) {
        $dbname = md5($workPhrase);

        $db = new PDO("sqlite:db{$dbname}.sqlite3");
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $db->exec("CREATE TABLE IF NOT EXISTS workload (
                   id INTEGER PRIMARY KEY,
                   fitness INT,
                   gene TEXT)");

        $this->db = $db;
        $this->data = $data;
        $this->workPhrase = $workPhrase;
    }

    public function start($data)
    {
        return array(
            'workPhrase' => $this->workPhrase,
            'populationSize' => 3000,
            'sharedPopulationSize' => 300,
            'generations' => 100,
            'createPopulation' => true,
            'start' => true
        );
    }

    public function workload($data)
    {

        $sql = "SELECT gene, fitness FROM workload ORDER BY fitness LIMIT 1";
        $rs = $this->db->query($sql)->fetchAll(PDO::FETCH_CLASS);

        if ($rs && (integer)$rs[0]->fitness === 0) {
            return array(
                'done' => true
            );
        }

        $insert = $this->db->prepare("INSERT INTO workload (gene, fitness) VALUES (?, ?)");

        $this->db->beginTransaction();

        foreach ($data['population'] as $chm) {
            $insert->execute(array_values($chm));
        }

        $this->db->commit();

        if (count($data['population']) == 1) {
            return array(
                'done' => true
            );
        }

        $sql = "SELECT gene, fitness FROM workload ORDER BY fitness LIMIT 100";
        $rs = $this->db->query($sql)->fetchAll(PDO::FETCH_CLASS);

        foreach ($rs as &$item) {
            $item->fitness = (integer)$item->fitness;
        }

        return array(
            'mergeElite' => $rs,
            'start' => true
        );

    }

    public function execute($action)
    {
        $validActions = array('start', 'workload');
        if (in_array($action, $validActions)) {
            return json_encode($this->$action($this->data));
        }
        return json_encode(false);
    }

}

$workPhrase = trim(file_get_contents('objective.txt'));
$action = $_POST['action'];
unset($_POST['action']);

$wl = new WorkLoad($workPhrase, $_POST);
echo $wl->execute($action);
