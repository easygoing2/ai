<?php
include_once('../../../../common.php');

// 최고관리자만 접근 가능
if ($is_admin != 'super') {
    die(json_encode(['success' => false, 'message' => '최고관리자만 접근 가능합니다.']));
}

// AJAX 요청 확인
if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    die(json_encode(['success' => false, 'message' => '잘못된 접근입니다.']));
}

// CSRF 토큰 검증 (유연한 처리)
$token = isset($_SERVER['HTTP_X_CSRF_TOKEN']) ? $_SERVER['HTTP_X_CSRF_TOKEN'] : 
         (isset($_SERVER['HTTP_X_CSRF_TOKEN']) ? $_SERVER['HTTP_X_CSRF_TOKEN'] : '');

// 토큰이 없거나 세션 토큰과 일치하지 않는 경우
if(!$token || !isset($_SESSION['ss_token']) || $token !== $_SESSION['ss_token']) {
    // 디버깅 정보 (개발 시에만 사용)
    error_log('CSRF Token Debug - Received: ' . $token . ', Session: ' . ($_SESSION['ss_token'] ?? 'not set'));
    die(json_encode(['success' => false, 'message' => 'CSRF 토큰이 유효하지 않습니다.']));
}

// JSON 데이터 받기
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !is_array($input)) {
    die(json_encode(['success' => false, 'message' => '잘못된 요청입니다.']));
}

// 동적으로 위젯 타입 가져오기 (분산형 시스템)
function get_allowed_widget_types() {
    $types = ['latest']; // 기본적으로 latest는 항상 포함

    // 각 위젯 폴더에서 widget.json 스캔
    $widget_base_path = G5_THEME_PATH . '/ui_widget';

    if (is_dir($widget_base_path)) {
        $widget_dirs = glob($widget_base_path . '/*', GLOB_ONLYDIR);

        foreach ($widget_dirs as $widget_dir) {
            $widget_json_path = $widget_dir . '/widget.json';

            if (file_exists($widget_json_path)) {
                $widget_data = @json_decode(file_get_contents($widget_json_path), true);

                if ($widget_data && isset($widget_data['filename'])) {
                    $types[] = $widget_data['filename'];
                }
            }
        }
    }

    return array_unique($types);
}

$allowed_widget_types = get_allowed_widget_types();

// 동적으로 스킨 목록 가져오기
function get_allowed_skins() {
    $skin_dir = G5_THEME_PATH . '/skin/latest';
    $allowed_skins = [];
    
    if (is_dir($skin_dir)) {
        $directories = scandir($skin_dir);
        foreach ($directories as $dir) {
            if ($dir != '.' && $dir != '..' && is_dir($skin_dir . '/' . $dir)) {
                // 디렉토리명 검증 (path traversal 방지)
                $dir = basename($dir);
                if (!preg_match('/[^a-zA-Z0-9_\-]/', $dir)) {
                    $allowed_skins[] = 'theme/' . $dir;
                }
            }
        }
    }
    
    return $allowed_skins;
}

$allowed_skins = get_allowed_skins();

// 입력 데이터 검증 및 정제
function validate_layout_data($data) {
    global $allowed_widget_types, $allowed_skins, $g5;
    
    if (!isset($data['grid_rows']) || !is_array($data['grid_rows'])) {
        return false;
    }
    
    $validated_rows = [];
    
    foreach ($data['grid_rows'] as $row) {
        if (!isset($row['columns']) || !is_array($row['columns'])) {
            continue;
        }
        
        $validated_columns = [];
        
        foreach ($row['columns'] as $col) {
            // 너비 검증
            $width = isset($col['width']) ? intval($col['width']) : 100;
            if ($width < 10 || $width > 100) {
                $width = 100;
            }
            
            $validated_col = ['width' => $width, 'widget' => null];
            
            // 위젯 데이터 검증
            if (isset($col['widget']) && is_array($col['widget'])) {
                $widget = $col['widget'];
                
                // 위젯 타입 검증
                if (!isset($widget['type']) || !in_array($widget['type'], $allowed_widget_types)) {
                    continue;
                }
                
                $validated_widget = ['type' => $widget['type']];
                
                if ($widget['type'] === 'latest') {
                    // 스킨 검증
                    if (isset($widget['skin']) && in_array($widget['skin'], $allowed_skins)) {
                        $validated_widget['skin'] = $widget['skin'];
                    } else {
                        $validated_widget['skin'] = 'theme/basic';
                    }
                    
                    // 멀티 게시판 처리 (boards 배열)
                    if (isset($widget['boards']) && is_array($widget['boards'])) {
                        $validated_boards = [];
                        foreach ($widget['boards'] as $board) {
                            if (preg_match('/^[a-zA-Z0-9_]+$/', $board)) {
                                $bo_table = sql_escape_string($board);
                                $board_check = sql_fetch("SELECT bo_table FROM {$g5['board_table']} WHERE bo_table = '{$bo_table}'");
                                if ($board_check) {
                                    $validated_boards[] = $board;
                                }
                            }
                        }
                        if (!empty($validated_boards)) {
                            $validated_widget['boards'] = $validated_boards;
                            $validated_widget['board'] = implode(',', $validated_boards); // 호환성을 위해 쉼표로 구분된 문자열도 저장
                        } else {
                            $validated_widget['board'] = 'free';
                            $validated_widget['boards'] = ['free'];
                        }
                    }
                    // 이전 버전 호환성 (단일 board 필드)
                    else if (isset($widget['board']) && preg_match('/^[a-zA-Z0-9_,]+$/', $widget['board'])) {
                        // 쉼표로 구분된 경우 처리
                        if (strpos($widget['board'], ',') !== false) {
                            $boards = explode(',', $widget['board']);
                            $validated_boards = [];
                            foreach ($boards as $board) {
                                $board = trim($board);
                                if (preg_match('/^[a-zA-Z0-9_]+$/', $board)) {
                                    $bo_table = sql_escape_string($board);
                                    $board_check = sql_fetch("SELECT bo_table FROM {$g5['board_table']} WHERE bo_table = '{$bo_table}'");
                                    if ($board_check) {
                                        $validated_boards[] = $board;
                                    }
                                }
                            }
                            if (!empty($validated_boards)) {
                                $validated_widget['boards'] = $validated_boards;
                                $validated_widget['board'] = implode(',', $validated_boards);
                            } else {
                                $validated_widget['board'] = 'free';
                                $validated_widget['boards'] = ['free'];
                            }
                        } else {
                            // 단일 게시판
                            $bo_table = sql_escape_string($widget['board']);
                            $board_check = sql_fetch("SELECT bo_table FROM {$g5['board_table']} WHERE bo_table = '{$bo_table}'");
                            if ($board_check) {
                                $validated_widget['board'] = $widget['board'];
                                $validated_widget['boards'] = [$widget['board']];
                            } else {
                                $validated_widget['board'] = 'free';
                                $validated_widget['boards'] = ['free'];
                            }
                        }
                    } else {
                        $validated_widget['board'] = 'free';
                        $validated_widget['boards'] = ['free'];
                    }
                    
                    // 행 수 검증
                    $rows = isset($widget['rows']) ? intval($widget['rows']) : 5;
                    $validated_widget['rows'] = max(1, min(20, $rows));
                    
                    // 제목 길이 검증
                    $subject_len = isset($widget['subject_len']) ? intval($widget['subject_len']) : 40;
                    $validated_widget['subject_len'] = max(10, min(100, $subject_len));
                    
                    // 타이틀 검증 (XSS 방지)
                    if (isset($widget['title'])) {
                        $validated_widget['title'] = clean_xss_tags($widget['title']);
                    }
                    
                    // 더보기 버튼 표시 여부 (여러 게시판 선택시 강제로 false)
                    if (isset($widget['show_more'])) {
                        // boards 배열이나 쉼표로 구분된 board 문자열 확인
                        $boardCount = 0;
                        if (isset($widget['boards']) && is_array($widget['boards'])) {
                            $boardCount = count($widget['boards']);
                        } elseif (isset($widget['board']) && strpos($widget['board'], ',') !== false) {
                            $boardCount = count(explode(',', $widget['board']));
                        } elseif (isset($widget['board']) && !empty($widget['board'])) {
                            $boardCount = 1;
                        }
                        
                        // 여러 게시판이면 더보기 버튼 비활성화
                        if ($boardCount > 1) {
                            $validated_widget['show_more'] = false;
                        } else {
                            $validated_widget['show_more'] = (bool)$widget['show_more'];
                        }
                    }
                } else {
                    // 커스텀 위젯 - 개별 widget.json에서 config schema를 확인하여 저장 (분산형 시스템)
                    $has_config = false;

                    // 위젯 타입에서 폴더명 추출
                    $widget_type = $widget['type'];
                    $widget_folder = explode('/', $widget_type)[0];
                    $widget_json_path = G5_THEME_PATH . '/ui_widget/' . $widget_folder . '/widget.json';

                    if (file_exists($widget_json_path)) {
                        $widget_def = @json_decode(file_get_contents($widget_json_path), true);

                        if ($widget_def && $widget_def['filename'] === $widget['type'] && isset($widget_def['config'])) {
                            $has_config = true;
                            $config_schema = $widget_def['config'];

                                    // widget.widget 객체에서 config 값 가져오기
                                    if (isset($widget['widget']) && is_array($widget['widget'])) {
                                        $validated_widget['widget'] = [];

                                        foreach ($config_schema as $key => $config) {
                                            if (isset($widget['widget'][$key])) {
                                                $value = $widget['widget'][$key];

                                                // 타입별 검증
                                                if ($config['type'] === 'select' && isset($config['options'])) {
                                                    // select: options에 있는 값인지 확인
                                                    $valid_values = array_column($config['options'], 'value');
                                                    if (in_array($value, $valid_values)) {
                                                        $validated_widget['widget'][$key] = $value;
                                                    } else {
                                                        $validated_widget['widget'][$key] = $config['default'];
                                                    }
                                                } else if ($config['type'] === 'number') {
                                                    // number: min, max 범위 확인
                                                    $num_value = intval($value);
                                                    $min = isset($config['min']) ? $config['min'] : PHP_INT_MIN;
                                                    $max = isset($config['max']) ? $config['max'] : PHP_INT_MAX;
                                                    $validated_widget['widget'][$key] = max($min, min($max, $num_value));
                                                } else if ($config['type'] === 'text') {
                                                    // text: XSS 방지
                                                    $validated_widget['widget'][$key] = clean_xss_tags($value);
                                                }
                                            } else {
                                                // 값이 없으면 기본값 사용
                                                $validated_widget['widget'][$key] = $config['default'];
                                            }
                                        }
                                    }
                        }
                    }

                    // config schema가 없는 경우 기존 방식대로 타이틀만 저장
                    if (!$has_config && isset($widget['title'])) {
                        $validated_widget['title'] = clean_xss_tags($widget['title']);
                    }
                }
                
                $validated_col['widget'] = $validated_widget;
            }
            
            $validated_columns[] = $validated_col;
        }
        
        if (!empty($validated_columns)) {
            $validated_rows[] = ['columns' => $validated_columns];
        }
    }
    
    return [
        'grid_rows' => $validated_rows,
        'updated_at' => date('c')
    ];
}

// 데이터 검증
$validated_data = validate_layout_data($input);
if (!$validated_data) {
    die(json_encode(['success' => false, 'message' => '유효하지 않은 데이터입니다.']));
}

// 레이아웃 파일 경로
$layout_file = G5_THEME_PATH.'/ui_system/widget-builder/widget-layout.json';

// 디렉토리가 없으면 생성
$dir = dirname($layout_file);
if (!is_dir($dir)) {
    // 상위 디렉토리 쓰기 권한 체크
    if (!is_writable(dirname($dir))) {
        die(json_encode(['success' => false, 'message' => '디렉토리 생성 권한이 없습니다.']));
    }
    if (!mkdir($dir, 0755, true)) {
        die(json_encode(['success' => false, 'message' => '디렉토리 생성에 실패했습니다.']));
    }
}

// 파일 쓰기 권한 체크
if (file_exists($layout_file) && !is_writable($layout_file)) {
    die(json_encode(['success' => false, 'message' => '파일 쓰기 권한이 없습니다.']));
}

// 백업 파일 생성
if (file_exists($layout_file)) {
    $backup_file = G5_THEME_PATH.'/ui_system/widget-builder/widget-layout-backup.json';
    @copy($layout_file, $backup_file);
}

// 파일에 저장
$result = file_put_contents($layout_file, json_encode($validated_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

if ($result !== false) {
    echo json_encode(['success' => true, 'message' => '저장되었습니다.']);
} else {
    echo json_encode(['success' => false, 'message' => '파일 저장에 실패했습니다.']);
}
?>