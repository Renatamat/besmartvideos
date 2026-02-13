<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_1_0($module)
{
    $table = _DB_PREFIX_ . 'besmartvideoslider_slides';

    $columnExists = (bool) Db::getInstance()->getValue(
        'SHOW COLUMNS FROM `' . pSQL($table) . '` LIKE "placement"'
    );

    if (!$columnExists) {
        $sql = 'ALTER TABLE `' . pSQL($table) . '`
            ADD COLUMN `placement` VARCHAR(32) NOT NULL DEFAULT "small_sequence" AFTER `position`';

        if (!Db::getInstance()->execute($sql)) {
            return false;
        }
    }

    $updateSql = 'UPDATE `' . pSQL($table) . '`
        SET `placement` = "small_sequence"
        WHERE `placement` = "" OR `placement` IS NULL';

    if (!Db::getInstance()->execute($updateSql)) {
        return false;
    }

    if (!$module->registerHook('displayBesmartVideosLarge')) {
        return false;
    }

    return $module->installTab();
}
