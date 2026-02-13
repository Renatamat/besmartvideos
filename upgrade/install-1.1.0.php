<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_1_0($module)
{
    $sql = 'ALTER TABLE `' . _DB_PREFIX_ . 'besmartvideoslider_slides`
        ADD COLUMN IF NOT EXISTS `placement` VARCHAR(32) NOT NULL DEFAULT "small_sequence" AFTER `position`';

    if (!Db::getInstance()->execute($sql)) {
        return false;
    }

    $updateSql = 'UPDATE `' . _DB_PREFIX_ . 'besmartvideoslider_slides`
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
