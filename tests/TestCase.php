<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Carbon;

abstract class TestCase extends BaseTestCase
{
    /**
     * Fecha y hora fija para que las pruebas sean deterministas:
     * lunes 5 de octubre de 2026, 08:00 (hora de Lima).
     */
    public const AHORA = '2026-10-05 08:00:00';

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::parse(self::AHORA));

        // Las pruebas no dependen de que los assets esten compilados.
        $this->withoutVite();
    }
}
