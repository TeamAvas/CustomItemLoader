<?php

namespace skh6075\customitemloader;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\convert\ItemTranslator;
use pocketmine\network\mcpe\convert\ItemTypeDictionary;
use pocketmine\network\mcpe\protocol\ItemComponentPacket;
use pocketmine\network\mcpe\protocol\types\ItemComponentPacketEntry;
use pocketmine\network\mcpe\protocol\types\ItemTypeEntry;
use pocketmine\plugin\PluginBase;
use ReflectionClass;
use skh6075\customitemloader\item\CustomDurableItem;
use skh6075\customitemloader\item\CustomItem;
use function json_decode;
use function file_get_contents;

class CustomItemLoader extends PluginBase implements Listener {

    /** @var array */
    public $config = [];


    public function onLoad(): void{
    }

    public function onEnable(): void{
        $this->saveResource("config.json");
        $this->config = json_decode(file_get_contents($this->getDataFolder() . "config.json"), true);

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

            ItemFactory::registerItem($item = (($itemData["description"]["type"] === "default") ? new CustomItem($id, $meta, $key) : new CustomDurableItem($id, $meta, $key, $itemData["components"]["item_components"]["durability"])));
            Item::addCreativeItem($item);
        }
        $simpleNetToCoreMap->setValue(ItemTranslator::getInstance(), $netToCoreValues);
        $simpleCoreToNetMap->setValue(ItemTranslator::getInstance(), $coreToNetValues);
        $itemTypes->setValue(ItemTypeDictionary::getInstance(), $itemTypesValues);

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    /**
     * @param PlayerJoinEvent $event
     */
    public function onPlayerJoin(PlayerJoinEvent $event): void{
        $player = $event->getPlayer();
        $entries = [];

        foreach ($this->config as $key => $itemData) {
            $name = (string)$itemData["description"]["name"];
            $id = (int)$itemData["description"]["id"];

            $entries[] = new ItemComponentPacketEntry($name, new CompoundTag("", [
                new CompoundTag("components", [
                    new CompoundTag("minecraft:display_name", [new StringTag("value", $key)]),
                    new CompoundTag("minecraft:icon", [new StringTag("texture", (string)($itemData["components"]["item_icon"] ?? "apple")),]),
                    new CompoundTag("item_properties", [
                        new ByteTag("allow_off_hand", (int)($itemData["components"]["item_properties"]["allow_off_hand"] ?? false)),
                        new ByteTag("animates_in_toolbar", (int)($itemData["components"]["item_properties"]["animates_in_toolbar"] ?? false)),
                        new ByteTag("can_destroy_toolbar", (int)($itemData["components"]["item_properties"]["can_destroy_toolbar"] ?? false)),
                        new ByteTag("can_destroy_in_creative", (int)($itemData["components"]["item_properties"]["can_destroy_in_creative"] ?? false)),
                        new IntTag("creative_category", (int)($itemData["components"]["item_properties"]["creative_category"] ?? 1)),
                        new IntTag("use_duration", (int)($itemData["components"]["item_properties"]["use_duration"] ?? 0)),
                        new IntTag("max_stack_size", (int)($itemData["components"]["item_properties"]["max_stack_size"] ?? 64)),
                        new StringTag("creative_group", (string)($itemData["components"]["item_properties"]["creative_group"] ?? "")),
                    ]),
                    new CompoundTag("minecraft:durability", [
                        new IntTag("max_durability", (int)($itemData["components"]["item_components"]["durability"] ?? 32767))
                    ]),
                ]),
                new ShortTag("id", $id),
                new StringTag("name", $key)
            ]));
        }
        $player->sendDataPacket(ItemComponentPacket::create($entries));
    }
}
