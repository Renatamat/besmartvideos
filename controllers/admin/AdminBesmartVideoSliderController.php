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
    $this->_select = 'b.`button_label`, b.`desktop_video`, b.`mobile_video`';
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
                    'type' => 'file',
                    'label' => $this->l('Desktop video (mp4)'),
                    'name' => 'desktop_video',
                    'lang' => true,
                    'desc' => $this->l('Upload MP4 file for desktop resolution.'),
                ],
                [
                    'type' => 'file',
                    'label' => $this->l('Mobile video (mp4)'),
                    'name' => 'mobile_video',
                    'lang' => true,
                    'desc' => $this->l('Upload MP4 file for mobile resolution.'),
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Button label'),
                    'name' => 'button_label',
                    'lang' => true,
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

    public function postProcess()
    {
        // Obsłuż upload _zanim_ Presta zacznie robić processSave()
        if (Tools::isSubmit('submitAdd' . $this->table)
            || Tools::isSubmit('submitAdd' . $this->table . 'AndStay')) {
            $this->handleUploads();
        }

        return parent::postProcess();
    }


//    public function processSave()
// {
//     // Upewniamy się, że obiekt jest załadowany / utworzony
//     if (!is_object($this->object)) {
//         $this->object = $this->loadObject(true);
//     }
//
//     if (!$this->object) {
//         // coś poszło nie tak – nie próbujemy dalej, żeby nie wywalić błędu
//         return false;
//     }
//
//     // Przepisanie danych z POST do obiektu (tutaj właśnie wcześniej leciał błąd)
//     $this->copyFromPost($this->object, $this->table);
//
//     // Ustaw pozycję tylko dla NOWEGO slajdu
//     if (!(int) $this->object->id) {
//         $this->object->position = BesmartVideoSlide::getMaxPosition() + 1;
//     }
//
//     // parent::processSave() zajmuje się resztą (walidacja, zapisy, asocjacje itd.)
//     return parent::processSave();
// }


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
        BesmartVideoSlide::cleanPositions();

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
        BesmartVideoSlide::cleanPositions();

        return $result;
    }

    private function handleUploads(): void
    {
        $languages = Language::getLanguages(false);
        $slideId = (int) Tools::getValue($this->identifier);
        $existing = $slideId ? new BesmartVideoSlide($slideId) : null;

        foreach ($languages as $lang) {
            $langId = (int) $lang['id_lang'];
            $desktopField = 'desktop_video_' . $langId;
            $mobileField = 'mobile_video_' . $langId;

            $desktopName = $this->uploadVideo($desktopField, 'desktop', $langId);
            $mobileName = $this->uploadVideo($mobileField, 'mobile', $langId);

            if (!$desktopName && $existing instanceof BesmartVideoSlide) {
                $desktopName = $existing->desktop_video[$langId] ?? '';
            }
            if (!$mobileName && $existing instanceof BesmartVideoSlide) {
                $mobileName = $existing->mobile_video[$langId] ?? '';
            }

            $_POST[$desktopField] = $desktopName;
            $_POST[$mobileField] = $mobileName;
        }
    }

    private function uploadVideo(string $fieldName, string $prefix, int $langId): ?string
    {
        if (empty($_FILES[$fieldName]['tmp_name'])) {
            return null;
        }

        $uploader = new Upload($fieldName);
        $uploader->setSavePath(_PS_MODULE_DIR_ . 'besmartvideoslider/videos/');
        $uploader->setAcceptTypes(['mp4', 'MP4']);
        $uploader->file_overwrite = false;
        $uploader->unique = true;

        $filename = sprintf('%s_%d_%s.mp4', $prefix, $langId, uniqid());

        if (!$uploader->validate()) {
            $this->errors[] = $this->l('Invalid video upload. Only MP4 files are allowed.');

            return null;
        }

        if (!$uploader->upload($filename)) {
            $this->errors[] = $this->l('Video upload failed.');

            return null;
        }

        return $filename;
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

        $languages = Language::getLanguages(false);
        foreach ($languages as $lang) {
            $langId = (int) $lang['id_lang'];
            $this->deleteFileIfExists($slide->desktop_video[$langId] ?? '');
            $this->deleteFileIfExists($slide->mobile_video[$langId] ?? '');
        }
    }

    private function deleteFileIfExists(string $filename): void
    {
        $path = _PS_MODULE_DIR_ . 'besmartvideoslider/videos/' . $filename;
        if ($filename && file_exists($path)) {
            @unlink($path);
        }
    }
}
