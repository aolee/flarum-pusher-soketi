<?php

/*
 * This file is part of Flarum.
 *
 * For detailed copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

namespace Flarum\Pusher\Provider;

use Flarum\Foundation\AbstractServiceProvider;
use Flarum\Settings\SettingsRepositoryInterface;
use Pusher\Pusher;
use Flarum\Pusher\SoketiConfig;

class PusherProvider extends AbstractServiceProvider
{
    public function register(): void
    {
        $this->container->bind(Pusher::class, function () {
            $settings = $this->container->make(SettingsRepositoryInterface::class);

            $options = [];

            if ($cluster = $settings->get('flarum-pusher.app_cluster')) {
                $options['cluster'] = $cluster;
            }

            // Inject Soketi host/port/scheme if configured in config.php
            $soketi = $this->container->make(SoketiConfig::class);
            $host = $soketi->host();
            $port = $soketi->port();
            $tls  = $soketi->tls();

            if ($host) {
                $options['host']   = $host;
                $options['port']   = $port ?? ($tls ? 443 : 6001);
                $options['scheme'] = $tls ? 'https' : 'http';
            }

            return new Pusher(
                $settings->get('flarum-pusher.app_key'),
                $settings->get('flarum-pusher.app_secret'),
                $settings->get('flarum-pusher.app_id'),
                $options
            );
        });
    }
}
