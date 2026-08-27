<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
$nick = get_sideview($mb['mb_id'], $mb['mb_nick'], $mb['mb_email'], $mb['mb_homepage']);
if($kind == "recv") {
    $kind_str = "보낸";
    $kind_date = "받은";
}
else {
    $kind_str = "받는";
    $kind_date = "보낸";
}

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/style.css">', 0);
?>

<!-- 쪽지보기 시작 { -->
<div class="memoViewWrap memoListWrap">
    <div class="memoHeader">
        <h2><i class='bx bx-message-detail'></i> <?php echo $g5['title'] ?></h2>
        <button type="button" class="button shadowline-de round-s bxicon color-gray-800 sBtn" onclick="window.close();">
            <i class='bx bx-x'></i>
            <span class="text">닫기</span>
        </button>
    </div>
    
    <div class="memoTabWrap">
        <ul class="memoTab">
            <li class="<?php if ($kind == 'recv') echo 'active'; ?>">
                <a href="./memo.php?kind=recv">
                    <i class='bx bx-inbox'></i> 받은쪽지
                </a>
            </li>
            <li class="<?php if ($kind == 'send') echo 'active'; ?>">
                <a href="./memo.php?kind=send">
                    <i class='bx bx-send'></i> 보낸쪽지
                </a>
            </li>
            <li>
                <a href="./memo_form.php">
                    <i class='bx bx-edit'></i> 쪽지쓰기
                </a>
            </li>
        </ul>
    </div>

    
    <div class="memoViewBox bg-wh round-m">
        <div class="memoViewHeader">
            <div class="userInfoSection">
                <div class="userInfo">
                    <div class="userImg">
                        <?php echo get_member_profile_img($mb['mb_id']); ?>
                    </div>
                    <div class="userData">
                        <div class="userTop">
                            <span class="userName"><?php echo $nick ?></span>
                            <span class="memoLabel <?php echo $kind == 'recv' ? 'received' : 'sent'; ?>">
                                <?php echo $kind == 'recv' ? '받은 쪽지' : '보낸 쪽지'; ?>
                            </span>
                        </div>
                        <div class="memoDate">
                            <i class='bx bx-time'></i> 
                            <span class="sound_only"><?php echo $kind_date ?>시간</span>
                            <?php echo $memo['me_send_datetime'] ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="memoActions">
                <a href="<?php echo $list_link ?>" class="button bg-wh color-bl shadowline-de round-s sBtn">
                    <span class="text">목록</span>
                </a>
                <a href="<?php echo $del_link; ?>" onclick="del(this.href); return false;" class="button shadowline-de round-s bxicon color-danger sBtn">
                    <span class="text">삭제</span>
                </a>
            </div>
            
          
        </div>

        <div class="memoViewContent">
            <?php echo conv_content($memo['me_memo'], 0) ?>
        </div>

        <div class="memoViewNav">
            <?php if($prev_link) { ?>
            <a href="<?php echo $prev_link ?>" class="navPrev">
                <i class='bx bx-chevron-left'></i> 이전쪽지
            </a>
            <?php } ?>
            <?php if($next_link) { ?>
            <a href="<?php echo $next_link ?>" class="navNext">
                다음쪽지 <i class='bx bx-chevron-right'></i>
            </a>
            <?php } ?>
        </div>
    </div>

    <div class="memoViewFooter">
        <?php if ($kind == 'recv') { ?>
        <a href="./memo_form.php?me_recv_mb_id=<?php echo $mb['mb_id'] ?>&amp;me_id=<?php echo $memo['me_id'] ?>" class="button bg-pr color-wh round-m bxicon mBtn">
            <i class='bx bx-reply'></i> 답장
        </a>
        <?php } ?>
    </div>
</div>
<!-- } 쪽지보기 끝 -->