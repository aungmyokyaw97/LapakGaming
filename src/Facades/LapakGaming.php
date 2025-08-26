<?php

namespace Amk\LapakGaming\Facades;

use Illuminate\Support\Facades\Facade;

class LapakGaming extends Facade 
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() 
    { 
        return \Amk\LapakGaming\LapakGaming::class; 
    }
}
