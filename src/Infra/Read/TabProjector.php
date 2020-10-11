<?php

declare(strict_types=1);

namespace Cafe\Infra\Read;

use Cafe\Application\Read\OpenTabs\Tab;
use Cafe\Domain\Tab\Events\DrinksOrdered;
use Cafe\Domain\Tab\Events\FoodOrdered;
use Cafe\Domain\Tab\Events\TabOpened;
use Cafe\Domain\Tab\OrderedItem;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use EventSauce\EventSourcing\Consumer;
use EventSauce\EventSourcing\Message;

class TabProjector implements Consumer
{
    private EntityManagerInterface $entityManager;
    private Connection $connection;

    public function __construct(EntityManagerInterface $entityManager, Connection $connection)
    {
        $this->entityManager = $entityManager;
        $this->connection = $connection;
    }

    public function handle(Message $message) : void
    {
        $event = $message->event();

        if ($event instanceof TabOpened) {
            $this->entityManager->persist(new Tab($event->tabId->toString(), $event->tableNumber, $event->waiter, [], [], []));
        }

        if ($event instanceof DrinksOrdered) {
            /** @var OrderedItem $item */
            foreach ($event->items as $item) {
                $this->connection->insert('read_model_tab_item', [
                    'tab_id' => $event->tabId->toString(),
                    'menu_number' => $item->menuNumber,
                    'description' => $item->description,
                    'price' => $item->price,
                    'status' => 'to-serve'
                ]);
            }
        }

        if ($event instanceof FoodOrdered) {
            /** @var OrderedItem $item */
            foreach ($event->items as $item) {
                $this->connection->insert('read_model_tab_item', [
                    'tab_id' => $event->tabId->toString(),
                    'menu_number' => $item->menuNumber,
                    'description' => $item->description,
                    'price' => $item->price,
                    'status' => 'in-preparation'
                ]);
            }
        }

        $this->entityManager->flush();
    }
}