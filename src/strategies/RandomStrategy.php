<?php
/**
 * Created by PhpStorm.
 * User: tsingsun
 * Date: 2017/8/7
 * Time: 上午11:16
 */

namespace yii\messenger\strategies;


use yii\messenger\base\StrategyInterface;

class RandomStrategy implements StrategyInterface
{
    /**
     * Apply the strategy and return result.
     *
     * @param array $gateways
     *
     * @return array
     */
    public function apply(array $gateways)
    {
        return array_rand($gateways,count($gateways)-1);
    }
}