<?phpclass Migrate{    public function __construct()    {        _Database::init();    }    public function createTableIfNotExists($args = array(), $echo = false)    {        if (!empty($args)) {            if (!array_key_exists('table_name', $args)) {                $warn = Winged::push_warning(__CLASS__, 'Table name not specified in args array.', true);                winged_error_handler('8', $warn['error_description'], __FILE__, 'in class : ' . __LINE__, $warn['real_backtrace']);                Winged::get_errors(__LINE__, __FILE__);            } else {                if ($this->tableExists(strtolower($args['table_name'])) && !$echo) {                    return false;                } else {                    $create_str = 'CREATE TABLE IF NOT EXISTS ' . $args['table_name'] . '[pk field]';                    $reserved = array('engine', 'charset');                    $engine = "ENGINE=InnoDB";                    $charset = "DEFAULT CHARSET=utf8";                    $pk = false;                    $fks = array();                    $not_first = 0;                    foreach ($args as $key => $value) {                        if ((!in_array(strtolower($key), $reserved) && is_array($value)) || ($value === 'safe' || $value === true)) {                            $type = 'VARCHAR';                            $length = '(255)';                            $is_null = 'NULL';                            $auto = '';                            $default = '';                            $type_length = '';                            $types = array('date', 'timestamp', 'text', 'blob', 'longblob', 'boolean', 'bool');                            if (array_key_exists('type', $value)) {                                $type = strtoupper(trim($value['type']));                            }                            if (array_key_exists('length', $value)) {                                $length = trim($value['length']);                                if ($length[0] != '(') {                                    $length = '(' . $length;                                }                                if ($length[strlen($length) - 1] != ')') {                                    $length .= ')';                                }                            }                            if (array_key_exists('null', $value)) {                                if (strtolower($value['null']) == 'null') {                                    $is_null = 'NULL';                                }                                if (strtolower($value['null']) == 'not null') {                                    $is_null = 'NOT NULL';                                }                                if (is_bool($value['null']) && $value['null']) {                                    $is_null = 'NULL';                                }                                if (is_bool($value['null']) && !$value['null']) {                                    $is_null = 'NOT NULL';                                }                            }                            if (array_key_exists('default', $value)) {                                $default = ' DEFAULT "' . $value['default'] . '"';                            }                            if (array_key_exists('auto', $value)) {                                $auto = ' AUTO_INCREMENT';                            }                            if (array_key_exists('pk', $value) && !$pk) {                                $pk = strtolower($key);                            }                            if (array_key_exists('fk', $value)) {                                $fks[strtolower($key)] = array();                                if (array_key_exists('reference_to', $value) && $value['reference_to'] !== '') {                                    $fks[strtolower($key)]['reference_to'] = $value['reference_to'];                                } else {                                    $warn = Winged::push_warning(__CLASS__, '' . $key . ' field is set to FK , however no table was set as a reference.', true);                                    winged_error_handler('8', $warn['error_description'], __FILE__, 'in class : ' . __LINE__, $warn['real_backtrace']);                                    Winged::get_errors(__LINE__, __FILE__);                                }                                if (array_key_exists('reference_field', $value) && $value['reference_field'] !== '') {                                    $fks[strtolower($key)]['reference_field'] = $value['reference_field'];                                } else {                                    $warn = Winged::push_warning(__CLASS__, '' . $key . ' field is set to FK , however no field was set as a reference for table ' . $value['reference_to'] . '', true);                                    winged_error_handler('8', $warn['error_description'], __FILE__, 'in class : ' . __LINE__, $warn['real_backtrace']);                                    Winged::get_errors(__LINE__, __FILE__);                                }                                if (array_key_exists('on_delete', $value) && $value['on_delete'] !== '') {                                    $fks[strtolower($key)]['on_delete'] = $value['on_delete'];                                }                                if (array_key_exists('on_update', $value) && $value['on_update'] !== '') {                                    $fks[strtolower($key)]['on_update'] = $value['on_update'];                                }                            }                            if (in_array(strtolower($type), $types)) {                                $type_length = " " . $type . " ";                            } else {                                $type_length = " " . $type . " " . $length . " ";                            }                            $entire_string = strtolower($key) . $type_length . $is_null . $auto . $default;                            if ($not_first === 0) {                                $not_first++;                                $create_str .= '(' . $entire_string;                            } else {                                $create_str .= ', ' . $entire_string;                            }                        }                    }                    $exp = explode('[pk field]', $create_str);                    if ($not_first == 0 || !$pk) {                        $create_str = $exp[0] . '(id INT NOT NULL AUTO_INCREMENT, PRIMARY KEY(id)';                    } else if ($not_first > 0 && !$pk) {                        $create_str = $exp[0] . 'id INT NOT NULL AUTO_INCREMENT, ' . $exp[1] . ', PRIMARY KEY(id)';                    } else if ($not_first > 0 && $pk) {                        $create_str = $exp[0] . $exp[1] . ', PRIMARY KEY(' . $pk . ')';                    }                    if (count($fks) > 0) {                        foreach ($fks as $key => $value) {                            $create_str .= ', FOREIGN KEY (' . $key . ') REFERENCES ' . $value['reference_to'] . '(' . $value['reference_field'] . ')';                            if (array_key_exists('on_update', $value)) {                                $create_str .= ' ON UPDATE ' . strtoupper($value['on_update']);                            }                            if (array_key_exists('on_delete', $value)) {                                $create_str .= ' ON DELETE ' . strtoupper($value['on_delete']);                            }                        }                    }                    $create_str .= ') ' . $engine . ' ' . $charset . ';';                    if($echo){                        return $create_str;                    }                    return _Database::execute($create_str);                }            }        } else {            $warn = Winged::push_warning(__CLASS__, 'Empty table args.', true);            winged_error_handler('8', $warn['error_description'], __FILE__, 'in class : ' . __LINE__, $warn['real_backtrace']);            Winged::get_errors(__LINE__, __FILE__);        }    }    public function tableExists($tableName = '')    {        $count = _Database::fetch('SHOW TABLES LIKE "' . $tableName . '"');        if (count($count) > 0) {            return true;        }        return false;    }}