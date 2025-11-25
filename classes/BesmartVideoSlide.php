<?php
/**
 * Besmart Video Slide model
 */

if (!defined('_PS_VERSION_')) {
    exit;
}


class BesmartVideoSlide extends ObjectModel

{
    /** @var bool */
    public $active;

    /** @var int */
    public $position;

    /** @var string[] */
    public $desktop_video;

    /** @var string[] */
    public $mobile_video;

    /** @var string[] */
    public $button_label;

    /** @var string[] */
    public $button_url;

    /** @var string */
    public $date_add;

    /** @var string */
    public $date_upd;

    public static $definition = [
        'table' => 'besmartvideoslider_slides',
        'primary' => 'id_slide',
        'multilang' => true,
        'fields' => [
            'active' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true],
            'position' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'desktop_video' => ['type' => self::TYPE_STRING, 'lang' => true, 'required' => true, 'validate' => 'isGenericName'],
            'mobile_video' => ['type' => self::TYPE_STRING, 'lang' => true, 'required' => true, 'validate' => 'isGenericName'],
            'button_label' => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml'],
            'button_url' => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isUrl'],
        ],
    ];

    public function add($autoDate = true, $nullValues = false)
    {
        // jeśli pozycja nie jest ustawiona – nadaj kolejną
        if (!Validate::isUnsignedInt($this->position)) {
            $this->position = self::getMaxPosition() + 1;
        }

        return parent::add($autoDate, $nullValues);
    }
    
    public static function getActiveSlides(int $idLang): array
    {
        $sql = new DbQuery();
        $sql->select('s.`id_slide`, s.`position`, sl.`desktop_video`, sl.`mobile_video`, sl.`button_label`, sl.`button_url`');
        $sql->from(self::$definition['table'], 's');
        $sql->leftJoin(self::$definition['table'] . '_lang', 'sl', 's.`id_slide` = sl.`id_slide` AND sl.`id_lang` = ' . (int) $idLang);
        $sql->where('s.`active` = 1');
        $sql->orderBy('s.`position` ASC');

        return Db::getInstance()->executeS($sql) ?: [];
    }

    public static function getMaxPosition(): int
    {
        $sql = new DbQuery();
        $sql->select('MAX(`position`)');
        $sql->from(self::$definition['table']);

        return (int) Db::getInstance()->getValue($sql);
    }

    public static function cleanPositions(): bool
    {
        $slides = Db::getInstance()->executeS('SELECT `id_slide` FROM `' . _DB_PREFIX_ . 'besmartvideoslider_slides` ORDER BY `position` ASC');

        if (!$slides) {
            return true;
        }

        foreach ($slides as $index => $slide) {
            Db::getInstance()->update(
                self::$definition['table'],
                ['position' => (int) $index],
                'id_slide = ' . (int) $slide['id_slide']
            );
        }

        return true;
    }
}
