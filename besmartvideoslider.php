<?php
/**
 * Besmart Video Slider Module
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'besmartvideoslider/classes/BesmartVideoSlide.php';

class Besmartvideoslider extends Module
{
    public function __construct()
    {
        $this->name = 'besmartvideoslider';
        $this->tab = 'front_office_features';
        $this->version = '1.1.1';
        $this->author = 'BeSmart';
        $this->bootstrap = true;
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '8.0.0', 'max' => _PS_VERSION_];

        parent::__construct();

        $this->displayName = $this->l('BeSmart Video Slider');
        $this->description = $this->l('Responsive video slider compatible with Swiper.');
    }

    public function install()
    {
        return parent::install()
            && Configuration::updateValue('BESMARTVIDEOSLIDER_ENABLED', 1)
            && $this->registerHook('displayHome')
            && $this->registerHook('displayBesmartVideosLarge')
            && $this->registerHook('displayTopColumn')
            && $this->registerHook('actionFrontControllerSetMedia')
            && $this->registerHook('actionAdminControllerSetMedia')
            && $this->installTab()
            && $this->createTables()
            && $this->createVideoDirectory();
    }

    public function uninstall()
    {
        return $this->removeTables()
            && $this->uninstallTab()
            && Configuration::deleteByName('BESMARTVIDEOSLIDER_ENABLED')
            && parent::uninstall();
    }

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submitBesmartvideosliderConfig')) {
            Configuration::updateValue('BESMARTVIDEOSLIDER_ENABLED', (int) Tools::getValue('BESMARTVIDEOSLIDER_ENABLED'));
            $output .= $this->displayConfirmation($this->l('Settings updated.'));
        }

        $fieldsForm = [
            'form' => [
                'legend' => [
                    'title' => $this->l('General settings'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable slider'),
                        'name' => 'BESMARTVIDEOSLIDER_ENABLED',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'enabled_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'enabled_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
                'buttons' => [
                    [
                        'type' => 'link',
                        'title' => $this->l('Manage videos (Small)'),
                        'icon' => 'process-icon-cogs',
                        'href' => $this->getAdminLink('AdminBesmartVideoSlider'),
                    ],
                    [
                        'type' => 'link',
                        'title' => $this->l('Manage videos (Large)'),
                        'icon' => 'process-icon-cogs',
                        'href' => $this->getAdminLink('AdminBesmartVideoSliderLarge'),
                    ],
                ],
            ],
        ];

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitBesmartvideosliderConfig';
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->fields_value['BESMARTVIDEOSLIDER_ENABLED'] = Configuration::get('BESMARTVIDEOSLIDER_ENABLED');

        return $output . $helper->generateForm([$fieldsForm]);
    }

    public function hookActionFrontControllerSetMedia()
    {
        if (!Configuration::get('BESMARTVIDEOSLIDER_ENABLED')) {
            return;
        }

        $this->context->controller->registerJavascript(
            'module-besmartvideoslider-slider',
            'modules/' . $this->name . '/views/js/slider.js',
            [
                'position' => 'bottom',
                'priority' => 200,
            ]
        );

        $this->context->controller->registerStylesheet(
            'module-besmartvideoslider-slider',
            'modules/' . $this->name . '/views/css/slider.css',
            [
                'media' => 'all',
                'priority' => 200,
            ]
        );
    }

    public function hookActionAdminControllerSetMedia()
    {
        $controller = Tools::getValue('controller');
        if (!in_array($controller, ['AdminBesmartVideoSlider', 'AdminBesmartVideoSliderLarge'], true)) {
            return;
        }

        $this->context->controller->addJS(_PS_JS_DIR_ . 'jquery/ui/jquery.ui.sortable.min.js');
        $this->context->controller->addJS(_PS_JS_DIR_ . 'admin-dnd.js');
    }

    public function hookDisplayHome($params)
    {
        return $this->renderSlider(BesmartVideoSlide::PLACEMENT_SMALL, 'small', 'views/templates/hook/slider.tpl');
    }

    public function hookDisplayBesmartVideosLarge($params)
    {
        return $this->renderSlider(BesmartVideoSlide::PLACEMENT_LARGE, 'large', 'views/templates/hook/large.tpl');
    }

    public function hookDisplayTopColumn($params)
    {
        return $this->renderSlider(BesmartVideoSlide::PLACEMENT_LARGE, 'large', 'views/templates/hook/large.tpl');
    }

    private function renderSlider(string $placement, string $variant, string $template)
    {
        if (!Configuration::get('BESMARTVIDEOSLIDER_ENABLED')) {
            return '';
        }

        $idLang = (int) $this->context->language->id;
        $slides = BesmartVideoSlide::getActiveSlides($idLang, $placement);

        foreach ($slides as &$slide) {
            $slide['desktop_video_src'] = $this->resolveVideoPath($slide['desktop_video'] ?? '');
            $slide['mobile_video_src'] = $this->resolveVideoPath($slide['mobile_video'] ?? '');
            $slide['desktop_poster_src'] = $this->resolvePosterPath($slide['desktop_video'] ?? '');
            $slide['mobile_poster_src'] = $this->resolvePosterPath($slide['mobile_video'] ?? '');
        }
        unset($slide);

        $this->context->smarty->assign([
            'besmartSliderSlides' => $slides,
            'besmartSliderModulePath' => $this->_path,
            'besmartSliderPlacement' => $placement,
            'besmartSliderVariant' => $variant,
        ]);

        return $this->display(__FILE__, $template);
    }

    private function createTables()
    {
        $queries = [];

        $queries[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'besmartvideoslider_slides` (
            `id_slide` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `active` TINYINT(1) NOT NULL DEFAULT 1,
            `position` INT UNSIGNED NOT NULL DEFAULT 0,
            `placement` VARCHAR(32) NOT NULL DEFAULT "small_sequence",
            `date_add` DATETIME NOT NULL,
            `date_upd` DATETIME NOT NULL,
            PRIMARY KEY (`id_slide`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

        $queries[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'besmartvideoslider_slides_lang` (
            `id_slide` INT UNSIGNED NOT NULL,
            `id_lang` INT UNSIGNED NOT NULL,
            `desktop_video` VARCHAR(255) NOT NULL,
            `mobile_video` VARCHAR(255) NOT NULL,
            `description` TEXT NULL,
            `button_label` VARCHAR(255) NOT NULL,
            `button_url` VARCHAR(255) NOT NULL,
            PRIMARY KEY (`id_slide`, `id_lang`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

        foreach ($queries as $query) {
            if (!Db::getInstance()->execute($query)) {
                return false;
            }
        }

        return true;
    }

    private function createVideoDirectory(): bool
    {
        $directory = _PS_MODULE_DIR_ . $this->name . '/videos/';

        if (!is_dir($directory)) {
            return mkdir($directory, 0755, true);
        }

        return true;
    }

    private function removeTables()
    {
        $queries = [
            'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'besmartvideoslider_slides_lang`',
            'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'besmartvideoslider_slides`',
        ];

        foreach ($queries as $query) {
            if (!Db::getInstance()->execute($query)) {
                return false;
            }
        }

        return true;
    }

    public function installTab(): bool
    {
        return $this->createTab('AdminBesmartVideoSlider', $this->l('Videos (Small)'))
            && $this->createTab('AdminBesmartVideoSliderLarge', $this->l('Videos (Large)'));
    }

    private function createTab(string $className, string $tabName): bool
    {
        $existingId = (int) Tab::getIdFromClassName($className);
        if ($existingId) {
            return true;
        }

        $parentId = (int) Tab::getIdFromClassName('IMPROVE');
        if (!$parentId) {
            $parentId = (int) Tab::getIdFromClassName('AdminParentModulesSf');
        }

        $tab = new Tab();
        $tab->class_name = $className;
        $tab->id_parent = $parentId;
        $tab->module = $this->name;
        $tab->active = 1;

        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $tabName;
        }

        return (bool) $tab->add();
    }

    private function uninstallTab(): bool
    {
        $tabClassNames = ['AdminBesmartVideoSliderLarge', 'AdminBesmartVideoSlider'];

        foreach ($tabClassNames as $className) {
            $idTab = (int) Tab::getIdFromClassName($className);
            if (!$idTab) {
                continue;
            }

            $tab = new Tab($idTab);
            if (!(bool) $tab->delete()) {
                return false;
            }
        }

        return true;
    }

    private function getAdminLink(string $controller)
    {
        return Context::getContext()->link->getAdminLink($controller);
    }

    private function resolveVideoPath(string $path): string
    {
        if ($path === '') {
            return '';
        }

        if (preg_match('#^(https?:)?//#', $path) || strpos($path, '/') === 0) {
            return $path;
        }

        return $this->_path . 'videos/' . ltrim($path, '/');
    }

    private function resolvePosterPath(string $videoPath): string
    {
        if ($videoPath === '') {
            return '';
        }

        $resolvedVideoPath = $this->resolveVideoPath($videoPath);
        $extensionPosition = strrpos($resolvedVideoPath, '.');

        if ($extensionPosition === false) {
            return $resolvedVideoPath . '.jpg';
        }

        return substr($resolvedVideoPath, 0, $extensionPosition) . '.jpg';
    }
}
