<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$latest_skin_url.'/style.css">', 0);
$list_count = (is_array($list) && $list) ? count($list) : 0;
?>

<div class="uxc_list" data-latest="uxc_list">
    <ul>
    <?php for ($i=0; $i<$list_count; $i++) {  ?>
        <li class="uxc_list_li">
            <?php
            // 비밀글 아이콘 - Boxicons로 변경
            if ($list[$i]['icon_secret']) echo "<i class=\"bx bx-lock-alt\" aria-hidden=\"true\"></i><span class=\"sound_only\">비밀글</span> ";

            // 제목 링크 - 멀티 게시판 지원
            $link_bo_table = isset($list[$i]['bo_table']) ? $list[$i]['bo_table'] : $bo_table;
            echo "<a href=\"".get_pretty_url($link_bo_table, $list[$i]['wr_id'])."\">";
			// 아이콘들
            if ($list[$i]['icon_hot']) echo "<span class=\"hot_icon i_con\">H<span class=\"sound_only\">인기글</span></span>";
            if ($list[$i]['icon_new']) echo "<span class=\"new_icon i_con\">N<span class=\"sound_only\">새글</span></span>";
            if ($list[$i]['is_notice'])
                echo "<strong>".$list[$i]['subject']."</strong>";
            else
                echo $list[$i]['subject'];
            
            // 댓글 수
            if ($list[$i]['comment_cnt']) echo "<span class=\"lt_cmt\">댓글 ".$list[$i]['comment_cnt']."</span>";

            
            
            echo "</a>";
            
            ?>
            
            <!-- 메타 정보 -->
            <div class="lt_info">
                <div class="lt_info_left">
                    <?php if (isset($list[$i]['bo_subject']) && $list[$i]['bo_subject']) { ?>
                    <span class="lt_board"><i class='bx bx-folder'></i> <?php echo $list[$i]['bo_subject'] ?></span>
                    <?php } ?>
                    <span class="lt_nick"><i class='bx bx-user'></i> <?php echo $list[$i]['name'] ?></span>
                    <?php
                        // 첨부파일 & 링크 아이콘
                        echo $list[$i]['icon_reply']." ";
                        if (isset($list[$i]['icon_file']) && $list[$i]['icon_file']) echo " <i class=\"bx bx-file\" aria-hidden=\"true\"></i>" ;
                        if ($list[$i]['icon_link']) echo " <i class=\"bx bx-link-alt\" aria-hidden=\"true\"></i>" ;
                    ?>
                </div>
                <div class="lt_info_right">
                    <span class="lt_date"><i class='bx bx-time'></i> <?php echo $list[$i]['datetime2'] ?></span>
                </div>
            </div>
        </li>
    <?php }  ?>
    
    <?php if ($list_count == 0) { // 게시물이 없을 때  ?>
    <li class="empty_li">
        <i class="bx bx-info-circle"></i>
        게시물이 없습니다.
    </li>
    <?php }  ?>
    </ul>
</div>