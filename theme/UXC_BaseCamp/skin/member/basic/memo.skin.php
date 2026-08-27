<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/style.css">', 0);
?>

<!-- 쪽지 목록 시작 { -->
<div class="memoListWrap">
    <div class="memoHeader">
        <h2><i class='bx bx-message-square-dots'></i> <?php echo $g5['title'] ?></h2>
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
                    <?php if ($kind == 'recv') { ?>
                    <span class="badge"><?php echo $total_count ?></span>
                    <?php } ?>
                </a>
            </li>
            <li class="<?php if ($kind == 'send') echo 'active'; ?>">
                <a href="./memo.php?kind=send">
                    <i class='bx bx-send'></i> 보낸쪽지
                    <?php if ($kind == 'send') { ?>
                    <span class="badge"><?php echo $total_count ?></span>
                    <?php } ?>
                </a>
            </li>
            <li>
                <a href="./memo_form.php">
                    <i class='bx bx-edit'></i> 쪽지쓰기
                </a>
            </li>
        </ul>
    </div>

    <div class="memoListBox">
        <?php
        if(count($list) > 0) {
            for ($i=0; $i<count($list); $i++) {
                $readed = (substr($list[$i]['me_read_datetime'],0,1) == 0) ? 'unread' : '';
                $memo_preview = utf8_strcut(strip_tags($list[$i]['me_memo']), 50, '..');
        ?>
        <div class="memoItem bg-wh round-m padding-m <?php echo $readed; ?>">
            <div class="memoInfo">
                <div class="userImg">
                    <?php echo get_member_profile_img($list[$i]['mb_id']); ?>
                </div>
                <div class="memoContent">
                    <div class="memoHeader">
                        <strong class="userName"><?php echo $list[$i]['name']; ?></strong>
                        <span class="memoDate"><?php echo $list[$i]['send_datetime']; ?></span>
                    </div>
                    <a href="<?php echo $list[$i]['view_href']; ?>" class="memoText">
                        <?php echo $memo_preview; ?>
                    </a>
                </div>
                <div class="memoActions">
                    <?php if (!$readed) { ?>
                    <span class="labelBox bg-pr color-wh round-x">New</span>
                    <?php } ?>
                    <a href="<?php echo $list[$i]['del_href']; ?>" onclick="del(this.href); return false;" class="button shadowline-de round-s color-gray-800 sBtn">
                        <i class='bx bx-trash'></i>
                        <span class="sound_only">삭제</span>
                    </a>
                </div>
            </div>
        </div>
        <?php 
            }
        } else {
            echo '<div class="emptyBox bg-wh round-m padding-m">';
            echo '<p class="emptyMessage"><i class="bx bx-message-square-x"></i> 쪽지가 없습니다.</p>';
            echo '</div>';
        }
        ?>
    </div>

    <!-- 페이징 -->
    <div class="listFooter">
        <?php echo $write_pages; ?>
    </div>

    <div class="memoFooter">
        <div class="infoText">
            <i class='bx bx-info-circle'></i>
            쪽지 보관일수는 최장 <strong><?php echo $config['cf_memo_del'] ?></strong>일 입니다.
        </div>
    </div>
</div>
<!-- } 쪽지 목록 끝 -->