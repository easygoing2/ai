<?php
$sub_menu = '790100';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, 'r');
$g5['title'] = '개인 캘린더 환경설정';
include_once(G5_ADMIN_PATH.'/admin.head.php');

if (!wzc_schema_installed()) {
?>
<div class="local_desc01 local_desc"><p>개인 캘린더 DB가 아직 설치되지 않았습니다.</p></div>
<div class="btn_fixed_top"><a href="./wzc_install.php" class="btn_submit btn">설치 페이지로 이동</a></div>
<?php
    include_once(G5_ADMIN_PATH.'/admin.tail.php');
    exit;
}

$wzc_config = wzc_get_config();
$board = sql_fetch("SELECT bo_table FROM `{$g5['board_table']}` WHERE bo_table='".sql_escape_string(WZC_BOARD_TABLE)."'", false);
$menu_row = sql_fetch("SELECT me_id FROM `{$g5['menu_table']}` WHERE me_name='내 캘린더' LIMIT 1", false);
?>
<div class="local_ov01 local_ov">
    <span class="btn_ov01"><span class="ov_txt">스키마 버전</span><span class="ov_num"><?php echo (int)$wzc_config['wcf_schema_version']; ?></span></span>
    <span class="btn_ov01"><span class="ov_txt">전용 게시판</span><span class="ov_num"><?php echo $board ? '연결됨' : '미연결'; ?></span></span>
    <span class="btn_ov01"><span class="ov_txt">상단 메뉴</span><span class="ov_num"><?php echo $menu_row ? '연결됨' : '미연결'; ?></span></span>
</div>

<form name="fwzcconfig" method="post" action="./wzc_config_update.php">
    <input type="hidden" name="token" value="<?php echo get_admin_token(); ?>">
    <div class="tbl_frm01 tbl_wrap">
        <table>
            <caption>개인 캘린더 환경설정</caption>
            <colgroup><col class="grid_4"><col></colgroup>
            <tbody>
                <tr>
                    <th scope="row">기능 사용</th>
                    <td>
                        <label><input type="radio" name="wcf_use" value="1" <?php echo get_checked((int)$wzc_config['wcf_use'], 1); ?>> 사용</label>
                        <label><input type="radio" name="wcf_use" value="0" <?php echo get_checked((int)$wzc_config['wcf_use'], 0); ?>> 사용 안 함</label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="wcf_max_events">회원별 최대 일정 수</label></th>
                    <td>
                        <input type="number" name="wcf_max_events" id="wcf_max_events" value="<?php echo (int)$wzc_config['wcf_max_events']; ?>" min="10" max="100000" class="frm_input" required>
                        <span class="frm_info">삭제하지 않은 일정 기준입니다. 기본값은 5,000개입니다.</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="btn_fixed_top">
        <a href="<?php echo WZC_BOARD_URL; ?>" class="btn btn_02" target="_blank" rel="noopener">사용자 화면</a>
        <a href="./wzc_install.php" class="btn btn_03">설치 상태 복구</a>
        <button type="submit" class="btn_submit btn">설정 저장</button>
    </div>
</form>
<?php include_once(G5_ADMIN_PATH.'/admin.tail.php'); ?>
