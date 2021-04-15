<?php


namespace Tests\Unit;


use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ProvidersTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('config:clear');
    }

    /**
     * @test
     */
    public function it_enforce_https()
    {
        putenv('ENFORCE_HTTPS=true');

        $url = $this->createApplication()
            ->make('url')
            ->to('/');

        $this->assertStringContainsString('https://', $url);
    }

    /**
     * @test
     */
    public function it_not_enforce_https()
    {
        putenv('ENFORCE_HTTPS=false');

        $url = $this->createApplication()
            ->make('url')
            ->to('/');

        $this->assertStringContainsString('http://', $url);
    }

}
