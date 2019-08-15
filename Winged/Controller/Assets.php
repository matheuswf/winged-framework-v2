<?php

namespace Winged\Assets;

use Winged\Controller\Controller;
use Winged\App\App;

/**
 * Class Assets
 * @package Winged\Assets
 */
class Assets
{
    /**
     * @var $controller Controller
     */
    private $controller = null;

    public function __construct(Controller $controller = null)
    {
        $this->controller = $controller;
    }

    /**
     * @return $this
     */
    public function admin()
    {
        $this->controller->rewriteHeadContentPath(App::$parent . '/head.content.php');

        /*<core css>*/
        $this->controller->addCss("roboto", "https://fonts.googleapis.com/css?family=Roboto:400,300,100,500,700,900", [], true);
        $this->controller->addCss("bootstrap", App::$parent . "assets/css/core/files/bootstrap.css");
        $this->controller->addCss("font-awesome", App::$parent . "assets/css/core/files/font-awesome.css");
        $this->controller->addCss("components", App::$parent . "assets/css/core/files/components.css");
        $this->controller->addCss("colors", App::$parent . "assets/css/core/files/colors.css");
        $this->controller->addCss("core", App::$parent . "assets/css/core/core.css");
        /*<end core css>*/

        /*<custom>*/
        $this->controller->addCss("croppic", App::$parent . "assets/ext/croppie/jcrop.css");
        /*<end custom>*/

        $this->controller->addJs("beggin", App::initialJs());

        /*<core js>*/
        $this->controller->addJs("jquery", App::$parent . "assets/js/core/files/libraries/jquery.min.js");
        $this->controller->addJs("jquery-ui", App::$parent . "assets/js/core/files/jquery-ui.min.js");
        $this->controller->addJs("pace", App::$parent . "assets/js/plugins/loaders/pace.min.js");
        $this->controller->addJs("bootstrap", App::$parent . "assets/js/core/files/libraries/bootstrap.min.js");
        $this->controller->addJs("blockui", App::$parent . "assets/js/plugins/loaders/blockui.min.js");
        $this->controller->addJs("jscookie", App::$parent . "assets/js/core/files/js.cookie.js");
        /*<end core js>*/

        /*<fileupload js>*/
        $this->controller->addJs("fileupload-widget", App::$parent . "assets/js/plugins/fileupload/jquery.ui.widget.js");
        $this->controller->addJs("fileupload-canvas", App::$parent . "assets/js/plugins/fileupload/canvas.to.blob.js");
        $this->controller->addJs("fileupload-load", App::$parent . "assets/js/plugins/fileupload/load-image.all.js");
        $this->controller->addJs("fileupload-iframe", App::$parent . "assets/js/plugins/fileupload/jquery.iframe-transport.js");
        $this->controller->addJs("fileupload-main", App::$parent . "assets/js/plugins/fileupload/jquery.fileupload.js");
        $this->controller->addJs("fileupload-process", App::$parent . "assets/js/plugins/fileupload/jquery.fileupload-process.js");
        $this->controller->addJs("fileupload-image", App::$parent . "assets/js/plugins/fileupload/jquery.fileupload-image.js");
        $this->controller->addJs("fileupload-audio", App::$parent . "assets/js/plugins/fileupload/jquery.fileupload-audio.js");
        $this->controller->addJs("fileupload-video", App::$parent . "assets/js/plugins/fileupload/jquery.fileupload-video.js");
        $this->controller->addJs("fileupload-validate", App::$parent . "assets/js/plugins/fileupload/jquery.fileupload-validate.js");
        $this->controller->addJs("fileupload-ui", App::$parent . "assets/js/plugins/fileupload/jquery.fileupload-ui.js");
        /*<end fileupload js>*/

        /*<custom>*/
        $this->controller->addJs("croppic", App::$parent . "assets/ext/croppie/jcrop.js");
        $this->controller->addJs("pnotify", App::$parent . "assets/js/plugins/notifications/pnotify.min.js");
        $this->controller->addJs("mask", App::$parent . "assets/js/core/files/mask.js");
        $this->controller->addJs("maskmoney", App::$parent . "assets/js/core/files/maskmoney.js");
        $this->controller->addJs("evaluate", App::$parent . "assets/js/core/files/evaluate.js");
        $this->controller->addJs("validate", App::$parent . "assets/js/plugins/forms/validation/validate.min.js");
        $this->controller->addJs("handlebars", App::$parent . "assets/js/plugins/forms/inputs/typeahead/handlebars.min.js");
        $this->controller->addJs("alpaca", App::$parent . "assets/js/plugins/forms/inputs/alpaca/alpaca.min.js");
        $this->controller->addJs("uniform", App::$parent . "assets/js/plugins/forms/styling/uniform.min.js");
        $this->controller->addJs("summernote", App::$parent . "assets/js/plugins/editors/summernote/summernote.min.js");
        $this->controller->addJs("summernote-lang", App::$parent . "assets/js/plugins/editors/summernote/lang/summernote-pt-BR.js");
        $this->controller->addJs("select", App::$parent . "assets/js/plugins/forms/selects/select2.min.js");
        $this->controller->addJs("validate_lang", App::$parent . "assets/js/plugins/forms/validation/localization/messages_pt_PT.js");
        $this->controller->addJs("checkbox_switchery", App::$parent . "assets/js/plugins/forms/styling/switchery.min.js");
        $this->controller->addJs("checkbox_switch", App::$parent . "assets/js/plugins/forms/styling/switch.min.js");
        $this->controller->addJs("search", App::$parent . "assets/js/core/search.js");
        $this->controller->addJs("admin.class", App::$parent . "assets/js/core/admin.class.js");
        $this->controller->addJs("bootbox", App::$parent . "assets/js/plugins/notifications/bootbox.min.js");
        $this->controller->addJs("sweet_alert", App::$parent . "assets/js/plugins/notifications/sweet_alert.min.js");
        $this->controller->addJs("moment", App::$parent . "assets/js/plugins/ui/moment/moment.min.js");
        $this->controller->addJs("moment_locales", App::$parent . "assets/js/plugins/ui/moment/moment_locales.min.js");
        $this->controller->addJs("daterangepicker", App::$parent . "assets/js/plugins/pickers/daterangepicker.js");
        $this->controller->addJs("anytime", App::$parent . "assets/js/plugins/pickers/anytime.min.js");
        $this->controller->addJs("pickadate", App::$parent . "assets/js/plugins/pickers/pickadate/picker.js");
        $this->controller->addJs("pickadatedate", App::$parent . "assets/js/plugins/pickers/pickadate/picker.date.js");
        $this->controller->addJs("pickadatetime", App::$parent . "assets/js/plugins/pickers/pickadate/picker.time.js");
        $this->controller->addJs("legacy", App::$parent . "assets/js/plugins/pickers/pickadate/legacy.js");
        $this->controller->addJs("default", App::$parent . "assets/js/pages/default.js");
        $this->controller->addJs("globals", "<script>let PAGE_SURNAME = \"". App::$page_surname ."\";</script>");
        /*<end custom>*/

        /*<core>*/
        $this->controller->addJs("core", App::$parent . "assets/js/core/core.js");
        /*<end core>*/

        return $this;
    }
}