<?php

namespace skh6075\customitemloader\resourcepack;

use pocketmine\resourcepacks\ZippedResourcePack;
use pocketmine\Server;
use skh6075\customitemloader\CustomItemLoader;
use ReflectionClass;
use function is_dir;
use function mkdir;
use function array_diff;
use function scandir;
use function strtolower;
use function substr;
use function strrchr;
use function count;

final class ResourcePackLoader{

    /** @var ?ResourcePackLoader */
    private static $instance = null;
    /** @var string */
    protected $path;


    public static function getInstance(): ?ResourcePackLoader{
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        if (empty($this->path)) {
            $this->path = CustomItemLoader::getInstance()->getDataFolder();
        }
        if (!is_dir($this->path . "resources/")) {
            mkdir($this->path . "resources/");
        }
    }

    public function init(): void{
        $founded = [];

        foreach (array_diff(scandir($this->path . "resources/"), [".", ".."]) as $value) {
            if (!in_array(substr(strrchr($value, '.'), 1), ["zip", "mcpack"])) {
                continue;
            }
            $founded [] = $value;
        }
        $manager = Server::getInstance()->getResourcePackManager();
        $ref = new ReflectionClass($manager);

        foreach ($founded as $value) {
            $pack = new ZippedResourcePack($value);
            $property = $ref->getProperty("resourcePacks");
            $property->setAccessible(true);

            $newResourcePack = $property->getValue($manager);
            $newResourcePack[] = $pack;
            $property->setValue($manager, $newResourcePack);

            $uuidProperty = $ref->getProperty("uuidList");
            $uuidProperty->setAccessible(true);
            $uuidPacks = $uuidProperty->getValue($manager);
            $uuidPacks[strtolower($pack->getPackId())] = $pack;
            $uuidProperty->setValue($manager, $uuidPacks);

            $serverProperty = $ref->getProperty("serverForceResources");
            $serverProperty->setAccessible(true);
            $serverProperty->setValue($manager, true);
        }
        Server::getInstance()->getLogger()->notice("Loaded ResourcePacks " . count($founded) . " count.");
    }
}