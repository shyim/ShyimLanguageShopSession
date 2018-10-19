<?php

namespace ShyimLanguageShopSession\Subscriber;

use Doctrine\DBAL\Connection;
use Enlight\Event\SubscriberInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;

/**
 * Class CartSubscriber
 * @package ShyimLanguageShopSession\Subscriber
 */
class CartSubscriber implements SubscriberInterface
{
    /**
     * @var Connection
     */
    private $connection;
    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    /**
     * CartSubscriber constructor.
     *
     * @param Connection              $connection
     * @param ContextServiceInterface $contextService
     */
    public function __construct(Connection $connection, ContextServiceInterface $contextService)
    {
        $this->connection = $connection;
        $this->contextService = $contextService;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopware_Modules_Basket_UpdateArticle_Start' => 'sUpdateArticle',
        ];
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     * @return bool
     */
    public function sUpdateArticle(\Enlight_Event_EventArgs $args)
    {
        /** @var \sBasket $subject */
        $subject = $args->getSubject();

        $id = $args->get('id');

        $productId = (int) $this->connection->fetchColumn('SELECT articleID FROM s_order_basket WHERE id = ? AND modus = 0');

        if ($productId) {
            $hasCategories = (bool) $this->connection->fetchColumn('SELECT 1 FROM s_articles_categories_ro WHERE articleID = ? AND categoryID = ?', [
                $productId,
                $this->contextService->getShopContext()->getShop()->getCategory()->getId(),
            ]);

            if (!$hasCategories) {
                $subject->sDeleteArticle($id);

                return true;
            }
        }
    }
}
