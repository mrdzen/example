<?php
namespace App\Goods;

use App\Constants;
use App\Goods\Exception\GoodsTableException;
use App\Goods\Model\Goods;

class TableOptions
{
    /**
     * @var string
     */
    protected $provider;

    /**
     * @var integer
     */
    protected $shopID;

    /**
     * @var bool
     */
    protected $temporary = false;

    /**
     * TableOptions constructor.
     * @param null $table
     */
    public function __construct($table = null)
    {
        if (null != $table) {
            $string = str_replace(app()->make(Goods::class)->getTable() . '_', '', $table);

            $params = explode('_', $string);

            if (isset($params[0]) && $params[0]) {
                $this->setProvider($params[0]);
            }

            if (isset($params[1]) && $params[1]) {
                $this->setShopID($params[1]);
            }

            if (isset($params[2]) && $params[2]) {
                $this->setTemporary(true);
            }
        }
    }

    /**
     * Set parts provider
     *
     * @param $provider
     * @return $this
     * @throws GoodsTableException
     */
    public function setProvider($provider)
    {
        if (app()->make(Constants::class)->getPartsProviders()->has($provider)) {
            $this->provider = $provider;
        } else {
            throw new GoodsTableException(sprintf('Parts provider code is invalid: "%s"', $provider));
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * Set shop id
     *
     * @param $shopID
     * @return $this
     * @throws GoodsTableException
     */
    public function setShopID($shopID)
    {
        if ($shopID > 0) {
            $this->shopID = (int)$shopID;
        } else {
            throw new GoodsTableException(sprintf('Shop ID is invalid : "%d"', $shopID));
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getShopID()
    {
        return $this->shopID;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setTemporary($value)
    {
        $this->temporary = (bool)$value;
        return $this;
    }

    /**
     * @return bool
     */
    public function isTemporary()
    {
        return $this->temporary;
    }

    /**
     * Return table name
     *
     * @return string
     */
    public function getTableName()
    {
        $tableName = sprintf('%s_%s_%d',
            app()->make(Goods::class)->getTable(),
            $this->getProvider(),
            $this->getShopID()
        );

        if ($this->isTemporary()) {
            $tableName .= '_tmp';
        }

        return $tableName;
    }
}