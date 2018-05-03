<?php
namespace App\Goods;

use \Illuminate\Support\Facades\Facade as IlluminateFacade;

class GoodsFacade extends IlluminateFacade
{
    /**
     *  Get the registered name of the component.
     *
     * @return mixed
     */
    protected static function getFacadeAccessor()
    {
        return GoodsFactory::class;
    }
}
