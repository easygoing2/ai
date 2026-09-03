<?php
include_once('./_common.php');

// 로그인 체크
if (!$member['mb_id']) {
    alert('로그인 후 이용하세요.', G5_BBS_URL.'/login.php?url='.urlencode(G5_BBS_URL.'/mypage.php'));
}

// Ajax 요청 처리
if (isset($_POST['ajax_mode']) && $_POST['ajax_mode'] === 'profile_update') {
    // CSRF 토큰 검증
    if (!isset($_POST['token']) || !check_token($_POST['token'])) {
        die(json_encode(['success' => false, 'message' => '토큰 검증 실패']));
    }

    // 회원 이미지 경로 설정
    $mb_img_dir = G5_DATA_PATH.'/member_image/'.substr($member['mb_id'],0,2);
    @mkdir($mb_img_dir, G5_DIR_PERMISSION);
    @chmod($mb_img_dir, G5_DIR_PERMISSION);
    
    $mb_img_filename = $mb_img_dir.'/'.$member['mb_id'].'.gif';
    
    $response = ['success' => false, 'message' => ''];
    
    // 프로필 이미지 삭제
    if (isset($_POST['delete_mb_img']) && $_POST['delete_mb_img'] === '1') {
        @unlink($mb_img_filename);
        $response['success'] = true;
        $response['message'] = '프로필 이미지가 삭제되었습니다.';
    }
    
    // 프로필 이미지 업로드
    if (isset($_FILES['mb_img']) && is_uploaded_file($_FILES['mb_img']['tmp_name'])) {
        require_once(G5_LIB_PATH.'/thumbnail.lib.php');
        
        $img_info = getimagesize($_FILES['mb_img']['tmp_name']);
        if (($img_info[2] == IMAGETYPE_GIF || $img_info[2] == IMAGETYPE_JPEG || $img_info[2] == IMAGETYPE_PNG)
            && $_FILES['mb_img']['size'] <= 50000
            && $img_info[0] <= 60 && $img_info[1] <= 60) {
            @unlink($mb_img_filename);
            
            $src = $_FILES['mb_img']['tmp_name'];
            $dest = $mb_img_filename;
            $thumb = thumbnail($src, $dest, 60, 60, false, true);
            
            if ($thumb) {
                chmod($mb_img_filename, G5_FILE_PERMISSION);
                $response['success'] = true;
                $response['message'] = '프로필 이미지가 업로드되었습니다.';
            } else {
                $response['message'] = '이미지 리사이징에 실패했습니다.';
            }
        } else {
            $response['message'] = '이미지는 gif, jpg, png 형식으로 크기 60x60 픽셀 이하, 용량 50KB 이하만 가능합니다.';
        }
    }
    
    // 기본 아이콘 선택
    if (isset($_POST['selected_icon'])) {
        $selected_icon = trim($_POST['selected_icon']);

        // 스킨 설정 확인
        $mypage_skin_ajax = 'basic'; // 기본값
        $skin_config_ajax = sql_fetch("SELECT skin_name FROM g5_theme_skin_config WHERE page_type = 'mypage'");
        if($skin_config_ajax && $skin_config_ajax['skin_name']) {
            $mypage_skin_ajax = $skin_config_ajax['skin_name'];
        }

        // URL에서 파일명만 추출
        $icon_filename = basename($selected_icon);
        $icon_ext = pathinfo($icon_filename, PATHINFO_EXTENSION);

        if (in_array(strtolower($icon_ext), ['gif', 'jpg', 'jpeg', 'png'])) {
            // 로컬 파일 경로 구성 (현재 스킨에 맞게)
            $source_file = G5_THEME_PATH.'/skin/mypage/'.$mypage_skin_ajax.'/icon/'.$icon_filename;
            
            if (file_exists($source_file)) {
                // 기존 이미지 삭제
                @unlink($mb_img_filename);
                
                // 파일 복사
                if (copy($source_file, $mb_img_filename)) {
                    chmod($mb_img_filename, G5_FILE_PERMISSION);
                    
                    // DB 업데이트 (mb_icon 필드는 사용하지 않음)
                    sql_query("UPDATE {$g5['member_table']} SET mb_icon = '' WHERE mb_id = '" . $member['mb_id'] . "'");
                    
                    $response['success'] = true;
                    $response['message'] = '프로필 아이콘이 변경되었습니다.';
                } else {
                    $response['message'] = '아이콘 복사 중 오류가 발생했습니다.';
                }
            } else {
                $response['message'] = '선택한 아이콘 파일을 찾을 수 없습니다.';
            }
        } else {
            $response['message'] = '지원하지 않는 파일 형식입니다.';
        }
    }
    
    // 닉네임 변경
    if (isset($_POST['mb_nick'])) {
        $mb_nick = trim($_POST['mb_nick']);
        
        if ($mb_nick && $mb_nick !== $member['mb_nick']) {
            // 닉네임 중복 확인
            $sql = " select mb_id from {$g5['member_table']} where mb_nick = '" . sql_real_escape_string($mb_nick) . "' and mb_id <> '" . $member['mb_id'] . "' ";
            $row = sql_fetch($sql);
            if ($row['mb_id']) {
                $response['message'] = '이미 사용중인 닉네임입니다.';
            } else {
                // 닉네임 변경 가능 여부 확인
                $nick_change_interval = 30; // 기본 30일
                if (defined('G5_NICK_CHANGE_DAYS')) {
                    $nick_change_interval = G5_NICK_CHANGE_DAYS;
                }
                
                $can_change_nick = !isset($member['mb_1']) || empty($member['mb_1']);
                
                if (!$can_change_nick) {
                    $date_field_result = sql_query("SHOW COLUMNS FROM {$g5['member_table']} LIKE 'mb_nick_date'");
                    $date_field = sql_fetch_array($date_field_result);
                    
                    if ($date_field && isset($member['mb_nick_date']) && $member['mb_nick_date'] && $member['mb_nick_date'] != '0000-00-00 00:00:00') {
                        $nick_last_changed = strtotime($member['mb_nick_date']);
                        $nick_available_at = strtotime("+{$nick_change_interval} days", $nick_last_changed);
                        $can_change_nick = ($nick_available_at <= time());
                    } else {
                        $can_change_nick = true;
                    }
                }
                
                if (!$can_change_nick) {
                    $response['message'] = '닉네임 변경 기간이 지나지 않았습니다.';
                } else {
                    // 닉네임 변경
                    $sql = " update {$g5['member_table']} 
                             set mb_nick = '" . sql_real_escape_string($mb_nick) . "', 
                                 mb_1 = '1'
                             where mb_id = '" . $member['mb_id'] . "' ";
                    
                    if (sql_query($sql)) {
                        $response['success'] = true;
                        $response['message'] = '닉네임이 변경되었습니다.';
                        $response['new_nick'] = $mb_nick;
                    } else {
                        $response['message'] = '닉네임 변경 중 오류가 발생했습니다.';
                    }
                }
            }
        }
    }
    
    // 프로필 이미지 URL 반환
    if ($response['success']) {
        if (file_exists($mb_img_filename)) {
            $response['profile_img'] = G5_DATA_URL.'/member_image/'.substr($member['mb_id'],0,2).'/'.$member['mb_id'].'.gif?'.time();
        } else {
            $response['profile_img'] = G5_THEME_URL.'/skin/mypage/basic/icon/no_profile.gif';
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// 페이지 제목
$g5['title'] = '마이페이지';

// 회원 포인트 정보
$point = number_format($member['mb_point']);

// 최근 게시글 수 (모든 게시판 통합)
$sql = " select count(*) as cnt from {$g5['board_new_table']} where mb_id = '{$member['mb_id']}' and wr_id = wr_parent ";
$row = sql_fetch($sql);
$total_posts = $row['cnt'];

// 최근 댓글 수 (모든 게시판 통합)
$sql = " select count(*) as cnt from {$g5['board_new_table']} where mb_id = '{$member['mb_id']}' and wr_id != wr_parent ";
$row = sql_fetch($sql);
$total_comments = $row['cnt'];

// 스크랩 수
$sql = " select count(*) as cnt from {$g5['scrap_table']} where mb_id = '{$member['mb_id']}' ";
$row = sql_fetch($sql);
$total_scrap = $row['cnt'];

// 쪽지 수
$sql = " select count(*) as cnt from {$g5['memo_table']} where me_recv_mb_id = '{$member['mb_id']}' and me_read_datetime = '0000-00-00 00:00:00' ";
$row = sql_fetch($sql);
$memo_not_read = $row['cnt'];

// 프로필 수정을 위한 추가 정보
$mb_img_dir = G5_DATA_PATH.'/member_image/'.substr($member['mb_id'],0,2);
$mb_img_filename = $mb_img_dir.'/'.$member['mb_id'].'.gif';

// 스킨 설정 확인 (먼저 확인)
$mypage_skin = 'basic'; // 기본값
$skin_config = sql_fetch("SELECT skin_name FROM g5_theme_skin_config WHERE page_type = 'mypage'");
if($skin_config && $skin_config['skin_name']) {
    $mypage_skin = $skin_config['skin_name'];
}

// 프로필 이미지 출력 우선순위
if (isset($member['mb_icon']) && $member['mb_icon']) {
    $current_icon = $member['mb_icon'];
} elseif (file_exists($mb_img_filename)) {
    $current_icon = G5_DATA_URL.'/member_image/'.substr($member['mb_id'],0,2).'/'.$member['mb_id'].'.gif?'.time();
} else {
    $current_icon = G5_THEME_URL.'/skin/mypage/'.$mypage_skin.'/icon/no_profile.gif';
}

// 기본 아이콘 목록
$default_icons = array();
$icon_path = G5_THEME_PATH.'/skin/mypage/'.$mypage_skin.'/icon';
$icon_url = G5_THEME_URL.'/skin/mypage/'.$mypage_skin.'/icon';

// 디렉토리의 모든 png 파일 가져오기
if (is_dir($icon_path)) {
    $files = glob($icon_path . '/*.png');

    // 파일명 정렬
    sort($files);

    foreach ($files as $file) {
        $filename = basename($file);
        // no_profile.gif는 제외
        if ($filename !== 'no_profile.gif' && strpos($filename, '.png') !== false) {
            $default_icons[] = $icon_url . '/' . $filename;
        }
    }
}

// 닉네임 변경 가능 여부 확인
$nick_change_interval = 30; // 기본 30일
if (defined('G5_NICK_CHANGE_DAYS')) {
    $nick_change_interval = G5_NICK_CHANGE_DAYS;
}

$can_change_nick = !isset($member['mb_1']) || empty($member['mb_1']);
$remain_days = 0;

if (!$can_change_nick) {
    // mb_nick_date 필드 존재 여부 확인
    $date_field_result = sql_query("SHOW COLUMNS FROM {$g5['member_table']} LIKE 'mb_nick_date'");
    $date_field = sql_fetch_array($date_field_result);
    
    if ($date_field) {
        // mb_nick_date 필드가 존재하고 값이 있는 경우
        if (isset($member['mb_nick_date']) && $member['mb_nick_date'] && $member['mb_nick_date'] != '0000-00-00 00:00:00') {
            $nick_last_changed = strtotime($member['mb_nick_date']);
            $nick_available_at = strtotime("+{$nick_change_interval} days", $nick_last_changed);
            $can_change_nick = ($nick_available_at <= time());
            $remain_days = ceil(($nick_available_at - time()) / 86400);
        } else {
            // mb_nick_date 값이 없으면 변경 가능
            $can_change_nick = true;
        }
    } else {
        // mb_nick_date 필드가 없으면 변경 가능
        $can_change_nick = true;
    }
}

include_once(G5_PATH.'/head.php');

// 스킨 설정 확인
$mypage_skin = 'basic'; // 기본값
$skin_config = sql_fetch("SELECT skin_name FROM g5_theme_skin_config WHERE page_type = 'mypage'");
if($skin_config && $skin_config['skin_name']) {
    $mypage_skin = $skin_config['skin_name'];
}

// 테마에 mypage 스킨이 있으면 사용
$mypage_skin_path = G5_THEME_PATH.'/skin/mypage/'.$mypage_skin.'/mypage.skin.php';
if(defined('G5_THEME_PATH') && is_file($mypage_skin_path)) {
    include_once($mypage_skin_path);
} else if(is_file(G5_THEME_PATH.'/skin/mypage/basic/mypage.skin.php')) {
    // 설정된 스킨이 없으면 basic 스킨 사용
    include_once(G5_THEME_PATH.'/skin/mypage/basic/mypage.skin.php');
} else {
?>

<div class="mypageWrap">
    <!-- 프로필 섹션 -->
    <div class="profileSection">
        <div class="profileHeader">
            <div class="profileImg">
                <?php echo get_member_profile_img($member['mb_id'], 100, 100); ?>
            </div>
            <div class="profileInfo">
                <h2><?php echo $member['mb_nick'] ?></h2>
                <span class="userLevel">레벨 <?php echo $member['mb_level'] ?></span>
                <div class="joinDate">
                    <i class='bx bx-calendar'></i> 가입일: <?php echo date("Y년 m월 d일", strtotime($member['mb_datetime'])) ?>
                </div>
            </div>
        </div>
        <div class="profileActions">
            <a href="<?php echo G5_BBS_URL ?>/member_confirm.php?url=register_form.php" class="button bg-pr">
                <i class='bx bx-edit'></i> 
                <span class="text">정보수정</span>
            </a>
            <a href="<?php echo G5_BBS_URL ?>/memo.php" target="_blank" class="button shadowline-de" onclick="win_memo(this.href); return false;">
                <i class='bx bx-envelope'></i> 
                <span class="text">쪽지함 <?php if($memo_not_read > 0) echo '<span class="badge">'.$memo_not_read.'</span>'; ?></span>
            </a>
            <a href="<?php echo G5_BBS_URL ?>/logout.php" class="button shadowline-de">
                <i class='bx bx-log-out'></i> 
                <span class="text">로그아웃</span>
            </a>
        </div>
    </div>

    <!-- 통계 카드 -->
    <div class="statsGrid">
        <div class="statCard points">
            <div class="statIcon">
                <i class='bx bx-coin-stack'></i>
            </div>
            <h3>보유 포인트</h3>
            <div class="statValue"><?php echo $point ?>P</div>
            <a href="<?php echo G5_BBS_URL ?>/point.php" target="_blank" class="statLink" onclick="win_point(this.href); return false;">
                내역 보기 <i class='bx bx-chevron-right'></i>
            </a>
        </div>

        <div class="statCard posts">
            <div class="statIcon">
                <i class='bx bx-edit-alt'></i>
            </div>
            <h3>작성한 글</h3>
            <div class="statValue"><?php echo number_format($total_posts) ?></div>
            <a href="<?php echo G5_BBS_URL ?>/new.php?mb_id=<?php echo $member['mb_id'] ?>" class="statLink">
                내 글 보기 <i class='bx bx-chevron-right'></i>
            </a>
        </div>

        <div class="statCard comments">
            <div class="statIcon">
                <i class='bx bx-comment-dots'></i>
            </div>
            <h3>작성한 댓글</h3>
            <div class="statValue"><?php echo number_format($total_comments) ?></div>
        </div>

        <div class="statCard scrap">
            <div class="statIcon">
                <i class='bx bx-bookmark'></i>
            </div>
            <h3>스크랩</h3>
            <div class="statValue"><?php echo number_format($total_scrap) ?></div>
            <a href="<?php echo G5_BBS_URL ?>/scrap.php" target="_blank" class="statLink" onclick="win_scrap(this.href); return false;">
                스크랩 보기 <i class='bx bx-chevron-right'></i>
            </a>
        </div>
    </div>

    <!-- 메뉴 섹션 -->
    <div class="menuSection">
        <h3>마이 메뉴</h3>
        <div class="menuGrid">
            <a href="<?php echo G5_BBS_URL ?>/member_confirm.php?url=register_form.php" class="menuItem">
                <i class='bx bx-user'></i>
                <span>회원정보 수정</span>
            </a>
            <a href="<?php echo G5_BBS_URL ?>/profile.php?mb_id=<?php echo $member['mb_id'] ?>" class="menuItem">
                <i class='bx bx-id-card'></i>
                <span>내 프로필</span>
            </a>
            <a href="<?php echo G5_BBS_URL ?>/memo.php" target="_blank" class="menuItem" onclick="win_memo(this.href); return false;">
                <i class='bx bx-envelope'></i>
                <span>쪽지함</span>
            </a>
            <a href="<?php echo G5_BBS_URL ?>/point.php" target="_blank" class="menuItem" onclick="win_point(this.href); return false;">
                <i class='bx bx-coin-stack'></i>
                <span>포인트 내역</span>
            </a>
            <a href="<?php echo G5_BBS_URL ?>/scrap.php" target="_blank" class="menuItem" onclick="win_scrap(this.href); return false;">
                <i class='bx bx-bookmark'></i>
                <span>스크랩 목록</span>
            </a>
            <a href="<?php echo G5_BBS_URL ?>/member_confirm.php?url=member_leave.php" class="menuItem">
                <i class='bx bx-user-x'></i>
                <span>회원 탈퇴</span>
            </a>
        </div>
    </div>
</div>

<script>
// 팝업 함수들
function win_memo(url) {
    window.open(url, "win_memo", "left=50,top=50,width=620,height=700,scrollbars=1");
}

function win_point(url) {
    window.open(url, "win_point", "left=50,top=50,width=750,height=600,scrollbars=1");
}

function win_scrap(url) {
    window.open(url, "win_scrap", "left=50,top=50,width=800,height=600,scrollbars=1");
}
</script>

<?php
}
include_once(G5_PATH.'/tail.php');
?>