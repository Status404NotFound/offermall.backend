<?php

namespace webmaster\traits;


trait TotalTrait
{
    /**
     * @param $rows
     * @param array $fields
     * @return array
     */
    public function getTotalRow($rows, $fields = []): array
    {
        if (empty($rows)) return [];

        $total = [];
        foreach ($rows as $order) {

            if (isset($fields)) {
                $order = array_diff_key($order, array_flip($fields));
            }

            foreach ($order as $key => $row) {
                if (isset($total[$key])) {
                    $total[$key] += $row;
                } else {
                    $total[$key] = $row;
                }
            }
        }

        return $total;
    }
}