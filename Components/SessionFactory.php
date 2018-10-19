<?php

namespace ShyimLanguageShopSession\Components;

use Shopware\Components\DependencyInjection\Bridge\Session;
use Shopware\Components\DependencyInjection\Container;

class SessionFactory extends Session
{
    public function createSession(Container $container, \SessionHandlerInterface $saveHandler = null)
    {
        $sessionOptions = $container->getParameter('shopware.session');

        if (!empty($sessionOptions['unitTestEnabled'])) {
            \Enlight_Components_Session::$_unitTestEnabled = true;
        }
        unset($sessionOptions['unitTestEnabled']);

        if (\Enlight_Components_Session::isStarted()) {
            \Enlight_Components_Session::writeClose();
        }

        /** @var $shop \Shopware\Models\Shop\Shop */
        $shop = $container->get('Shop');
        $mainShop = $shop->getMain() ?: $shop;

        $sessionOptions['name'] = 'session-' . $mainShop->getId();

        if ($mainShop->getSecure()) {
            $sessionOptions['cookie_secure'] = true;
        }

        if ($saveHandler) {
            session_set_save_handler($saveHandler);
            unset($sessionOptions['save_handler']);
        }

        unset($sessionOptions['locking']);

        \Enlight_Components_Session::start($sessionOptions);

        $container->set('SessionID', \Enlight_Components_Session::getId());

        $namespace = new \Enlight_Components_Session_Namespace('Shopware');
        $namespace->offsetSet('sessionId', \Enlight_Components_Session::getId());

        return $namespace;
    }
}
