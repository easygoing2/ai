<?php
include_once('./_common.php');

// 최고관리자만 접근 가능
if ($is_admin != 'super') {
    alert('최고관리자만 접근 가능합니다.');
}

$g5['title'] = '메인 슬라이더 관리';

// 기존 테이블 호환성을 위한 컬럼 추가
$table_check = sql_fetch("SHOW TABLES LIKE 'g5_theme_slider'");
if($table_check) {
    // slide_link_target 컬럼 체크
    $col_check = sql_fetch("SHOW COLUMNS FROM g5_theme_slider LIKE 'slide_link_target'");
    if(!$col_check) {
        sql_query("ALTER TABLE g5_theme_slider ADD slide_link_target VARCHAR(20) DEFAULT '_self' AFTER slide_link", false);
    }
    
    // click_count 컬럼 체크
    $col_check2 = sql_fetch("SHOW COLUMNS FROM g5_theme_slider LIKE 'click_count'");
    if(!$col_check2) {
        sql_query("ALTER TABLE g5_theme_slider ADD click_count INT DEFAULT 0 AFTER slide_use", false);
    }
}

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
                `slide_link_target` VARCHAR(20) DEFAULT '_self',
                `slide_button` VARCHAR(100),
                `text_color` VARCHAR(20) DEFAULT 'white',
                `button_color` VARCHAR(20) DEFAULT 'primary',
                `slide_order` INT DEFAULT 0,
                `slide_use` TINYINT(1) DEFAULT 1,
                `click_count` INT DEFAULT 0,
                `reg_datetime` DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`slide_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            
            if(sql_query($sql, false)) {
                // slide_link_target 컴럼이 없으면 추가
                $col_check = sql_fetch("SHOW COLUMNS FROM g5_theme_slider LIKE 'slide_link_target'");
                if(!$col_check) {
                    sql_query("ALTER TABLE g5_theme_slider ADD slide_link_target VARCHAR(20) DEFAULT '_self' AFTER slide_link", false);
                }
                
                // 스킨 설정 테이블 생성
                $skin_table_sql = "CREATE TABLE IF NOT EXISTS `g5_theme_skin_config` (
                    `page_type` VARCHAR(50) NOT NULL PRIMARY KEY,
                    `skin_name` VARCHAR(100) NOT NULL DEFAULT 'basic',
                    `update_datetime` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
                sql_query($skin_table_sql, false);
                
                // 기본값 삽입 (없을 경우에만)
                sql_query("INSERT IGNORE INTO g5_theme_skin_config (page_type, skin_name) VALUES ('mypage', 'basic'), ('dashboard', 'basic')", false);
                
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
            $slide_link_target = isset($_POST['slide_link_target']) ? $_POST['slide_link_target'] : '_self';
            $text_color = isset($_POST['text_color']) ? $_POST['text_color'] : 'white';
            $button_color = isset($_POST['button_color']) ? $_POST['button_color'] : 'primary';
            $slide_order = isset($_POST['slide_order']) ? (int)$_POST['slide_order'] : 0;
            $slide_use = isset($_POST['slide_use']) ? (int)$_POST['slide_use'] : 0;
            
            // 이미지 업로드 처리
            $slide_image = '';
            $update_image = false;
            
            if(isset($_FILES['slide_image']) && $_FILES['slide_image']['error'] == 0) {
                $upload_dir = G5_DATA_PATH.'/mainkv';
                if(!is_dir($upload_dir)) {
                    @mkdir($upload_dir, 0755, true);
                }
                
                $filename = time().'_'.preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['slide_image']['name']);
                $upload_file = $upload_dir.'/'.$filename;
                
                if(move_uploaded_file($_FILES['slide_image']['tmp_name'], $upload_file)) {
                    $slide_image = $filename;
                    $update_image = true;
                    
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
                        slide_link_target = '$slide_link_target',
                        text_color = '$text_color',
                        button_color = '$button_color',
                        slide_order = '$slide_order',
                        slide_use = '$slide_use'";
                if($update_image) {
                    $sql .= ", slide_image = '$slide_image'";
                }
            } else {
                // 수정
                $sql = "UPDATE g5_theme_slider SET
                        slide_title = '$slide_title',
                        slide_subtitle = '$slide_subtitle',
                        slide_button = '$slide_button',
                        slide_link = '$slide_link',
                        slide_link_target = '$slide_link_target',
                        text_color = '$text_color',
                        button_color = '$button_color',
                        slide_order = '$slide_order',
                        slide_use = '$slide_use'";
                if($update_image) {
                    $sql .= ", slide_image = '$slide_image'";
                }
                $sql .= " WHERE slide_id = '$slide_id'";
            }
            
            if(sql_query($sql)) {
                die(json_encode(['success' => true]));
            } else {
                die(json_encode(['success' => false, 'message' => '저장 실패: ' . sql_error()]));
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
            
            // 모든 슬라이더를 순서대로 가져오기
            $sql = "SELECT * FROM g5_theme_slider ORDER BY slide_order ASC, slide_id DESC";
            $result = sql_query($sql);
            
            $sliders = array();
            $current_index = -1;
            $i = 0;
            
            while($row = sql_fetch_array($result)) {
                $sliders[] = $row;
                if($row['slide_id'] == $slide_id) {
                    $current_index = $i;
                }
                $i++;
            }
            
            if($current_index >= 0) {
                $swap_index = -1;
                
                if($direction == 'up' && $current_index > 0) {
                    $swap_index = $current_index - 1;
                } else if($direction == 'down' && $current_index < count($sliders) - 1) {
                    $swap_index = $current_index + 1;
                }
                
                if($swap_index >= 0) {
                    // 순서 번호 재정렬
                    for($j = 0; $j < count($sliders); $j++) {
                        $new_order = $j;
                        
                        if($j == $current_index) {
                            $new_order = $swap_index;
                        } else if($j == $swap_index) {
                            $new_order = $current_index;
                        }
                        
                        $sql = "UPDATE g5_theme_slider SET slide_order = '$new_order' WHERE slide_id = '{$sliders[$j]['slide_id']}'";
                        sql_query($sql);
                    }
                    
                    die(json_encode(['success' => true]));
                }
            }
            
            die(json_encode(['success' => false, 'message' => '이동할 수 없습니다.']));
            break;
            
        case 'update_click':
            // 클릭 카운트 업데이트
            $slide_id = isset($_POST['slide_id']) ? (int)$_POST['slide_id'] : 0;
            
            if($slide_id > 0) {
                $sql = "UPDATE g5_theme_slider SET click_count = click_count + 1 WHERE slide_id = '$slide_id'";
                if(sql_query($sql)) {
                    // 현재 클릭 수 조회
                    $sql = "SELECT click_count FROM g5_theme_slider WHERE slide_id = '$slide_id'";
                    $result = sql_fetch($sql);
                    die(json_encode(['success' => true, 'click_count' => $result['click_count']]));
                }
            }
            
            die(json_encode(['success' => false, 'message' => '클릭 카운트 업데이트 실패']));
            break;
            
        case 'delete_slider_table':
            // 슬라이더 테이블 삭제
            // 먼저 이미지 파일들 삭제
            $sql = "SELECT slide_image FROM g5_theme_slider WHERE slide_image != ''";
            $result = sql_query($sql);
            while($row = sql_fetch_array($result)) {
                $file_path = G5_DATA_PATH.'/mainkv/'.$row['slide_image'];
                if(file_exists($file_path)) {
                    @unlink($file_path);
                }
            }
            
            // 테이블 삭제
            $sql = "DROP TABLE IF EXISTS g5_theme_slider";
            if(sql_query($sql, false)) {
                die(json_encode(['success' => true]));
            } else {
                die(json_encode(['success' => false, 'message' => '테이블 삭제 실패']));
            }
            break;
    }
    
    die(json_encode(['success' => false, 'message' => '잘못된 요청']));
}

// 스킨 설정
$slider_admin_skin = G5_THEME_PATH.'/ui_theme_setting/theme_slider';

include_once(G5_PATH.'/head.php');

// 스킨 파일 include
if(file_exists($slider_admin_skin.'/slider_admin.skin.php')) {
    include_once($slider_admin_skin.'/slider_admin.skin.php');
} else {
    echo '<p>슬라이더 관리 스킨 파일이 없습니다.</p>';
}

include_once(G5_PATH.'/tail.php');
?>