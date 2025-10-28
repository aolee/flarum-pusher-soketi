<?php

/*
 * This file is part of Flarum.
 *
 * For detailed copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

namespace Flarum\Pusher\Api\Controller;

use Flarum\Http\RequestUtil;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Support\Arr;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Pusher\Pusher;
use Flarum\Pusher\SoketiConfig;

class AuthController implements RequestHandlerInterface
{
    public function __construct(
        protected SettingsRepositoryInterface $settings
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $userChannel = 'private-user' . RequestUtil::getActor($request)->id;
        $body = $request->getParsedBody();

        if (Arr::get($body, 'channel_name') === $userChannel) {
            // Keep cluster if set (ignored when custom host is used)
            $options = ['cluster' => $this->settings->get('flarum-pusher.app_cluster')];

            // Inject Soketi host/port/scheme if configured in config.php
            $soketi = resolve(SoketiConfig::class);
            $host = $soketi->host();
            $port = $soketi->port();
            $tls  = $soketi->tls();

            if ($host) {
                $options['host']   = $host;
                $options['port']   = $port ?? ($tls ? 443 : 6001);
                $options['scheme'] = $tls ? 'https' : 'http';
            }

            $pusher = new Pusher(
                $this->settings->get('flarum-pusher.app_key'),
                $this->settings->get('flarum-pusher.app_secret'),
                $this->settings->get('flarum-pusher.app_id'),
                $options
            );

            $payload = json_decode(
                $pusher->socket_auth($userChannel, Arr::get($body, 'socket_id')),
                true
            );

            return new JsonResponse($payload);
        }

        return new EmptyResponse(403);
    }
}
