<?php
namespace Fluid\Socket;

use Fluid\Event;

/**
 * This class registers all WebSocket Server Events
 * @package Fluid\Socket
 */
class WebSocketServerEvents
{
    public static function register()
    {
        // User loads data from a page
        Event::on('data:get', function($session, $language, $page) {
            WebSocketServer::sendToSession($session, array(
                'target' => 'data_request',
                'data' => array(
                    'language' => $language,
                    'page' => $page
                )
            ));
        });

        // User loads data from a page
        Event::on('language:changed', function($session, $language) {
            WebSocketServer::sendToSession($session, array(
                'target' => 'language_detected',
                'data' => array(
                    'language' => $language
                )
            ));
        });
    }
}
