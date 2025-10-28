<?php
namespace Flarum\Pusher;
use Flarum\Foundation\Config;

class SoketiConfig
{
    public function __construct(private Config $config) {}

    public function host(): ?string
    {
        return $this->config['soketi.host'] ?? null;
    }

    public function port(): ?int
    {
        $p = $this->config['soketi.port'] ?? null;
        return $p !== null ? (int) $p : null;
    }

    public function tls(): bool
    {
        return (bool) ($this->config['soketi.tls'] ?? true);
    }
}
