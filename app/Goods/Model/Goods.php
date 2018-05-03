<?php
namespace App\Goods\Model;

use App\Goods\TableOptions;
use Illuminate\Database\Eloquent\Model;

class Goods extends Model
{
    /**
     * Table name is dynamic
     *
     * @var string
     */
    protected $table = 'goods';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'article',
        'code',
        'price',
        'currency',
        'quantity',
    ];

    /**
     * @return TableOptions
     */
    public function getTableOptions()
    {
        return new TableOptions($this->table);
    }
}