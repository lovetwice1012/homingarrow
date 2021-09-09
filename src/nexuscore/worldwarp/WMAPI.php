<?php

declare(strict_types=1);

namespace nexuscore\worldwarp;

use pocketmine\Server;

class WMAPI {
    public static function getAllLevels(): array {
        $levels = [];
        foreach (glob(Server::getInstance()->getDataPath() . "/worlds/*") as $world) {
            try {
                if(count(scandir($world)) >= 4) { 
                    $levels[] = basename($world);
                }
            } catch ( \Exception $ex ) {
            
            }
        }
        return $levels;
    }
}
