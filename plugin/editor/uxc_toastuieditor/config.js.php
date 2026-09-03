<?php
// 동적으로 JavaScript 설정을 생성하는 PHP 파일

// 그누보드 common.php 포함
$common_path = '';
$check_paths = array(
    '../../../common.php',
    '../../../../common.php',
    '../../../../../common.php',
    '../../../../../../common.php'
);

foreach ($check_paths as $path) {
    if (file_exists($path)) {
        $common_path = $path;
        break;
    }
}

if ($common_path) {
    include_once($common_path);
}

// common.php에서 설정한 HTML Content-Type을 JavaScript용으로 덮어씁니다.
header('Content-Type: application/javascript; charset=utf-8');
header('Cache-Control: public, max-age=3600');

$plugin_url = defined('G5_PLUGIN_URL') ? G5_PLUGIN_URL . '/editor/uxc_toastuieditor' : '';
?>
// Toast UI Editor Configuration (자동 생성 - 수정하지 마세요)
window.TOASTUI_EDITOR_CONFIG = {
    pluginUrl: '<?php echo $plugin_url; ?>',
    uploadUrl: '<?php echo $plugin_url; ?>/upload.php',
    editorHeight: '600px',
    toolbarItems: [
        ['heading', 'bold', 'italic', 'strike'],
        ['hr', 'quote'],
        ['ul', 'ol', 'task', 'indent', 'outdent'],
        ['table', 'image', 'link'],
        ['code', 'codeblock'],
        ['scrollSync']
    ],
    prismLanguagesPath: 'https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/',
    language: 'ko-KR',
    maxContentWarningSize: 1000000,
    autoSaveInterval: 30000
};
