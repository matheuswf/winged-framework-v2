<?php

class QueryBuilder
{

    protected $select_arr = array();
    protected $from_arr = '';
    protected $inner_arr = array();
    protected $left_arr = array();
    protected $right_arr = array();
    protected $having_arr = array();
    protected $group_arr = array();
    protected $distinct_var = false;
    protected $order_bys = array();
    protected $limit_arr = array();
    protected $main_alias = array();
    protected $execute_query = false;
    protected $clear_after = true;
    protected $command = false;
    protected $into_arr = false;
    protected $update_arr = [];
    protected $insert_arr = [];
    protected $delete_arr = [];
    protected $set_arr = [];
    protected $values_arr = [];

    protected $where_order = array();

    protected $queries = array();

    public function select($select = array())
    {
        $this->command = 'select';
        $this->select_arr = $select;
        return $this;
    }

    /**
     * Example: ['alias' => 'table_name', 'alias' => 'table_name', 'table_name', 'table_name']
     * @param array $update
     * @return $this
     */
    public function update($update = array())
    {
        $this->command = 'update';
        $this->update_arr = $update;
        return $this;
    }

    /**
     * @return $this
     */
    public function insert()
    {
        $this->command = 'insert';
        return $this;
    }

    /**
     * @param $table_name
     * @return $this
     */
    public function into($table_name)
    {
        if(!CurrentDB::tableExists($table_name)){
            Winged::fatalError(__CLASS__, "Table " . $table_name . " no exists in database " . WingedConfig::$DBNAME, true);
        }
        if (is_string($table_name)) {
            $this->into_arr = $table_name;
        }
        return $this;
    }

    /**
     * Example: after call of method into() call with ['field_name' => 'value']
     * @param array $values
     * @return $this
     */
    public function values($values = array())
    {
        $this->values_arr = $values;
        return $this;
    }

    /**
     * Example: ['alias' => 'table_name', 'alias' => 'table_name', 'table_name', 'table_name']
     * @param array $delete
     * @return $this
     */
    public function delete($delete = array())
    {
        $this->command = 'delete';
        $this->delete_arr = $delete;
        return $this;
    }

    /**
     * Example: after call of method into() call with ['field_name' => 'value']
     * @param array $set
     * @return $this
     */
    public function set($set = array())
    {
        $this->set_arr = $set;
        return $this;
    }

    /**
     * Example: use to select distinct
     * @param bool $option
     * @return $this
     */
    public function distinct($option = true)
    {
        $this->distinct_var = $option;
        return $this;
    }

    /**
     * Example: ['alias' => 'table_name']
     * @param array $from
     * @return $this
     */
    public function from($from = array())
    {
        if (count($from) > 0) {
            $key = array_keys($from);
            if (is_string($key[0]) && is_string($from[$key[0]]) && $this->from_arr == '') {
                if(!CurrentDB::tableExists(trim($from[$key[0]]))){
                    Winged::fatalError(__CLASS__, "Table " . trim($from[$key[0]]) . " no exists in database " . WingedConfig::$DBNAME, true);
                }
                $this->from_arr = 'FROM ' . trim($from[$key[0]]) . ' AS ' . trim($key[0]) . '';
                $this->main_alias = array('alias' => trim($key[0]), 'table_name' => trim($from[$key[0]]));
            }
        }
        return $this;
    }

    /**
     * Example: ['alias' => 'table_name'], 'alias.field_name = alias.field_name'
     * @param array $inner
     * @param string $condition
     * @return $this
     */
    public function innerJoin($inner = array(), $condition = '')
    {
        if ($condition != '' && count($inner) > 0) {
            $key = array_keys($inner);
            if (is_string($key[0]) && is_string($key[0])) {
                if(!CurrentDB::tableExists(trim($inner[$key[0]]))){
                    Winged::fatalError(__CLASS__, "Table " . trim($inner[$key[0]]) . " no exists in database " . WingedConfig::$DBNAME, true);
                }
                $this->inner_arr[] = 'INNER JOIN ' . trim($inner[$key[0]]) . ' AS ' . trim($key[0]) . ' ON ' . trim($condition);
            }
        }
        return $this;
    }

    /**
     * Example: ['alias' => 'table_name'], 'alias.field_name = alias.field_name'
     * @param array $inner
     * @param string $condition
     * @return $this
     */
    public function leftJoin($inner = array(), $condition = '')
    {
        if ($condition != '' && count($inner) > 0) {
            $key = array_keys($inner);
            if (is_string($key[0]) && is_string($key[0])) {
                if(!CurrentDB::tableExists(trim($inner[$key[0]]))){
                    Winged::fatalError(__CLASS__, "Table " . trim($inner[$key[0]]) . " no exists in database " . WingedConfig::$DBNAME, true);
                }
                $this->left_arr[] = 'LEFT JOIN ' . trim($inner[$key[0]]) . ' AS ' . trim($key[0]) . ' ON ' . trim($condition);
            }
        }
        return $this;
    }

    /**
     * Example: ['alias' => 'table_name'], 'alias.field_name = alias.field_name'
     * @param array $inner
     * @param string $condition
     * @return $this
     */
    public function rightJoin($inner = array(), $condition = '')
    {
        if ($condition != '' && count($inner) > 0) {
            $key = array_keys($inner);
            if (is_string($key[0])) {
                if(!CurrentDB::tableExists(trim($inner[$key[0]]))){
                    Winged::fatalError(__CLASS__, "Table " . trim($inner[$key[0]]) . " no exists in database " . WingedConfig::$DBNAME, true);
                }
                $this->right_arr[] = 'RIGHT JOIN ' . trim($inner[$key[0]]) . ' AS ' . trim($key[0]) . ' ON ' . trim($condition);
            }
        }
        return $this;
    }

    /**
     * Example: DbDict::EQUALS, ['alias.field_name' => 'value']
     * @param string $condition
     * @param array $args
     * @return $this
     */
    public function having($condition = '', $args = array())
    {
        if ($condition != '' && count($args) > 0) {
            $key = array_keys($args);
            if (is_string($key[0])) {
                $this->having_arr[] = array('type' => 'beggin', 'condition' => $condition, 'args' => $args, 'key' => trim($key[0]), 'value' => trim($args[$key[0]]));
            }
        }
        return $this;
    }

    /**
     * Example: DbDict::EQUALS, ['alias.field_name' => 'value']
     * @param string $condition
     * @param array $args
     * @return $this
     */
    public function andHaving($condition = '', $args = array())
    {
        if ($condition != '' && count($args) > 0) {
            $key = array_keys($args);
            if (is_string($key[0])) {
                $this->having_arr[] = array('type' => 'and', 'condition' => $condition, 'args' => $args, 'key' => trim($key[0]), 'value' => trim($args[$key[0]]));
            }
        }
        return $this;
    }

    /**
     * Example: DbDict::EQUALS, ['alias.field_name' => 'value']
     * @param string $condition
     * @param array $args
     * @return $this
     */
    public function orHaving($condition = '', $args = array())
    {
        if ($condition != '' && count($args) > 0) {
            $key = array_keys($args);
            if (is_string($key[0])) {
                $this->having_arr[] = array('type' => 'or', 'condition' => $condition, 'args' => $args, 'key' => trim($key[0]), 'value' => trim($args[$key[0]]));
            }
        }
        return $this;
    }

    /**
     * @param string $field_name
     * @return $this
     */
    public function addGroupBy($field_name)
    {
        $this->group_arr[] = $field_name;
        return $this;
    }

    /**
     * @param array $fields
     * @return $this
     */
    public function groupBy($fields = array())
    {
        $this->group_arr = $fields;
        return $this;
    }

    /**
     * Example: DbDict::EQUALS, ['alias.field_name' => 'value']
     * Example: if you need a sub select or explicit argument without commas in query call with -> param, param, $extra = DbDict::SUB_SELECT | DbDict::ARGUMENT
     * @param string $condition
     * @param array $args
     * @param string $extra
     * @return $this
     */
    public function where($condition = '', $args = array(), $extra = null)
    {
        $condition = trim($condition);
        if ($condition != '' && count($args) > 0) {
            if ($condition == DbDict::BETWEEN) {
                if (count($args) == 3) {
                    $this->where_order[] = array('type' => 'beggin', 'condition' => DbDict::BETWEEN, 'args' => $args, 'key' => trim($args[0]), 'value' => array($args[1], $args[2]), 'extra' => $extra);
                } else {
                    Winged::push_warning(__CLASS__, "Between requires exactly three args in array. Given: " . count($args), true);
                }
            } else if ($condition == DbDict::NOTIN || $condition == DbDict::IN) {
                $key = array_keys($args);
                if (is_string($key[0])) {
                    if (is_array($args[$key[0]])) {
                        $this->where_order[] = array('type' => 'beggin', 'condition' => $condition, 'args' => $args, 'key' => trim($key[0]), 'value' => $args[$key[0]], 'extra' => $extra);
                    } else {
                        Winged::push_warning(__CLASS__, "" . $condition . " requires an array in associative args array. Given: " . gettype($args[$key[0]]), true);
                    }
                }
            } else {
                $key = array_keys($args);
                if (is_string($key[0])) {
                    $this->where_order[] = array('type' => 'beggin', 'condition' => $condition, 'args' => $args, 'key' => trim($key[0]), 'value' => trim($args[$key[0]]), 'extra' => $extra);
                }
            }
        }
        return $this;
    }

    /**
     * Example: DbDict::EQUALS, ['alias.field_name' => 'value']
     * Example: if you need a sub select or explicit argument without commas in query call with -> param, param, $extra = DbDict::SUB_SELECT | DbDict::ARGUMENT
     * @param string $condition
     * @param array $args
     * @param string $extra
     * @return $this
     */
    public function andWhere($condition = '', $args = array(), $extra = null)
    {
        $condition = trim($condition);
        if ($condition != '' && count($args) > 0) {
            if ($condition == DbDict::BETWEEN) {
                if (count($args) == 3) {
                    $this->where_order[] = array('type' => 'and', 'condition' => DbDict::BETWEEN, 'args' => $args, 'key' => trim($args[0]), 'value' => array($args[1], $args[2]), 'extra' => $extra);
                } else {
                    Winged::push_warning(__CLASS__, "Between requires exactly three args in array. Given: " . count($args), true);
                }
            } else if ($condition == DbDict::NOTIN || $condition == DbDict::IN) {
                $key = array_keys($args);
                if (is_string($key[0])) {
                    if (is_array($args[$key[0]])) {
                        $this->where_order[] = array('type' => 'and', 'condition' => $condition, 'args' => $args, 'key' => trim($key[0]), 'value' => $args[$key[0]], 'extra' => $extra);
                    } else {
                        Winged::push_warning(__CLASS__, "" . $condition . " requires an array in associative args array. Given: " . gettype($args[$key[0]]), true);
                    }
                }
            } else {
                $key = array_keys($args);
                if (is_string($key[0])) {
                    $this->where_order[] = array('type' => 'and', 'condition' => $condition, 'args' => $args, 'key' => trim($key[0]), 'value' => trim($args[$key[0]]), 'extra' => $extra);
                }
            }
        }
        return $this;
    }

    /**
     * Example: DbDict::EQUALS, ['alias.field_name' => 'value']
     * Example: if you need a sub select or explicit argument without commas in query call with -> param, param, $extra = DbDict::SUB_SELECT | DbDict::ARGUMENT
     * @param string $condition
     * @param array $args
     * @param string $extra
     * @return $this
     */
    public function orWhere($condition = '', $args = array(), $extra = null)
    {
        $condition = trim($condition);
        if ($condition != '' && count($args) > 0) {
            if ($condition == DbDict::BETWEEN) {
                if (count($args) == 3) {
                    $this->where_order[] = array('type' => 'or', 'condition' => DbDict::BETWEEN, 'args' => $args, 'key' => trim($args[0]), 'value' => array($args[1], $args[2]), 'extra' => $extra);
                } else {
                    Winged::push_warning(__CLASS__, "Between requires exactly three args in array. Given: " . count($args), true);
                }
            } else if ($condition == DbDict::NOTIN || $condition == DbDict::IN) {
                $key = array_keys($args);
                if (is_string($key[0])) {
                    if (is_array($args[$key[0]])) {
                        $this->where_order[] = array('type' => 'or', 'condition' => $condition, 'args' => $args, 'key' => trim($key[0]), 'value' => $args[$key[0]], 'extra' => $extra);
                    } else {
                        Winged::push_warning(__CLASS__, "" . $condition . " requires an array in associative args array. Given: " . gettype($args[$key[0]]), true);
                    }
                }
            } else {
                $key = array_keys($args);
                if (is_string($key[0])) {
                    $this->where_order[] = array('type' => 'or', 'condition' => $condition, 'args' => $args, 'key' => trim($key[0]), 'value' => trim($args[$key[0]]), 'extra' => $extra);
                }
            }
        }
        return $this;
    }


    /**
     * @param string | array $direction
     * @param string $field
     * @return $this;
     */
    public function orderBy($direction = '', $field = '')
    {
        if (is_array($direction) && count($direction) == 2) {
            $field = trim($direction[1]);
            $direction = $direction[0];
        }
        $direction = trim($direction);
        if ($direction == DbDict::ASC || $direction == DbDict::DESC) {
            $this->order_bys[] = array($direction, $field);
        }
        return $this;
    }

    /**
     * @param string | array $direction
     * @param string $field
     * @return $this;
     */
    public function addOrderBy($direction = '', $field = '')
    {
        if (is_array($direction) && count($direction) == 2) {
            $field = trim($direction[1]);
            $direction = $direction[0];
        }
        $direction = trim($direction);
        if ($direction == DbDict::ASC || $direction == DbDict::DESC) {
            $this->order_bys[] = array($direction, $field);
        }
        return $this;
    }

    /**
     * @param int $initial_or_count
     * @param int $final
     * @return $this
     */
    public function limit($initial_or_count, $final = 0)
    {
        $initial_or_count = intval($initial_or_count);
        $final = intval($final);

        if (is_int($initial_or_count) && is_int($final)) {
            $this->limit_arr = array('init' => $initial_or_count, 'final' => $final);
        }
        return $this;
    }

    protected function findNextWhereOrder($pos)
    {
        $next = false;
        foreach ($this->where_order as $key => $value) {
            if ($next) {
                return $value;
            }
            if ($pos == $key) {
                $next = true;
            }
        }
        return false;
    }

}