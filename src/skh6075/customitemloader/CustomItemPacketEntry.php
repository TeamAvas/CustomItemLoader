<?php

namespace skh6075\customitemloader;

use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\types\ItemComponentPacketEntry;

final class CustomItemPacketEntry{

    /** @var ?CustomItemPacketEntry */
    private static $instance = null;
    /** @var ItemComponentPacketEntry[] */
    private static $entries = [];


    public static function getInstance(): ?CustomItemPacketEntry{
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
    }

    public function pushItemComponentEntry(ItemComponentPacketEntry $entry): void{
        self::$entries[] = $entry;
    }

    /**
     * @return ItemComponentPacketEntry[]
     */
    public function getItemComponentEntries(): array{
        return self::$entries;
    }

    public static function makeItemComponentPacket(string $key, array $data): ItemComponentPacketEntry{
        $name = (string)$data["description"]["name"];
        $id = (int)$data["description"]["id"];

        $allow_off_hand = (int)($data["components"]["item_properties"]["allow_off_hand"] ?? false);
        $animates_in_toolbar = (int)($data["components"]["item_properties"]["animates_in_toolbar"] ?? false);
        $can_destroy_toolbar = (int)($data["components"]["item_properties"]["can_destroy_toolbar"] ?? false);
        $can_destroy_in_creative = (int)($data["components"]["item_properties"]["can_destroy_in_creative"] ?? false);
        $creative_category = (int)($data["components"]["item_properties"]["creative_category"] ?? 1);
        $use_duration = (int)($data["components"]["item_properties"]["use_duration"] ?? 0);
        $max_stack_size = (int)($data["components"]["item_properties"]["max_stack_size"] ?? 64);
        $creative_group = (string)($data["components"]["item_properties"]["creative_group"] ?? "");

        $nbt = new CompoundTag("", [
            new CompoundTag("components", [
                new CompoundTag("minecraft:display_name", [new StringTag("value", $key)]),
                new CompoundTag("minecraft:icon", [new StringTag("texture", (string)($data["components"]["item_icon"] ?? "apple")),]),
                new CompoundTag("item_properties", [
                    new ByteTag("allow_off_hand", $allow_off_hand),
                    new ByteTag("animates_in_toolbar", $animates_in_toolbar),
                    new ByteTag("can_destroy_toolbar", $can_destroy_toolbar),
                    new ByteTag("can_destroy_in_creative", $can_destroy_in_creative),
                    new IntTag("creative_category", $creative_category),
                    new IntTag("use_duration", $use_duration),
                    new IntTag("max_stack_size", $max_stack_size),
                    new StringTag("creative_group", $creative_group)
                ]),
            ]),
            new CompoundTag("minecraft:on_use", [new ByteTag("on_use", 1)]),
            new CompoundTag("minecraft:on_use_on", [new ByteTag("on_use_on", 1)]),
            new ShortTag("id", $id),
            new StringTag("name", $name)
        ]);
        if ($data["description"]["type"] === "durable") {
            $nbt->setTag(new CompoundTag("minecraft:durability", [
                new ShortTag("damage_change", (int)($data["durability"]["damage_change"] ?? 1)),
                new ShortTag("max_durable", (int)($data["durability"]["max_durable"] ?? 32767))
            ]));
        }
        return new ItemComponentPacketEntry($name, $nbt);
    }
}