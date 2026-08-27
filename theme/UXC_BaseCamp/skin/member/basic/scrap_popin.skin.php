<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/style.css">', 0);
?>

<!-- 스크랩 팝업 래퍼 -->
<div class="scrapPopinWrap">
    
    <!-- 헤더 영역 -->
    <div class="scrapPopinHeader">
        <h2><i class='bx bx-bookmark-plus'></i> 스크랩하기</h2>
        <button type="button" class="button shadowline-de round-s bxicon color-gray-800 sBtn" onclick="window.close();">
            <i class='bx bx-x'></i>
            <span class="text">닫기</span>
        </button>
    </div>

    <!-- 스크랩 폼 -->
    <form name="f_scrap_popin" action="./scrap_popin_update.php" method="post">
        <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
        <input type="hidden" name="wr_id" value="<?php echo $wr_id ?>">
        
        <!-- 스크랩 대상 글 정보 -->
        <div class="scrapTargetInfo bg-gray-100 round-m padding-m">
            <div class="targetLabel">
                <i class='bx bx-file'></i> 스크랩할 글
            </div>
            <div class="targetTitle">
                <?php echo get_text(cut_str($write['wr_subject'], 255)) ?>
            </div>
        </div>

        <!-- 스크랩 댓글 입력 -->
        <div class="scrapCommentBox">
            <label for="wr_content" class="commentLabel">
                <i class='bx bx-message-square-add'></i> 스크랩 댓글
            </label>
            <textarea name="wr_content" id="wr_content" class="commentTextarea" placeholder="스크랩하시면서 감사 혹은 격려의 댓글을 남기실 수 있습니다."></textarea>
            <div class="commentHelp">
                <i class='bx bx-info-circle'></i>
                <span>댓글을 남기시면 글 작성자에게 알림이 전송됩니다.</span>
            </div>
        </div>

        <!-- 버튼 영역 -->
        <div class="scrapPopinButtons">
            <button type="button" class="button shadowline-de round-s mBtn" onclick="window.close();">
                <i class='bx bx-x'></i>
                <span class="text">취소</span>
            </button>
            <button type="submit" class="button bg-pr color-wh round-s mBtn">
                <i class='bx bx-bookmark-plus'></i>
                <span class="text">스크랩 확인</span>
            </button>
        </div>
    </form>

</div>
<!-- } 스크랩 팝업 끝 -->