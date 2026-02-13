<?php
/**
 * Admin controller for BeSmart Video Slider (large placement)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'besmartvideoslider/controllers/admin/AdminBesmartVideoSliderController.php';

class AdminBesmartVideoSliderLargeController extends AdminBesmartVideoSliderController
{
    protected $placement = BesmartVideoSlide::PLACEMENT_LARGE;
}
