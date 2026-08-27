<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.G5_THEME_URL.'/skin/mypage/basic/style.css">', 0);
?>

<div class="mypageWrap">
    <!-- 프로필 영역 -->
    <div class="profileBox cardBox">
        <div class="titleBox">
            <div class="profileInfo">
                <div class="profileImg" id="mainProfileImg">
                    <?php echo get_member_profile_img($member['mb_id'], 60, 60); ?>
                </div>
                <div class="contentInfo">
                    <strong class="title" id="mainProfileNick"><?php echo get_text($member['mb_nick']) ?></strong>
                    <p class="desc">
                        <span class="labelBox bg-pr color-wh-only round-s">
                            <i class='bx bx-star'></i> Level <?php echo $member['mb_level'] ?>
                        </span>
                        <span class="date"><i class='bx bx-calendar'></i> <?php echo date("Y.m.d", strtotime($member['mb_datetime'])) ?> 가입</span>
                    </p>
                </div>
            </div>
            <div class="buttonGroup">
                <a href="<?php echo G5_BBS_URL ?>/member_confirm.php?url=register_form.php" class="button mBtn shadowline-de round-s bxicon">
                    <i class='bx bx-edit'></i>
                    <span class="text">정보수정</span>
                </a>
                <a href="<?php echo G5_BBS_URL ?>/memo.php" target="_blank" onclick="win_memo(this.href); return false;" class="button mBtn shadowline-de round-s bxicon <?php echo $memo_not_read > 0 ? 'has-badge' : ''; ?>">
                    <i class='bx bx-envelope'></i>
                    <span class="text">쪽지</span>
                    <?php if($memo_not_read > 0) { ?>
                    <span class="badge"><?php echo $memo_not_read ?></span>
                    <?php } ?>
                </a>
                <?php if($is_admin == 'super') { ?>
                <button type="button" onclick="openSkinModal('mypage')" class="button mBtn shadowline-de round-s bxicon">
                    <i class='bx bx-palette'></i>
                    <span class="text">스킨변경</span>
                </button>
                <?php } ?>
            </div>
        </div>
    </div>

    <!-- 활동 통계 -->
    <div class="statsBox">
        <h3 class="sectionTitle">
            <i class='bx bx-bar-chart-alt-2'></i> 나의 활동
        </h3>
        <div class="statsGrid">
            <a href="<?php echo G5_BBS_URL ?>/point.php" target="_blank" onclick="win_point(this.href); return false;" class="statItem cardBox">
                <div class="statIcon points">
                    <i class='bx bx-coin-stack'></i>
                </div>
                <div class="statInfo">
                    <span class="label">포인트</span>
                    <strong class="value"><?php echo $point ?>P</strong>
                </div>
            </a>

            <a href="<?php echo G5_BBS_URL ?>/new.php?mb_id=<?php echo $member['mb_id'] ?>" class="statItem cardBox">
                <div class="statIcon posts">
                    <i class='bx bx-edit-alt'></i>
                </div>
                <div class="statInfo">
                    <span class="label">작성글</span>
                    <strong class="value"><?php echo number_format($total_posts) ?></strong>
                </div>
            </a>

            <div class="statItem cardBox">
                <div class="statIcon comments">
                    <i class='bx bx-comment-dots'></i>
                </div>
                <div class="statInfo">
                    <span class="label">댓글</span>
                    <strong class="value"><?php echo number_format($total_comments) ?></strong>
                </div>
            </div>

            <a href="<?php echo G5_BBS_URL ?>/scrap.php" target="_blank" onclick="win_scrap(this.href); return false;" class="statItem cardBox">
                <div class="statIcon scrap">
                    <i class='bx bx-bookmark'></i>
                </div>
                <div class="statInfo">
                    <span class="label">스크랩</span>
                    <strong class="value"><?php echo number_format($total_scrap) ?></strong>
                </div>
            </a>
        </div>
    </div>

    <!-- 최근 활동 -->
    <div class="recentActivityBox">
        <!-- 최근 작성글 -->
        <div class="recentBox">
            <h3 class="sectionTitle">
                <i class='bx bx-edit-alt'></i> 최근 작성글
                <a href="<?php echo G5_BBS_URL ?>/new.php?mb_id=<?php echo $member['mb_id'] ?>" class="viewMore">전체보기 <i class='bx bx-chevron-right'></i></a>
            </h3>
        <?php
        // 최근 작성글 가져오기
        $sql = " SELECT a.*, b.bo_subject 
                FROM {$g5['board_new_table']} a 
                LEFT JOIN {$g5['board_table']} b ON a.bo_table = b.bo_table 
                WHERE a.mb_id = '".sql_escape_string($member['mb_id'])."' 
                AND a.wr_id = a.wr_parent 
                AND b.bo_use_search = 1 
                ORDER BY a.bn_id DESC 
                LIMIT 5 ";
        $result = sql_query($sql);
        $post_list = array();
        
        while($row = sql_fetch_array($result)) {
            $tmp_write_table = $g5['write_prefix'] . $row['bo_table'];
            $row2 = sql_fetch(" SELECT wr_subject, wr_datetime, wr_comment FROM {$tmp_write_table} WHERE wr_id = '".sql_escape_string($row['wr_id'])."' ");
            if($row2) {
                $post_list[] = array(
                    'bo_table' => $row['bo_table'],
                    'bo_subject' => $row['bo_subject'],
                    'wr_id' => $row['wr_id'],
                    'wr_subject' => $row2['wr_subject'],
                    'wr_datetime' => $row2['wr_datetime'],
                    'wr_comment' => $row2['wr_comment']
                );
            }
        }
        $post_count = count($post_list);
        
        if($post_count > 0) {
        ?>
        <div class="recentList">
            <?php foreach($post_list as $row) { ?>
            <div class="recentItem cardBox">
                <div class="recentInfo">
                    <h4 class="recentTitle">
                        <a href="<?php echo get_pretty_url($row['bo_table'], $row['wr_id']); ?>">
                            <?php echo get_text($row['wr_subject']) ?>
                        </a>
                    </h4>
                    <div class="recentMeta">
                        <span class="board"><i class='bx bx-folder'></i> <?php echo get_text($row['bo_subject']) ?></span>
                        <span class="date"><i class='bx bx-time'></i> <?php echo date('Y.m.d', strtotime($row['wr_datetime'])) ?></span>
                        <?php if($row['wr_comment'] > 0) { ?>
                        <span class="comment"><i class='bx bx-comment'></i> <?php echo $row['wr_comment'] ?></span>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
        <?php } else { ?>
        <div class="emptyBox cardBox">
            <div class="emptyContent">
                <i class='bx bx-edit-alt'></i>
                <p>작성한 글이 없습니다.</p>
            </div>
        </div>
        <?php } ?>
        </div>

        <!-- 최근 댓글 -->
        <div class="recentBox">
            <h3 class="sectionTitle">
                <i class='bx bx-comment-dots'></i> 최근 댓글
            </h3>
        <?php
        // 최근 댓글 가져오기
        $sql = " SELECT a.*, b.bo_subject 
                FROM {$g5['board_new_table']} a 
                LEFT JOIN {$g5['board_table']} b ON a.bo_table = b.bo_table 
                WHERE a.mb_id = '".sql_escape_string($member['mb_id'])."' 
                AND a.wr_id <> a.wr_parent 
                AND b.bo_use_search = 1 
                ORDER BY a.bn_id DESC 
                LIMIT 5 ";
        $result = sql_query($sql);
        $comment_list = array();
        
        while($row = sql_fetch_array($result)) {
            $tmp_write_table = $g5['write_prefix'] . $row['bo_table'];
            $row2 = sql_fetch(" SELECT wr_content, wr_datetime FROM {$tmp_write_table} WHERE wr_id = '".sql_escape_string($row['wr_id'])."' ");
            if($row2) {
                $comment_list[] = array(
                    'bo_table' => $row['bo_table'],
                    'bo_subject' => $row['bo_subject'],
                    'wr_parent' => $row['wr_parent'],
                    'wr_id' => $row['wr_id'],
                    'wr_content' => $row2['wr_content'],
                    'wr_datetime' => $row2['wr_datetime']
                );
            }
        }
        $comment_count = count($comment_list);
        
        if($comment_count > 0) {
        ?>
        <div class="recentList">
            <?php foreach($comment_list as $row) { ?>
            <div class="recentItem cardBox">
                <div class="recentInfo">
                    <h4 class="recentTitle">
                        <a href="<?php echo get_pretty_url($row['bo_table'], $row['wr_parent']); ?>#c_<?php echo $row['wr_id'] ?>">
                            <?php echo cut_str(strip_tags($row['wr_content']), 50, '...') ?>
                        </a>
                    </h4>
                    <div class="recentMeta">
                        <span class="board"><i class='bx bx-folder'></i> <?php echo get_text($row['bo_subject']) ?></span>
                        <span class="date"><i class='bx bx-time'></i> <?php echo date('Y.m.d', strtotime($row['wr_datetime'])) ?></span>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
        <?php } else { ?>
        <div class="emptyBox cardBox">
            <div class="emptyContent">
                <i class='bx bx-comment-dots'></i>
                <p>작성한 댓글이 없습니다.</p>
            </div>
        </div>
        <?php } ?>
        </div>
    </div>

    <!-- 메뉴 목록 -->
    <div class="menuBox">
        <h3 class="sectionTitle">
            <i class='bx bx-grid-alt'></i> 메뉴
        </h3>
        <div class="menuList">
            <a href="<?php echo G5_BBS_URL ?>/member_confirm.php?url=register_form.php" class="menuItem cardBox">
                <div class="titleBox">
                    <div class="contentInfo">
                        <strong class="title">
                            <i class='bx bx-user-circle'></i> 회원정보 수정
                        </strong>
                        <p class="desc">프로필 및 개인정보 변경</p>
                    </div>
                    <div class="menuArrow">
                        <i class='bx bx-chevron-right'></i>
                    </div>
                </div>
            </a>

            <a href="<?php echo G5_BBS_URL ?>/point.php" target="_blank" onclick="win_point(this.href); return false;" class="menuItem cardBox">
                <div class="titleBox">
                    <div class="contentInfo">
                        <strong class="title">
                            <i class='bx bx-coin-stack'></i> 포인트 내역
                        </strong>
                        <p class="desc">보유 포인트: <?php echo $point ?>P</p>
                    </div>
                    <div class="menuArrow">
                        <i class='bx bx-chevron-right'></i>
                    </div>
                </div>
            </a>

            <a href="<?php echo G5_BBS_URL ?>/scrap.php" target="_blank" onclick="win_scrap(this.href); return false;" class="menuItem cardBox">
                <div class="titleBox">
                    <div class="contentInfo">
                        <strong class="title">
                            <i class='bx bx-bookmark'></i> 스크랩 목록
                        </strong>
                        <p class="desc">저장한 게시물 <?php echo number_format($total_scrap) ?>개</p>
                    </div>
                    <div class="menuArrow">
                        <i class='bx bx-chevron-right'></i>
                    </div>
                </div>
            </a>

            <a href="<?php echo G5_BBS_URL ?>/memo.php" target="_blank" onclick="win_memo(this.href); return false;" class="menuItem cardBox <?php echo $memo_not_read > 0 ? 'has-new' : ''; ?>">
                <div class="titleBox">
                    <div class="contentInfo">
                        <strong class="title">
                            <i class='bx bx-envelope'></i> 쪽지함
                            <?php if($memo_not_read > 0) { ?>
                            <span class="labelBox bg-danger color-wh round-s">새쪽지 <?php echo $memo_not_read ?>개</span>
                            <?php } ?>
                        </strong>
                        <p class="desc">받은 쪽지 확인 및 전송</p>
                    </div>
                    <div class="menuArrow">
                        <i class='bx bx-chevron-right'></i>
                    </div>
                </div>
            </a>

            <a href="<?php echo G5_BBS_URL ?>/faq.php" class="menuItem cardBox">
                <div class="titleBox">
                    <div class="contentInfo">
                        <strong class="title">
                            <i class='bx bx-help-circle'></i> 자주 묻는 질문
                        </strong>
                        <p class="desc">FAQ 및 도움말</p>
                    </div>
                    <div class="menuArrow">
                        <i class='bx bx-chevron-right'></i>
                    </div>
                </div>
            </a>

            <a href="<?php echo G5_BBS_URL ?>/qalist.php" class="menuItem cardBox">
                <div class="titleBox">
                    <div class="contentInfo">
                        <strong class="title">
                            <i class='bx bx-question-mark'></i> 1:1문의
                        </strong>
                        <p class="desc">관리자에게 문의하기</p>
                    </div>
                    <div class="menuArrow">
                        <i class='bx bx-chevron-right'></i>
                    </div>
                </div>
            </a>
            <a href="<?php echo G5_BBS_URL ?>/member_confirm.php?url=member_leave.php" class="menuItem cardBox danger">
                <div class="titleBox">
                    <div class="contentInfo">
                        <strong class="title">
                            <i class='bx bx-user-x'></i> 회원 탈퇴
                        </strong>
                        <p class="desc">계정을 영구적으로 삭제합니다</p>
                    </div>
                    <div class="menuArrow">
                        <i class='bx bx-chevron-right'></i>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

<?php if($is_admin == 'super') { ?>
<!-- 스킨 변경 모달 -->
<div id="skinModal" class="multiModal" style="display: none;">
    <div class="modalBox w400">
        <!-- mbHeader -->
        <div class="mbHeader">
            <div class="title">
                <h2>마이페이지 스킨 변경</h2>
            </div>
            <button type="button" class="modalCloseBtn" onclick="closeSkinModal()">
                <i class='bx bx-x'></i>
            </button>
        </div>
        <!-- mbBody -->
        <div class="mbBody bg-clg">
            <div class="mbBodyContents">
                <form id="skinForm">
                    <input type="hidden" name="page_type" value="mypage">
                    <div class="configItem">
                        <div class="configLabel">
                            <label for="skin_select" class="sr-only">스킨 선택</label>
                        </div>
                        <div class="configInput">
                            <select name="skin_name" id="skin_select" class="frm_input full_input">
                                <?php
                                // 현재 선택된 스킨 확인
                                $current_skin = 'basic';
                                $skin_config = sql_fetch("SELECT skin_name FROM g5_theme_skin_config WHERE page_type = 'mypage'");
                                if($skin_config) {
                                    $current_skin = $skin_config['skin_name'];
                                }
                                
                                // 스킨 목록 가져오기
                                $skin_dir = G5_THEME_PATH.'/skin/mypage';
                                $skins = array();
                                if(is_dir($skin_dir)) {
                                    $handle = opendir($skin_dir);
                                    while($file = readdir($handle)) {
                                        if($file == '.' || $file == '..') continue;
                                        if(is_dir($skin_dir.'/'.$file)) {
                                            $skins[] = $file;
                                        }
                                    }
                                    closedir($handle);
                                    sort($skins);
                                }
                                
                                foreach($skins as $skin) {
                                    $selected = ($skin == $current_skin) ? 'selected' : '';
                                    echo '<option value="'.$skin.'" '.$selected.'>'.$skin.'</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- buttonWrap -->
        <div class="buttonWrap">
            <button type="button" onclick="closeSkinModal()" class="button bg-wh color-bl shadowline-de round-m bxicon sBtn">
                취소
            </button>
            <button type="button" onclick="saveSkin()" class="button bg-pr color-wh round-m bxicon sBtn">
                저장
            </button>
        </div>
    </div>
</div>
<?php } ?>

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

<?php if($is_admin == 'super') { ?>
// 스킨 변경 관련 함수
function openSkinModal() {
    document.getElementById('skinModal').style.display = 'flex';
}

function closeSkinModal() {
    document.getElementById('skinModal').style.display = 'none';
}

function saveSkin() {
    const formData = new FormData(document.getElementById('skinForm'));
    
    fetch('<?php echo G5_BBS_URL ?>/uxc_skin_update.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            alert(data.message || '스킨이 변경되었습니다.');
            location.reload();
        } else {
            alert(data.message || '스킨 변경에 실패했습니다.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('오류가 발생했습니다.');
    });
}
<?php } ?>
</script>