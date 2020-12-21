<?php

namespace skh6075\customitemloader;

use pocketmine\event\Listener;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\network\mcpe\convert\ItemTranslator;
use pocketmine\network\mcpe\convert\ItemTypeDictionary;
use pocketmine\network\mcpe\protocol\types\ItemTypeEntry;
use pocketmine\plugin\PluginBase;
use ReflectionClass;
use skh6075\customitemloader\item\CustomDurableItem;
use skh6075\customitemloader\item\CustomItem;
use skh6075\customitemloader\listener\EventListener;
use skh6075\customitemloader\resourcepack\ResourcePackLoader;
use function json_decode;
use function file_get_contents;

class CustomItemLoader extends PluginBase implements Listener {

    /** @var ?CustomItemLoader */
    private static $instance = null;
    /** @var array */
    public $config = [];


    public static function getInstance(): ?CustomItemLoader{
        return self::$instance;
    }

    public function onLoad(): void{
        if (self::$instance === null) {
            self::$instance = $this;
        }
    }

    public function onEnable(): void{
        $this->saveResource("config.json");
        $this->config = json_decode(file_get_contents($this->getDataFolder() . "config.json"), true);

        ResourcePackLoader::getInstance()->init();

        $ref = new ReflectionClass(ItemTranslator::class);
        $simpleCoreToNetMap = $ref->getProperty("simpleCoreToNetMapping");
        $simpleNetToCoreMap = $ref->getProperty("simpleNetToCoreMapping");
        $simpleCoreToNetMap->setAccessible(true);
        $simpleNetToCoreMap->setAccessible(true);
        $coreToNetValues = $simpleCoreToNetMap->getValue(ItemTranslator::getInstance());
        $netToCoreValues = $simpleNetToCoreMap->getValue(ItemTranslator::getInstance());

        $ref_1 = new ReflectionClass(ItemTypeDictionary::class);
        $itemTypes = $ref_1->getProperty("itemTypes");
        $intToStringMap = $ref_1->getProperty("intToStringIdMap");
        $stringToIntMap = $ref_1->getProperty("stringToIntMap");
        $itemTypes->setAccessible(true);
        $intToStringMap->setAccessible(true);
        $stringToIntMap->setAccessible(true);

        $itemTypesValues = $itemTypes->getValue(ItemTypeDictionary::getInstance());
        foreach ($this->config as $key => $itemData) {
            $name = (string)$itemData["description"]["name"];
            $id = (int)$itemData["description"]["id"];
            $meta = (int)$itemData["description"]["meta"];
            $runtimeId = $id;

            $itemTypesValues[] = $entry = new ItemTypeEntry($name, $runtimeId, true);
            $coreToNetValues[$entry->getNumericId()] = $runtimeId;
            $netToCoreValues[$runtimeId] = $entry->getNumericId();

            CustomItemPacketEntry::getInstance()->pushItemComponentEntry(CustomItemPacketEntry::makeItemComponentPacket($key, $itemData));
            ItemFactory::registerItem($item = (($itemData["description"]["type"] === "default") ? new CustomItem($id, $meta, $key, ($itemData["components"]["item_properties"]["max_stack_size"] ?? 64)) : new CustomDurableItem($id, $meta, $key, $itemData["components"]["item_components"]["durability"], ($itemData["components"]["item_properties"]["max_stack_size"] ?? 64))));
            Item::addCreativeItem($item);
        }
        $simpleNetToCoreMap->setValue(ItemTranslator::getInstance(), $netToCoreValues);
        $simpleCoreToNetMap->setValue(ItemTranslator::getInstance(), $coreToNetValues);
        $itemTypes->setValue(ItemTypeDictionary::getInstance(), $itemTypesValues);

        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
    }
}