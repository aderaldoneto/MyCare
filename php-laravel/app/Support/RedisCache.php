<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class RedisCache
{
    public function __construct(private string $store = 'redis')
    {
    }

    public function remember(string $tag, string $key, Closure $callback): mixed
    {
        try {
            $cache = Cache::store($this->store);
            $versionKey = "cache-version:{$tag}";
            $cache->add($versionKey, 1, now()->addYears(100));
            $version = $cache->get($versionKey, 1);

            return $cache->remember(
                "{$tag}:v{$version}:{$key}",
                now()->addMinutes(5),
                $callback
            );
        } catch (Throwable $exception) {
            Log::warning('Redis indisponível; a resposta será carregada sem cache.', [
                'tag' => $tag,
                'exception' => $exception->getMessage(),
            ]);

            return $callback();
        }
    }

    public function flush(string $tag): void
    {
        try {
            Cache::store($this->store)->increment("cache-version:{$tag}");
        } catch (Throwable $exception) {
            Log::warning('Não foi possível invalidar o cache Redis.', [
                'tag' => $tag,
                'exception' => $exception->getMessage(),
            ]);
        }
    }
}
