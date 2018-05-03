<?php

namespace App\Jobs;

use App\Goods\Model\Goods as GoodsModel;
use App\Goods\TableOptions;
use App\Pricelist\Data\Exception\ValidateDataException;
use App\Pricelist\Data\ForwardMotorsRow;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Nickfan\ChannelLog\Facades\ChannelLog;

class ImportPriceList implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const INSERT_BLOCK = 300;

    /**
     * @var \SplFileInfo
     */
    protected $realPath;

    /**
     * @var string
     */
    protected $encoding;

    /**
     * @var string
     */
    protected $provider;

    /**
     * @var integer
     */
    protected $shop;

    /**
     * @var GoodsModel
     */
    protected $model;

    /**
     * ImportPriceList constructor.
     *
     * @param \SplFileInfo $file
     * @param $provider
     * @param $shopID
     */
    public function __construct(\SplFileInfo $file, $provider, $shopID)
    {
        $this->realPath = $file->getRealPath();
        $this->provider = $provider;
        $this->shop = $shopID;
    }

    /**
     * Open file
     *
     * @return \SplFileObject
     */
    public function open()
    {
        return new \SplFileObject($this->realPath);
    }

    /**
     * Get file encoding
     *
     * @return null
     */
    public function encoding()
    {
        if (is_null($this->encoding)) {
            $this->encoding = file_encoding($this->realPath);
        }

        return $this->encoding;
    }

    /**
     * @return GoodsModel
     */
    public function model()
    {
        if (is_null($this->model)) {
            $options = (new TableOptions())
                ->setProvider($this->provider)
                ->setShopID($this->shop)
                ->setTemporary(true);

            if (null == ($this->model = \Goods::model($options))) {
                $this->model = \Goods::create($options);
            } else {
                $this->model->truncate();
            }
        }

        return $this->model;
    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $file = $this->open();

        $line = 0;
        $rows = collect();
        $file->rewind();
        while (!$file->eof()) {
            $line++;

            try {
                $row = new ForwardMotorsRow( $file->fgets(), $this->encoding());

                if ($row->isEmpty()) {
                    continue;
                }

                $rows[] = $row;

            } catch (ValidateDataException $exception) {
                ChannelLog::channel('parser')
                    ->info(sprintf('Line [%d]: %s', $line, $exception->getMessage()));

                continue;
            }

            $rows = $this->save($rows);
        }

        $options = $this->model()->getTableOptions();
        $options->setTemporary(false);

        Schema::dropIfExists($options->getTableName());
        Schema::rename($this->model()->getTable(), $options->getTableName());
    }

    /**
     * Mass insert to table
     *
     * @param Collection $rows
     * @return Collection
     */
    protected function save(Collection $rows)
    {
        if ($rows->count() >= static::INSERT_BLOCK) {
            $this->model()->insert($rows->map(function($row) {
                return [
                    'article' => $row->getArticle(),
                    'code' => $row->getCode(),
                    'provider' => 'forward-motors',
                    'price' => $row->getPrice(),
                    'currency' => $row->getCurrency(),
                    'quantity' => $row->getQuantity() == INF ? -1 : $row->getQuantity(),
                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                ];
            })->toArray());

            $rows = collect();
        }

        return $rows;
    }
}
