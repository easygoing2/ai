<?php
include_once('./_common.php');

// 최고관리자만 접근 가능
if ($is_admin != 'super') {
    alert('최고관리자만 접근 가능합니다.');
}

$g5['title'] = '테마 설정';

// Ajax 요청 처리
if(isset($_REQUEST['action'])) {
    header('Content-Type: application/json');
    
    $action = $_REQUEST['action'];
    
    switch($action) {
        case 'create_slider_table':
            // 슬라이더 테이블 생성
            $sql = "CREATE TABLE IF NOT EXISTS `g5_theme_slider` (
                `slide_id` INT NOT NULL AUTO_INCREMENT,
                `slide_title` VARCHAR(255),
                `slide_subtitle` TEXT,
                `slide_image` VARCHAR(255),
                `slide_link` VARCHAR(500),
                `slide_button` VARCHAR(100),
                `text_color` VARCHAR(20) DEFAULT 'white',
                `button_color` VARCHAR(20) DEFAULT 'primary',
                `slide_order` INT DEFAULT 0,
                `slide_use` TINYINT(1) DEFAULT 1,
                `reg_datetime` DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`slide_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            
            if(sql_query($sql, false)) {
                die(json_encode(['success' => true]));
            } else {
                die(json_encode(['success' => false, 'message' => '테이블 생성 실패']));
            }
            break;
            
        case 'save_slider':
            // 슬라이더 저장
            $mode = $_POST['mode'];
            $slide_id = isset($_POST['slide_id']) ? (int)$_POST['slide_id'] : 0;
            $slide_title = isset($_POST['slide_title']) ? strip_tags($_POST['slide_title']) : '';
            $slide_subtitle = isset($_POST['slide_subtitle']) ? strip_tags($_POST['slide_subtitle']) : '';
            $slide_button = isset($_POST['slide_button']) ? strip_tags($_POST['slide_button']) : '';
            $slide_link = isset($_POST['slide_link']) ? strip_tags($_POST['slide_link']) : '';
            $text_color = isset($_POST['text_color']) ? $_POST['text_color'] : 'white';
            $button_color = isset($_POST['button_color']) ? $_POST['button_color'] : 'primary';
            $slide_order = isset($_POST['slide_order']) ? (int)$_POST['slide_order'] : 0;
            $slide_use = isset($_POST['slide_use']) ? 1 : 0;
            
            // 이미지 업로드 처리
            $slide_image = '';
            if(isset($_FILES['slide_image']) && $_FILES['slide_image']['error'] == 0) {
                $upload_dir = G5_DATA_PATH.'/mainkv';
                if(!is_dir($upload_dir)) {
                    @mkdir($upload_dir, 0755, true);
                }
                
                $filename = time().'_'.preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['slide_image']['name']);
                $upload_file = $upload_dir.'/'.$filename;
                
                if(move_uploaded_file($_FILES['slide_image']['tmp_name'], $upload_file)) {
                    $slide_image = $filename;
                    
                    // 기존 이미지 삭제 (수정 모드일 때)
                    if($mode == 'edit' && $slide_id > 0) {
                        $sql = "SELECT slide_image FROM g5_theme_slider WHERE slide_id = '$slide_id'";
                        $old = sql_fetch($sql);
                        if($old['slide_image'] && file_exists($upload_dir.'/'.$old['slide_image'])) {
                            @unlink($upload_dir.'/'.$old['slide_image']);
                        }
                    }
                }
            }
            
            if($mode == 'add') {
                // 추가
                $sql = "INSERT INTO g5_theme_slider SET
                        slide_title = '$slide_title',
                        slide_subtitle = '$slide_subtitle',
                        slide_button = '$slide_button',
                        slide_link = '$slide_link',
                        text_color = '$text_color',
                        button_color = '$button_color',
                        slide_order = '$slide_order',
                        slide_use = '$slide_use'";
                if($slide_image) {
                    $sql .= ", slide_image = '$slide_image'";
                }
            } else {
                // 수정
                $sql = "UPDATE g5_theme_slider SET
                        slide_title = '$slide_title',
                        slide_subtitle = '$slide_subtitle',
                        slide_button = '$slide_button',
                        slide_link = '$slide_link',
                        text_color = '$text_color',
                        button_color = '$button_color',
                        slide_order = '$slide_order',
                        slide_use = '$slide_use'";
                if($slide_image) {
                    $sql .= ", slide_image = '$slide_image'";
                }
                $sql .= " WHERE slide_id = '$slide_id'";
            }
            
            if(sql_query($sql)) {
                die(json_encode(['success' => true]));
            } else {
                die(json_encode(['success' => false, 'message' => '저장 실패']));
            }
            break;
            
        case 'get_slider':
            // 슬라이더 정보 조회
            $slide_id = isset($_GET['slide_id']) ? (int)$_GET['slide_id'] : 0;
            $sql = "SELECT * FROM g5_theme_slider WHERE slide_id = '$slide_id'";
            $slider = sql_fetch($sql);
            
            if($slider) {
                die(json_encode(['success' => true, 'slider' => $slider]));
            } else {
                die(json_encode(['success' => false, 'message' => '슬라이더를 찾을 수 없습니다.']));
            }
            break;
            
        case 'delete_slider':
            // 슬라이더 삭제
            $slide_id = isset($_POST['slide_id']) ? (int)$_POST['slide_id'] : 0;
            
            // 이미지 파일 삭제
            $sql = "SELECT slide_image FROM g5_theme_slider WHERE slide_id = '$slide_id'";
            $slider = sql_fetch($sql);
            if($slider['slide_image']) {
                $file_path = G5_DATA_PATH.'/mainkv/'.$slider['slide_image'];
                if(file_exists($file_path)) {
                    @unlink($file_path);
                }
            }
            
            // DB 삭제
            $sql = "DELETE FROM g5_theme_slider WHERE slide_id = '$slide_id'";
            if(sql_query($sql)) {
                die(json_encode(['success' => true]));
            } else {
                die(json_encode(['success' => false, 'message' => '삭제 실패']));
            }
            break;
            
        case 'move_slider':
            // 슬라이더 순서 변경
            $slide_id = isset($_POST['slide_id']) ? (int)$_POST['slide_id'] : 0;
            $direction = isset($_POST['direction']) ? $_POST['direction'] : '';
            
            // 현재 슬라이더 정보
            $sql = "SELECT * FROM g5_theme_slider WHERE slide_id = '$slide_id'";
            $current = sql_fetch($sql);
            
            if($current) {
                if($direction == 'up') {
                    // 위로 이동 (순서 감소)
                    $sql = "SELECT * FROM g5_theme_slider 
                            WHERE slide_order <= '{$current['slide_order']}' 
                            AND slide_id != '$slide_id'
                            ORDER BY slide_order DESC, slide_id DESC LIMIT 1";
                } else {
                    // 아래로 이동 (순서 증가)
                    $sql = "SELECT * FROM g5_theme_slider 
                            WHERE slide_order >= '{$current['slide_order']}' 
                            AND slide_id != '$slide_id'
                            ORDER BY slide_order ASC, slide_id ASC LIMIT 1";
                }
                
                $target = sql_fetch($sql);
                
                if($target) {
                    // 순서 교체
                    $sql1 = "UPDATE g5_theme_slider SET slide_order = '{$target['slide_order']}' WHERE slide_id = '{$current['slide_id']}'";
                    $sql2 = "UPDATE g5_theme_slider SET slide_order = '{$current['slide_order']}' WHERE slide_id = '{$target['slide_id']}'";
                    
                    sql_query($sql1);
                    sql_query($sql2);
                    
                    die(json_encode(['success' => true]));
                } else {
                    die(json_encode(['success' => false, 'message' => '이동할 수 없습니다.']));
                }
            }
            break;
    }
    
    die(json_encode(['success' => false, 'message' => '잘못된 요청']));
}

// 폼 제출 처리
if (isset($_POST['w']) && $_POST['w'] == 'u') {

    // 여분필드 설정 제목
    $cf_1_subj = "로고 및 폰트 설정 - JSON";
    $cf_2_subj = "예비필드2";
    $cf_3_subj = "컬러셋";
    $cf_4_subj = "예비필드4";
    $cf_5_subj = "예비필드5";
    $cf_6_subj = "예비필드6";
    $cf_7_subj = "기타 설정 - JSON";
    $cf_8_subj = "예비필드8";
    $cf_9_subj = "API 설정 - JSON";
    $cf_10_subj = "예비필드10";

    // POST 데이터 받기
    $cf_1 = isset($_POST['cf_1']) ? $_POST['cf_1'] : '';
    $cf_2 = ''; // 사용 안함
    $cf_3 = isset($_POST['cf_3']) && !empty($_POST['cf_3']) ? $_POST['cf_3'] : (isset($config['cf_3']) ? $config['cf_3'] : '');
    $cf_4 = ''; // 사용 안함
    $cf_5 = ''; // 사용 안함
    $cf_6 = ''; // 사용 안함
    $cf_7 = isset($_POST['cf_7']) ? $_POST['cf_7'] : '';
    $cf_8 = ''; // 사용 안함
    $cf_9 = isset($_POST['cf_9']) ? $_POST['cf_9'] : '';
    $cf_10 = ''; // 사용 안함
    
    // 설정 업데이트
    $sql = " update {$g5['config_table']}
                set cf_1_subj = '$cf_1_subj',
                    cf_2_subj = '$cf_2_subj',
                    cf_3_subj = '$cf_3_subj',
                    cf_4_subj = '$cf_4_subj',
                    cf_5_subj = '$cf_5_subj',
                    cf_6_subj = '$cf_6_subj',
                    cf_7_subj = '$cf_7_subj',
                    cf_8_subj = '$cf_8_subj',
                    cf_9_subj = '$cf_9_subj',
                    cf_10_subj = '$cf_10_subj',
                    cf_1 = '$cf_1',
                    cf_2 = '$cf_2',
                    cf_3 = '$cf_3',
                    cf_4 = '$cf_4',
                    cf_5 = '$cf_5',
                    cf_6 = '$cf_6',
                    cf_7 = '$cf_7',
                    cf_8 = '$cf_8',
                    cf_9 = '$cf_9',
                    cf_10 = '$cf_10' ";

    sql_query($sql);

    alert('테마 설정이 저장되었습니다.', G5_BBS_URL.'/uxc_theme_config.php', false);
    exit;
}

// 스킨 설정
$theme_config_skin = G5_THEME_PATH.'/ui_theme_setting/theme_config';

include_once(G5_PATH.'/head.php');

// 스킨 파일 include
if(file_exists($theme_config_skin.'/theme_config.skin.php')) {
    include_once($theme_config_skin.'/theme_config.skin.php');
} else {
    echo '<p>테마 설정 스킨 파일이 없습니다.</p>';
}

include_once(G5_PATH.'/tail.php');
?>