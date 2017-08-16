<?php
/**
 * Created by PhpStorm.
 * User: tsingsun
 * Date: 2017/8/7
 * Time: 上午11:11
 */

namespace yii\messenger\base;

/**
 * 消息策略
 * @package yii\messenger\base
 */
interface StrategyInterface
{
    /**
     * Apply the strategy and return result.
     *
     * @param array $gateways
     *
     * @return array
     */
    public function apply(array $gateways);
}