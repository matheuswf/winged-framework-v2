<?php

WingedConfig::$USE_PREPARED_STMT = post('use_stmt') ? get_defined_constants()[post('use_stmt')] : null;
WingedConfig::$DB_DRIVER = post('dbtype') ? get_defined_constants()[post('dbtype')] : false;
WingedConfig::$STD_DB_CLASS = post('class') ? get_defined_constants()[post('class')] : false;

if (WingedConfig::$USE_PREPARED_STMT === null || !WingedConfig::$DB_DRIVER || !WingedConfig::$STD_DB_CLASS) {
    echo json_encode(['status' => false]);
} else {
    Connections::newDb((new Database())->connect([
        'host' => post('host'),
        'user' => post('user'),
        'dbname' => post('dbname'),
        'password' => post('password'),
    ]), 'teste_connection', true);
    if (!CurrentDB::$current->db) {
        echo json_encode(['status' => false]);
    } else {
        echo json_encode(['status' => true]);
    }
}




