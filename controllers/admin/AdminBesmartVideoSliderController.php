<?php
/**
 * Admin controller for BeSmart Video Slider
 */

if (!defined('_PS_VERSION_')) {
    exit;
}
require_once _PS_MODULE_DIR_ . 'besmartvideoslider/classes/BesmartVideoSlide.php';


class AdminBesmartVideoSliderController extends ModuleAdminController
{
    protected $placement = BesmartVideoSlide::PLACEMENT_SMALL;

    public function __construct()
    {
        $this->table = 'besmartvideoslider_slides';
        $this->className = 'BesmartVideoSlide';
        $this->identifier = 'id_slide';
        $this->lang = true;
        $this->bootstrap = true;
        $this->position_identifier = 'id_slide';
        $this->position = true;
        $this->_defaultOrderBy = 'position';
        $this->_orderWay = 'ASC';
        $this->_where = ' AND a.`placement` = "' . pSQL($this->placement) . '"';

        // Ensure module instance exists before using l() which requires $this->module->name
        if ($this->module === null) {
            $this->module = Module::getInstanceByName('besmartvideoslider');
        }

        // Ensure translator is available before using $this->l() on PrestaShop 8+
        $this->translator = Context::getContext()->getTranslator();
        $this->fields_list = [
            'id_slide' => [
                'title' => $this->l('ID'),
                'align' => 'center',
                'filter_key' => 'a!id_slide',
                'class' => 'fixed-width-xs',
            ],
            'button_label' => [
                'title' => $this->l('Button label'),
                'filter_key' => 'b!button_label',
            ],
            'desktop_video' => [
                'title' => $this->l('Desktop video'),
                'filter_key' => 'b!desktop_video',
            ],
            'mobile_video' => [
                'title' => $this->l('Mobile video'),
                'filter_key' => 'b!mobile_video',
            ],
            'position' => [
                'title' => $this->l('Position'),
                'filter_key' => 'a!position',
                'position' => true,
                'align' => 'center',
            ],
            'active' => [
                'title' => $this->l('Status'),
                'active' => 'status',
                'type' => 'bool',
                'align' => 'center',
                'orderby' => false,
            ],
        ];
        $this->bulk_actions = [
            'delete' => [
                'text' => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected items?'),
            ],
        ];
        $this->list_no_link = true;
        $this->row_hover = true;
        $this->explicitSelect = true;
        $this->base_tpl_list = 'list.tpl';
        $this->base_tpl_form = 'form.tpl';

        parent::__construct();
    }

    public function initPageHeaderToolbar()
    {
        if (empty($this->display)) {
            $this->page_header_toolbar_btn['new_slide'] = [
                'href' => self::$currentIndex . '&add' . $this->table . '&token=' . $this->token,
                'desc' => $this->l('Add new slide'),
                'icon' => 'process-icon-new',
            ];
        }

        parent::initPageHeaderToolbar();
    }

   public function renderList()
{
    $this->_select = 'b.`button_label`, b.`desktop_video`, b.`mobile_video`, b.`description`';
    $this->_group = '';

    $this->addRowAction('edit');
    $this->addRowAction('delete');

    return parent::renderList();
}


    public function renderForm()
    {
        $this->fields_form = [
            'legend' => [
                'title' => $this->l('Slide'),
                'icon' => 'icon-film',
            ],
            'input' => [
                [
                    'type' => 'switch',
                    'label' => $this->l('Enabled'),
                    'name' => 'active',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                        ],
                        [
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        ],
                    ],
                ],
                [
                    'type' => 'hidden',
                    'name' => 'placement',
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Desktop video path or URL'),
                    'name' => 'desktop_video',
                    'lang' => true,
                    'desc' => $this->l('Provide full URL or path to the desktop version (e.g. /videos/video.mp4). If only a filename is provided, it will be loaded from the module videos directory.'),
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Mobile video path or URL'),
                    'name' => 'mobile_video',
                    'lang' => true,
                    'desc' => $this->l('Provide full URL or path to the mobile version (e.g. /videos/video-mobile.mp4). If only a filename is provided, it will be loaded from the module videos directory.'),
                ],
                [
                    'type' => 'textarea',
                    'label' => $this->l('Description (HTML)'),
                    'name' => 'description',
                    'lang' => true,
                    'autoload_rte' => false,
                    'rows' => 6,
                    'desc' => $this->l('Short HTML description displayed at the bottom of the slide.'),
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Button label'),
                    'name' => 'button_label',
                    'lang' => true,
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Button label on category page (optional)'),
                    'name' => 'button_label_category',
                    'lang' => true,
                    'desc' => $this->l('Used only in displayBesmartVideosLarge hook. If empty, default button label is used.'),
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Button URL'),
                    'name' => 'button_url',
                    'lang' => true,
                    'desc' => $this->l('Full link used for call to action button.'),
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
            ],
        ];

        return parent::renderForm();
    }

    public function getFieldsValue($obj)
    {
        $fieldsValue = parent::getFieldsValue($obj);
        $fieldsValue['placement'] = $this->placement;

        return $fieldsValue;
    }

    public function processAdd()
    {
        $_POST['placement'] = $this->placement;

        return parent::processAdd();
    }

    public function processUpdate()
    {
        $_POST['placement'] = $this->placement;

        return parent::processUpdate();
    }

    public function ajaxProcessUpdatePositions()
    {
        $positions = Tools::getValue($this->table);
        if (is_array($positions)) {
            foreach ($positions as $index => $rowId) {
                $idSlide = (int) str_replace('tr_', '', $rowId);
                $slide = new BesmartVideoSlide($idSlide);
                if (Validate::isLoadedObject($slide)) {
                    $slide->position = (int) $index;
                    $slide->save();
                }
            }
        }

        die(true);
    }

    public function processDelete()
    {
        $this->deleteSlideFiles((int) Tools::getValue($this->identifier));
        $result = parent::processDelete();
        BesmartVideoSlide::cleanPositions($this->placement);

        return $result;
    }

    public function processBulkDelete()
    {
        $selected = Tools::getValue($this->table . 'Box');
        if (is_array($selected)) {
            foreach ($selected as $id) {
                $this->deleteSlideFiles((int) $id);
            }
        }
        $result = parent::processBulkDelete();
        BesmartVideoSlide::cleanPositions($this->placement);

        return $result;
    }

    private function getExistingVideoValue($videoField): string
    {
        if (is_array($videoField)) {
            $defaultLang = (int) Configuration::get('PS_LANG_DEFAULT');
            if (!empty($videoField[$defaultLang])) {
                return (string) $videoField[$defaultLang];
            }

            foreach ($videoField as $value) {
                if (!empty($value)) {
                    return (string) $value;
                }
            }
        } elseif (is_string($videoField)) {
            return $videoField;
        }

        return '';
    }

    private function deleteSlideFiles(int $idSlide): void
    {
        if (!$idSlide) {
            return;
        }

        $slide = new BesmartVideoSlide($idSlide);
        if (!Validate::isLoadedObject($slide)) {
            return;
        }

        $filenames = array_unique(array_filter([
            $this->getExistingVideoValue($slide->desktop_video),
            $this->getExistingVideoValue($slide->mobile_video),
        ]));

        foreach ($filenames as $filename) {
            $this->deleteFileIfExists($filename);
        }
    }

    private function deleteFileIfExists(string $filename): void
    {
        if (!$this->isLocalModuleVideo($filename)) {
            return;
        }

        $path = _PS_MODULE_DIR_ . 'besmartvideoslider/videos/' . $filename;
        if ($filename && file_exists($path)) {
            @unlink($path);
        }
    }

    private function isLocalModuleVideo(string $path): bool
    {
        if ($path === '') {
            return false;
        }

        if (preg_match('#^(https?:)?//#', $path)) {
            return false;
        }

        return strpos($path, '/') === false;
    }
}
