<?php
/**
 * Besmart Video Slide model
 */

if (!defined('_PS_VERSION_')) {
    exit;
}


class BesmartVideoSlide extends ObjectModel

{
    public const PLACEMENT_SMALL = 'small_sequence';
    public const PLACEMENT_LARGE = 'large_sequence';

    /** @var bool */
    public $active;

    /** @var int */
    public $position;

    /** @var string */
    public $placement;

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
            'placement' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 32],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'desktop_video' => ['type' => self::TYPE_STRING, 'lang' => true, 'required' => true, 'validate' => 'isCleanHtml'],
            'mobile_video' => ['type' => self::TYPE_STRING, 'lang' => true, 'required' => true, 'validate' => 'isCleanHtml'],
            'button_label' => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml'],
            'button_url' => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isUrl'],
        ],
    ];

    public function add($autoDate = true, $nullValues = false)
    {
        // jeśli pozycja nie jest ustawiona – nadaj kolejną
        if (!Validate::isUnsignedInt($this->position)) {
            $this->position = self::getMaxPosition($this->placement ?: self::PLACEMENT_SMALL) + 1;
        }

        if (!$this->placement) {
            $this->placement = self::PLACEMENT_SMALL;
        }

        return parent::add($autoDate, $nullValues);
    }

    public static function getActiveSlides(int $idLang, string $placement = self::PLACEMENT_SMALL): array
    {
        $sql = new DbQuery();
        $sql->select('s.`id_slide`, s.`position`, sl.`desktop_video`, sl.`mobile_video`, sl.`button_label`, sl.`button_url`');
        $sql->from(self::$definition['table'], 's');
        $sql->leftJoin(self::$definition['table'] . '_lang', 'sl', 's.`id_slide` = sl.`id_slide` AND sl.`id_lang` = ' . (int) $idLang);
        $sql->where('s.`active` = 1');
        $sql->where('s.`placement` = "' . pSQL($placement) . '"');
        $sql->orderBy('s.`position` ASC');

        return Db::getInstance()->executeS($sql) ?: [];
    }

    public static function getMaxPosition(string $placement = self::PLACEMENT_SMALL): int
    {
        $sql = new DbQuery();
        $sql->select('MAX(`position`)');
        $sql->from(self::$definition['table']);
        $sql->where('`placement` = "' . pSQL($placement) . '"');

        return (int) Db::getInstance()->getValue($sql);
    }

    public static function cleanPositions(string $placement = self::PLACEMENT_SMALL): bool
    {
        $slides = Db::getInstance()->executeS(
            'SELECT `id_slide` FROM `' . _DB_PREFIX_ . 'besmartvideoslider_slides` WHERE `placement` = "' . pSQL($placement) . '" ORDER BY `position` ASC'
        );

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
