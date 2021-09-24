<?php


namespace nexuscore\task;


use pocketmine\item\Item;
use pocketmine\plugin\Plugin;
use pocketmine\scheduler\AsyncTask;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\Config;
use nexuscore\nexuscore;

class checktweettask extends Task
{
    private $plugin;
    /** @var Config $config */
    private $config;
    /** @var Config $config1 */
    private $config1;

    public function __construct(Plugin $plugin, Config $rewardconfig, Config $dayconfig)
    {
        $this->plugin = $plugin;
        $this->config = $rewardconfig;
        $this->config1 = $dayconfig;
    }

    public function onRun():void
    {

        //$economy = $this->config->get("Reward_economy", false);
        //$items = $this->config->get("Reward_items", false);

        $tablearray = $this->config->get("Reward_table", false);

        $week = [
            0 => '日', //0
            1 => '月', //1
            2 => '火', //2
            3 => '水', //3
            4 => '木', //4
            5 => '金', //5
            6 => '土', //6
        ];

        $table = $tablearray[$week[date("w")]] ?? -1;
        if ($table === -1) {
            return;
        }

        $day = date("z");//0-365
        if ($this->config1->get("day", -1) === $day) {
            return;
        }
        $this->config1->set("day", $day);
        $this->config1->save();

        Server::getInstance()->getAsyncPool()->submitTask(new class($this->config) extends AsyncTask {
            public $config;

            public function __construct(Config $config)
            {
                $this->config = $config;
            }

            public function onRun():void
            {
                var_dump("!!");
                $url = 'https://script.google.com/macros/s/AKfycbxBhaMRTSKOoCoLnHEsArmc8i4901NUOFFM-qSpxZYVOO4EEC2Cu3yKhxlY1_IVrbWsRg/exec';

                $data = array(//"test" => "test",
                );
                $data = http_build_query($data, "", "&");

                $header = array(
                    "Content-Type: application/x-www-form-urlencoded",
                    "Content-Length: " . strlen($data)
                );

                $context = array(
                    "http" => array(
                        "protocol_version" => "1.1",
                        "method" => "POST",
                        "header" => implode("\r\n", $header),
                        "content" => $data
                    )
                );

                $html = file_get_contents($url, false, stream_context_create($context));

                $result = json_decode($html, true);
                var_dump($result);
                $array = [];
                foreach ($result as $data1) {
                    $array[$data1["gamertag"]][] = $data1;
                }

                $this->setResult($array);
            }

            public function onCompletion():void
            {
                $economyapi = Server::getInstance()->getPluginManager()->getPlugin("EconomyAPI");
                $economy = $this->config->get("Reward_economy", false);
                $items = $this->config->get("Reward_items", false);

                $tablearray = $this->config->get("Reward_table", false);

                $week = [
                    0 => '日', //0
                    1 => '月', //1
                    2 => '火', //2
                    3 => '水', //3
                    4 => '木', //4
                    5 => '金', //5
                    6 => '土', //6
                ];

                $table = $tablearray[$week[date("w")]] ?? -1;

                //$previous = $this->config->get("previous");
                $array = $this->getResult();
                $result = nexuscore::$itemconfig->getAll();
                foreach ($array as $gamertag => $value) {//$gamertag
                    if ($this->config->get("can_duplicate", false)) {
                        foreach ($value as $test) {
                            $result[$gamertag] = [...($this->addReward($gamertag, $economyapi, $economy, $items, $table)), ...($result[$gamertag] ?? [])];
                        }
                    } else {
                        $result[$gamertag] = [...($this->addReward($gamertag, $economyapi, $economy, $items, $table)), ...($result[$gamertag] ?? [])];
                    }
                }
                var_dump([$array,$result]);
                nexuscore::$itemconfig->setAll($result);
                nexuscore::$itemconfig->save();
            }

            public function addReward($username, $economyapi, $economy, $items1, $table)
            {
                if ($economy !== false) {
                    if ($economyapi !== null) {
                        $economyapi->addMoney($username, $economy[$table]);
                        var_dump($economy[$table]);
                    }
                }

                $result = [];
                if ($items1 !== false) {
                    $items = $items1[$table] ?? null;
                    if (!is_array($items)) {
                        return [];
                    }
                    $result = [];
                    foreach ($items as $itemdata) {
                        $result[] = $itemdata;//Item::jsonDeserialize($itemdata);
                    }
                }
                return $result;
            }
        });
    }
}
