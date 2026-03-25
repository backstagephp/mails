<?php

namespace Backstage\Mails\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Backstage\Mails\Mails
 */
class Mails extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Backstage\Mails\Mails::class;
    }
}
