<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// ui_theme_setting 폴더에서 config.json 파일을 읽어 탭 메뉴 동적 생성
$admin_tabs = array();
$theme_setting_path = G5_THEME_PATH.'/ui_theme_setting';

if (is_dir($theme_setting_path)) {
    $folders = array_diff(scandir($theme_setting_path), array('.', '..'));

    foreach ($folders as $folder) {
        $folder_path = $theme_setting_path.'/'.$folder;
        $config_file = $folder_path.'/config.json';

        // config.json 파일이 있는 폴더만 처리
        if (is_dir($folder_path) && file_exists($config_file)) {
            $tab_config = json_decode(file_get_contents($config_file), true);

            if ($tab_config && isset($tab_config['name']) && isset($tab_config['file'])) {
                $admin_tabs[] = array(
                    'url' => G5_BBS_URL.'/'.$tab_config['file'],
                    'icon' => isset($tab_config['icon']) ? $tab_config['icon'] : 'bx-cog',
                    'title' => $tab_config['name'],
                    'file' => $tab_config['file'],
                    'order' => isset($tab_config['order']) ? (int)$tab_config['order'] : 999,
                    'description' => isset($tab_config['description']) ? $tab_config['description'] : ''
                );
            }
        }
    }

    // order 기준으로 정렬
    usort($admin_tabs, function($a, $b) {
        return $a['order'] - $b['order'];
    });
}

// 현재 페이지 확인
$current_file = basename($_SERVER['PHP_SELF']);
?>

<!-- 탭 네비게이션 -->
<div class="configTabs">
    <?php foreach($admin_tabs as $tab) {
        $active = ($current_file == $tab['file']) ? 'active' : '';
        $title_attr = !empty($tab['description']) ? ' title="'.htmlspecialchars($tab['description']).'"' : '';
    ?>
    <a href="<?php echo $tab['url']; ?>" class="configTab <?php echo $active; ?>"<?php echo $title_attr; ?>>
        <i class='bx <?php echo $tab['icon']; ?>'></i><?php echo $tab['title']; ?>
    </a>
    <?php } ?>
</div>
