<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/style.css">', 0);
?>

<!-- 스크랩 목록 래퍼 -->
<div class="scrapListWrap">
    
    <!-- 헤더 영역 -->
    <div class="scrapHeader">
        <h2><i class='bx bx-bookmark'></i> <?php echo $g5['title'] ?></h2>
        <button type="button" class="button shadowline-de round-s bxicon color-gray-800 sBtn" onclick="window.close();">
            <i class='bx bx-x'></i>
            <span class="text">닫기</span>
        </button>
    </div>

    <!-- 스크랩 목록 -->
    <div class="scrapListBox">
        <?php 
        if(count($list) > 0) {
            for ($i=0; $i<count($list); $i++) {  
        ?>
        <div class="scrapItem bg-wh round-m padding-m">
            <div class="scrapInfo">
                <div class="scrapContent">
                    <a href="<?php echo $list[$i]['opener_href_wr_id'] ?>" class="scrapTitle" target="_blank" onclick="opener.document.location.href='<?php echo $list[$i]['opener_href_wr_id'] ?>'; return false;">
                        <?php echo $list[$i]['subject'] ?>
                    </a>
                    <div class="scrapMeta">
                        <a href="<?php echo $list[$i]['opener_href'] ?>" class="scrapBoard" target="_blank" onclick="opener.document.location.href='<?php echo $list[$i]['opener_href'] ?>'; return false;">
                            <i class='bx bx-folder'></i> <?php echo $list[$i]['bo_subject'] ?>
                        </a>
                        <span class="scrapDate">
                            <i class='bx bx-time'></i> <?php echo date("y-m-d H:i", strtotime($list[$i]['ms_datetime'])); ?>
                        </span>
                    </div>
                </div>
                <div class="scrapAction">
                    <a href="<?php echo $list[$i]['del_href'];  ?>" onclick="del(this.href); return false;" class="button shadowline-de round-s bxicon color-gray-800 sBtn">
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
            echo '<p class="emptyMessage"><i class="bx bx-bookmark-x"></i> 스크랩한 글이 없습니다.</p>';
            echo '</div>';
        }
        ?>
    </div>
    
    <!-- 페이징 -->
    <div class="listFooter">
        <?php echo get_paging($config['cf_write_pages'], $page, $total_page, "?$qstr&amp;page="); ?>
    </div>

</div>
<!-- } 스크랩 목록 끝 -->