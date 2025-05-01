<?php

namespace Tests\Unit\Services;

use App\Services\MemeCoinService;
use PHPUnit\Framework\TestCase;

class MemeCoinServiceTest extends TestCase
{
    /** @test */
    public function it_builds_base_coin_name()
    {
        $service = new MemeCoinService();
        $this->assertEquals('HODLJoMiDoRocket', $service->buildBaseCoinName('John Michael Doe'));
        $this->assertEquals('MoonJaSmToken', $service->buildBaseCoinName('Jane Smith'));
    }

    /** @test */
    public function it_handles_unicode_names()
    {
        $service = new MemeCoinService();
        $result = $service->buildBaseCoinName('José Álvarez');
        $this->assertStringContainsString('JoÁl', $result);
    }
}
