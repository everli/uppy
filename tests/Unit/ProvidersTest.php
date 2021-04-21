<?php


namespace Tests\Unit;


use Illuminate\Support\Env;
use Tests\TestCase;

class ProvidersTest extends TestCase
{

    /**
     * @test
     */
    public function it_enforce_https()
    {
        Env::getRepository()->set('ENFORCE_HTTPS','true');

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
        Env::getRepository()->set('ENFORCE_HTTPS','false');

        $url = $this->createApplication()
            ->make('url')
            ->to('/');

        $this->assertStringContainsString('http://', $url);
    }

}
