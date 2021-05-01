<?php
/**
 * Created by PhpStorm.
 * User: evild
 * Date: 9/3/18
 * Time: 5:01 PM
 */

namespace common\components\joinMap;

use Yii;
use yii\db\ActiveQuery;
use yii\db\TableSchema;

/**
 * JoinMap
 *
 * @property TableSchema read-only $_main_schema
 * @property array read-only $_join_map
 *
 */

class JoinMap
{
    private $_main_schema;
    private $_join_map = [];

    public const INNER = 'INNER JOIN';
    public const RIGHT = 'RIGHT JOIN';
    public const LEFT = 'LEFT JOIN';

    /**
     * JoinMap constructor.
     *
     * @param ActiveQuery $query
     * @param array       $join_map
     *
     * @throws JoinMapException
     */
    public function __construct(ActiveQuery $query, array $join_map)
    {
        $main_table_name = \call_user_func([$query->modelClass, 'tableName']);

        if ( !$this->_main_schema = Yii::$app->db->schema->getTableSchema($main_table_name)) {
            throw new JoinMapException("Main table '$main_table_name' doesn't exist");
        }

        if ( !$join_map) {
            throw new JoinMapException('Dependencies must contain at least one table');
        }

        foreach ($join_map as $tables_params) {
            $this->_addTableToJoinMap($tables_params[0], $tables_params[1], $tables_params[2], $tables_params[3], $tables_params[4], $tables_params[5] ?? null);
        }
    }

    /**
     * @param string $join_type
     * @param string $table_1
     * @param string $column_1
     * @param string $table_2
     * @param string $column_2
     * @param null   $extension
     */
    private function _addTableToJoinMap(string $join_type, string $table_1, string $column_1, string $table_2, string $column_2, $extension = null): void
    {
        $this->_join_map[$table_1]['is_joined'] = false;
        $this->_join_map[$table_1]['join_type'] = $join_type;
        $this->_join_map[$table_1]['on_params'] = ['table_1' => $table_1, 'column_1' => $column_1, 'table_2' => $table_2, 'column_2' => $column_2];
        $this->_join_map[$table_1]['extension'] = (string)$extension;
    }

    /**
     * @param ActiveQuery $query
     * @param string      $table_name_to_join
     */
    public function join(ActiveQuery $query, string $table_name_to_join): void
    {
        if ($this->_join_map[$table_name_to_join]['is_joined'] !== true) {
            $join_type = $this->_join_map[$table_name_to_join]['join_type'];
            $extension = $this->_join_map[$table_name_to_join]['extension'];
            $on_params = $this->_join_map[$table_name_to_join]['on_params'];
            $on = $on_params['table_1'] . '.' . $on_params['column_1'] . ' = ' . $on_params['table_2'] . '.' . $on_params['column_2'];

            $query->join($join_type, $table_name_to_join ,$on, $extension);
            $this->_join_map[$table_name_to_join]['is_joined'] = true;
        }
    }

    ///**
    // * @param ActiveQuery $query
    // *
    // * @throws JoinMapException
    // */
    //public function initJoins(ActiveQuery $query): void
    //{
    //    $query_table_name = \call_user_func([$query->modelClass, 'tableName']);
    //
    //    if ($this->_main_schema === null || $this->_main_schema !== Yii::$app->db->schema->getTableSchema($query_table_name)) {
    //        throw new JoinMapException("'$query_table_name' table it's not the same main table what was be initialized");
    //    }
    //
    //    $hard_conditions = [];
    //    foreach ($query->where as $key => &$condition) {
    //        if (\is_array($condition)) {
    //            if (\count($condition) > 1) {
    //                foreach ($condition as $index => $params) {
    //                    $table_column_arr = explode('.', $params);
    //                    if (\count($table_column_arr) > 1) {
    //                        $hard_conditions[$key] = $condition;
    //                        $condition = [];
    //                        $condition[$params] = true;
    //                    }
    //                }
    //            }
    //
    //            foreach ($condition as $table_column => $value) {
    //                $inited_table_name = '';
    //                $this->_join_map[$query_table_name] = true;
    //
    //                foreach ($this->_join_map as $table_name => $params) {
    //                    $pos1 = stripos($table_column, $table_name);
    //
    //                    if ($pos1 !== false) {
    //                        $inited_table_name = $table_name;
    //                    }
    //                }
    //
    //                if ( !$inited_table_name || $inited_table_name === $query_table_name) {
    //                    continue;
    //                }
    //
    //                if ( !isset($this->_join_map[$inited_table_name])) {
    //                    throw new JoinMapException("'$inited_table_name' table by where condition wasn't be initialized in joinMap array");
    //                }
    //
    //                $joins = [];
    //                foreach ($this->_join_map as $table) {
    //                    $joins[$table['on_params']['table_1']] = $table['on_params']['table_2'];
    //                }
    //
    //                $join_map = $this->_buildJoinMap($joins, $inited_table_name, $query_table_name);
    //                foreach (array_reverse($join_map) as $table) {
    //                    $this->join($query, $table);
    //                }
    //            }
    //        }
    //    }
    //
    //    if ( !empty($hard_conditions)) {
    //        foreach ($hard_conditions as $index => $cond) {
    //            if (isset($query->where[$index])) {
    //                $query->where[$index] = $cond;
    //            }
    //        }
    //    }
    //}

    ///**
    // * @param array  $join_map
    // * @param string $table_name
    // * @param string $query_table_name
    // *
    // * @return array
    // */
    //private function _buildJoinMap(array $join_map, string $table_name, string $query_table_name): array
    //{
    //    static $road;
    //    if (empty($road)) {
    //        $road = [];
    //    }
    //
    //    if ($join_map[$table_name] !== $query_table_name) {
    //        $road[] = $table_name;
    //        $this->_buildJoinMap($join_map, $join_map[$table_name], $query_table_name);
    //
    //    } else {
    //        $road[] = $table_name;
    //    }
    //
    //    return $road;
    //}
}