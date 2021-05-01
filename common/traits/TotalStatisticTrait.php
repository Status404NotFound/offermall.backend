<?php

namespace common\traits;

trait TotalStatisticTrait
{
    /**
     * Calculating statistic total rows
     * @param $rows
     * @param array $fields (expected fields array)
     * @return array
     */
    public function getTotalRow($rows, $fields = []) : array
    {
        if (empty($rows)) return [];

        $total = [];
        $model = new self();
        foreach ($rows as $order) {

            $filtered = array_diff_key($order, array_flip($fields));

            foreach ($filtered as $row_name => $row) {
                if (isset($total[$row_name])) {
                    $total[$row_name] += $row;
                } else {
                    $total[$row_name] = $row;
                }
            }
        }

        $model->setAttributes($total);
        $model->setCalculatedAttributes();
        $total = $model->getAttributes();

        return $total;
    }
}