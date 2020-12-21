<?php

namespace skh6075\customitemloader\listener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\network\mcpe\protocol\ItemComponentPacket;
use skh6075\customitemloader\CustomItemPacketEntry;

class EventListener implements Listener{

    /**
     * @param PlayerJoinEvent $event
     * @priority HIGHEST
     */
    public function onPlayerJoin(PlayerJoinEvent $event): void{
        $event->getPlayer()->sendDataPacket(ItemComponentPacket::create(CustomItemPacketEntry::getInstance()->getItemComponentEntries()));
    }
}