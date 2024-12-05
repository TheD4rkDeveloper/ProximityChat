<?php

namespace D4rkDev\ProximityChat;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\player\Player;
use pocketmine\permission\DefaultPermissions;

class Main extends PluginBase implements Listener
{
    private Config $config;

    public function onEnable(): void
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        
        $this->saveResource("config.yml");
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        if (strtolower($command->getName()) === "sudo") {
            if (count($args) < 2) {
                $sender->sendMessage('Usage: /sudo <player> <message>');
                $sender->sendMessage('Az Shoma Eshtebah Dide Shod Lotfan Code Ro Yekbar Digar Be Dorosti Befrestid.');
                return false;
            }

            $playerName = array_shift($args);
            $player = $this->getServer()->getPlayerExact($playerName);

            if ($player instanceof Player) {
                $player->chat(implode(" ", $args));
                return true;
            } else {
                $sender->sendMessage("Player Peyda Nashod.");
                return false;
            }
        }

        return false;
    }

    public function onChat(PlayerChatEvent $event): void
    {
        $player = $event->getPlayer();
        $recipients = [$player];

        $range = (float)$this->config->get("range", 20.0);

        $boundingBox = $player->getPosition()->getWorld()->getNearbyEntities(
            $player->getBoundingBox()->expandedCopy($range, $range, $range),
            $player
        );

        foreach ($boundingBox as $entity) {
            if ($entity instanceof Player) {
                $recipients[] = $entity;
            }
        }

        if ($this->config->get("send-to-op", true)) {
            foreach ($this->getServer()->getOnlinePlayers() as $onlinePlayer) {
                if ($onlinePlayer->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                    if (!in_array($onlinePlayer, $recipients, true)) {
                        $recipients[] = $onlinePlayer;
                    }
                }
            }
        }

        $event->setRecipients($recipients);
    }
}