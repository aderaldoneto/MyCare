<?php

namespace Tests\Unit;

use App\Support\RedisCache;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class RedisCacheTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::store('array')->flush();
    }

    public function test_flushing_a_group_makes_the_next_read_recompute_its_value(): void
    {
        $cache = new RedisCache('array');
        $loads = 0;

        $first = $cache->remember('patients', 'list:page-1', function () use (&$loads) {
            $loads++;

            return 'before update';
        });
        $cached = $cache->remember('patients', 'list:page-1', function () use (&$loads) {
            $loads++;

            return 'unexpected cache miss';
        });

        $cache->flush('patients');

        $updated = $cache->remember('patients', 'list:page-1', function () use (&$loads) {
            $loads++;

            return 'after update';
        });

        $this->assertSame('before update', $first);
        $this->assertSame('before update', $cached);
        $this->assertSame('after update', $updated);
        $this->assertSame(2, $loads);
    }
}
