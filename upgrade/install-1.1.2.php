<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_1_2($module)
{
    $langTable = _DB_PREFIX_ . 'besmartvideoslider_slides_lang';

    $columnExists = (int) Db::getInstance()->getValue(
        'SELECT COUNT(*)
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = "' . pSQL(_DB_NAME_) . '"
        AND TABLE_NAME = "' . pSQL($langTable) . '"
        AND COLUMN_NAME = "button_label_category"'
    );

    if ($columnExists === 0) {
        $sql = 'ALTER TABLE `' . pSQL($langTable) . '`
            ADD COLUMN `button_label_category` VARCHAR(255) NULL AFTER `button_label`';

        if (!Db::getInstance()->execute($sql)) {
            return false;
        }
    }

    return true;
}
