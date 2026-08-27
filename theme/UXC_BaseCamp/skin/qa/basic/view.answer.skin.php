<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>

<!-- 답변 섹션 -->
<div class="qaAnswerWrap">
    <div class="answerHeader">
        <h2><i class='bx bx-comment-check'></i> 답변</h2>
        <div class="answerInfo">
            <span class="datetime"><i class="bx bx-time"></i> <?php echo date("y-m-d H:i", strtotime($answer['qa_datetime'])) ?></span>
            
            <?php if ( $answer_update_href || $answer_delete_href ){ ?>
            <div class="answerActions">
                <button type="button" class="btn_more_opt button shadowline-de round-s bxicon color-gray-800 sBtn" title="답변 옵션">
                    <i class="bx bx-dots-vertical-rounded"></i>
                    <span class="sound_only">답변 옵션</span>
                </button>
                <ul class="more_opt_list">
                    <?php if($answer_update_href) { ?>
                    <li><a href="<?php echo $answer_update_href; ?>" title="답변수정"><i class='bx bx-edit'></i> 답변수정</a></li>
                    <?php } ?>
                    <?php if($answer_delete_href) { ?>
                    <li><a href="<?php echo $answer_delete_href; ?>" onclick="del(this.href); return false;" title="답변삭제"><i class='bx bx-trash'></i> 답변삭제</a></li>
                    <?php } ?>	
                </ul>
            </div>
            <?php } ?>
        </div>
    </div>
    
    <div class="answerContent">
        <div class="answerContentBox bg-wh round-m padding-m">
            <strong class="title"><?php echo get_text($answer['qa_subject']); ?></strong>
            
            <?php
            // 파일 출력
            if(isset($answer['img_count']) && $answer['img_count']) {
                echo "<div id=\"bo_v_img\">\n";

                for ($i=0; $i<$answer['img_count']; $i++) {
                    echo get_file_thumbnail($answer['img_file'][$i]);
                }

                echo "</div>\n";
            }
            ?>

            <div class="viewContText">
                <div class="content">
                    <?php echo get_view_thumbnail(conv_content($answer['qa_content'], $answer['qa_html'])); ?>
                </div>
            </div>

            <?php if(isset($answer['download_count']) && $answer['download_count']) { ?>
            <!-- 첨부파일 시작 { -->
            <div class="fileBox">
                <h2 class="sr-only">첨부파일</h2>
                <ul>
                <?php
                // 가변 파일
                for ($i=0; $i<$answer['download_count']; $i++) {
                 ?>
                    <li>
                        <i class='bx bx-file'></i>
                        <a href="<?php echo $answer['download_href'][$i];  ?>" class="view_file_download">
                            <strong><?php echo $answer['download_source'][$i] ?></strong>
                        </a>
                        <br>
                        <span class="dataInfo">DATE : <?php echo date("y-m-d H:i", strtotime($answer['qa_datetime'])) ?></span>
                    </li>
                <?php
                }
                 ?>
                </ul>
            </div>
            <!-- } 첨부파일 끝 -->
            <?php } ?>
        </div>
    </div>

    <div class="answerFooter">
        <a href="<?php echo $rewrite_href; ?>" class="button bg-pr color-wh round-m bxicon mBtn" title="추가질문">
            <i class='bx bx-plus'></i> 추가질문
        </a>  
    </div>
</div>

<script>
$(function() {
    // 답변 옵션 토글
    $(".btn_more_opt").on("click", function() {
        $(this).siblings(".more_opt_list").toggle();
    });
    
    // 다른 곳 클릭시 닫기
    $(document).on("click", function(e) {
        if (!$(e.target).closest(".answerActions").length) {
            $(".more_opt_list").hide();
        }
    });
});
</script>
