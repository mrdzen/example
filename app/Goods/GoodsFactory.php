<?php
namespace App\Goods;

use App\Goods\Model\Goods;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class GoodsFactory
{
    /**
     * Get table model
     *
     * @param TableOptions $options
     * @return Goods|null
     */
    public function model(TableOptions $options)
    {
        if (Schema::hasTable($options->getTableName())) {
            $model = new Goods();
            $model->setTable($options->getTableName());

            return $model;
        }

        return null;
    }

    /**
     * Create table
     *
     * @param TableOptions $options
     * @return Goods|null
     */
    public function create(TableOptions $options)
    {
        Schema::create($options->getTableName(), function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('article', 64);
            $table->string('code', 64);
            $table->string('provider', 32);
            $table->double('price', 8, 2);
            $table->string('currency', 3);
            $table->smallInteger('quantity');
            $table->timestamp('created_at');
        });

        return $this->model($options);
    }
}