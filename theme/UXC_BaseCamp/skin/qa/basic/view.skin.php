<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

if (isset($config['cf_editor']) && $config['cf_editor'] === 'uxc_toastuieditor') {
    include_once(G5_PLUGIN_PATH.'/editor/uxc_toastuieditor/board_common.php');
}

if (!function_exists('uxc_qa_editor_content')) {
    function uxc_qa_editor_content($raw_content, $converted_content)
    {
        $markdown_marker = '<!--TOASTUI_EDITOR_MARKDOWN-->';
        $html_marker = '<!--TOASTUI_EDITOR_HTML-->';

        if (strpos($raw_content, $markdown_marker) === 0) {
            $markdown = ltrim(substr($raw_content, strlen($markdown_marker)), "\r\n");

            return '<div class="content qa-markdown-content" style="opacity:0">'
                . '<textarea class="qa-markdown-source" style="display:none">' . htmlspecialchars($markdown, ENT_QUOTES, 'UTF-8') . '</textarea>'
                . '<div class="qa-markdown-viewer toastui-editor-contents"></div>'
                . '</div>';
        }

        if (strpos($raw_content, $html_marker) === 0) {
            $html = ltrim(substr($raw_content, strlen($html_marker)), "\r\n");

            return '<div class="content">' . get_view_thumbnail(conv_content($html, 1)) . '</div>';
        }

        return '<div class="content">' . get_view_thumbnail($converted_content) . '</div>';
    }
}

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$qa_skin_url.'/style.css">', 0);
?>

<script src="<?php echo G5_JS_URL; ?>/viewimageresize.js"></script>

<!-- qaViewWrap -->
<div class="qaViewWrap boardViewWrap cardBox" data-board="qa_view">

    <div class="boardViewHeader">
        <div class="btnGroupWrap">
    
            <div class="btnGroup">
                <?php if ($update_href) { ?>
                <button type="button" onclick="location.href='<?php echo $update_href ?>'" title="수정" class="button shadowline-de round-s bxicon color-gray-800 sBtn">
                    <i class='bx bx-edit'></i>
                    <span class="text">수정</span>
                </button>
                <?php } ?>
                
                <?php if ($delete_href) { ?>
                <a href="<?php echo $delete_href ?>" onclick="del(this.href); return false;" title="삭제" alt="삭제" class="button shadowline-de round-s bxicon color-gray-800 sBtn">
                    <i class='bx bx-trash'></i>
                    <span class="text">삭제</span>
                </a>
                <?php } ?>
    
                <button type="button" onclick="location.href='<?php echo $list_href ?>'" title="목록" class="button shadowline-de round-s bxicon color-gray-800 sBtn">
                    <i class='bx bx-menu'></i>
                    <span class="text">목록</span>
                </button>

            </div>
    
            <div class="btnGroup">

                <?php if ($write_href) { ?>
                <button type="button" onclick="location.href='<?php echo $write_href ?>'" title="문의등록" class="button shadowline-de round-s bxicon color-gray-800 sBtn">
                    <i class='bx bx-pencil'></i>
                    <span class="text">문의등록</span>
                </button>
                <?php } ?>
    
            </div>
        </div>

    </div>
    
    <!-- boardViewBody -->
    <div class="boardViewBody">
        
        <!-- infoBox -->
        <div class="infoBox">
            <div class="info">
                <div class="pfImg"><?php echo get_member_profile_img($view['mb_id']) ?></div>
                <div class="pfData">
                    <div class="pfName">
                        <strong><?php echo $view['name'] ?></strong>
                        <div class="pdInfo">
                            <?php if($view['email']) { ?>
                                <span class="email button bg-wh shadowline-de round-s sBtn"><i class="bx bx-envelope"></i> <?php echo $view['email']; ?></span>
                            <?php } ?>
                            <?php if($view['hp']) { ?>
                                <span class="phone button bg-wh shadowline-de round-s sBtn"><i class="bx bx-phone"></i> <?php echo $view['hp']; ?></span>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="postData">
                        <span><?php echo date("y-m-d H:i", strtotime($view['datetime'])) ?></span>
                        <span class="status <?php echo ($view['qa_status'] ? 'complete' : 'waiting'); ?>">
                            <i class='bx <?php echo ($view['qa_status'] ? 'bx-check-circle' : 'bx-time-five'); ?>'></i>
                            <?php echo ($view['qa_status'] ? '답변완료' : '답변대기'); ?>
                        </span>
                    </div>
    
                </div>
    
            </div>
    
            <div class="catagory">
                <?php if ($view['category']) { ?>
                    <i class='bx bx-purchase-tag-alt'></i>
                    <?php echo $view['category']; ?>
                <?php } ?>
            </div>
        </div>

        <!-- viewContent -->
        <div class="viewContent">
        
            <div class="viewContentBox">
                <strong class="title"><?php echo cut_str(get_text($view['qa_subject']), 999); ?></strong>
                
                <?php
                // 파일 출력
                if($view['img_count']) {
                    echo "<div id=\"bo_v_img\">\n";
                    for ($i=0; $i<$view['img_count']; $i++) {
                        echo get_file_thumbnail($view['img_file'][$i]);
                    }
                    echo "</div>\n";
                }
                ?>

                <div class="viewContText">
                    <!-- content  -->
                    <?php echo uxc_qa_editor_content($view['qa_content'], $view['content']); ?>
                </div>
                
            </div>
        </div>
    
        <div class="viewTools">
            <?php if($view['download_count']) { ?>
            <!-- 첨부파일 시작 { -->
            <div class="fileBox">
                <h2 class="sr-only">첨부파일</h2>
                <ul>
                <?php
                // 가변 파일
                for ($i=0; $i<$view['download_count']; $i++) {
                    if ($view['download_source'][$i]) {
                ?>
                    <li>
                        <i class='bx bx-file'></i>
                        <a href="<?php echo $view['download_href'][$i];  ?>" class="view_file_download">
                            <strong><?php echo $view['download_source'][$i] ?></strong>
                        </a>
                        <br>
                        <span class="dataInfo">DATE : <?php echo date("y-m-d H:i", strtotime($view['datetime'])) ?></span>
                    </li>
                <?php }} ?>
                </ul>
            </div>
            <!-- } 첨부파일 끝 -->
            <?php } ?>
           
            <!-- 연관질문 카드 형식 -->
            <?php if($view['rel_count']) { ?>
            <div class="relatedCardBox">
                <h2><i class="bx bx-link"></i> 연관질문</h2>
                <div class="relatedCards">
                    <?php for($i=0; $i<$view['rel_count']; $i++) { ?>
                    <a href="<?php echo $rel_list[$i]['view_href']; ?>" class="relatedCard bg-wh round-m padding-m">
                        <div class="cardHeader">
                            <span class="categoryTag"><?php echo get_text($rel_list[$i]['category']); ?></span>
                            <span class="status <?php echo ($rel_list[$i]['qa_status'] ? 'answered' : 'waiting'); ?>">
                                <?php echo ($rel_list[$i]['qa_status'] ? '<i class="bx bx-check-circle"></i> 답변완료' : '<i class="bx bx-time-five"></i> 답변대기'); ?>
                            </span>
                        </div>
                        <div class="cardBody">
                            <h3 class="title"><?php echo $rel_list[$i]['subject']; ?></h3>
                            <div class="date">
                                <i class='bx bx-time'></i> <?php echo $rel_list[$i]['date']; ?>
                            </div>
                        </div>
                    </a>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>

            <!-- 추가질문 버튼 -->
            <?php if($view['qa_type']) { ?>
            <div class="addQuestionBox">
                <a href="<?php echo $rewrite_href; ?>" class="button bg-pr color-wh round-m bxicon mBtn">
                    <i class='bx bx-plus'></i> 추가질문
                </a>
            </div>
            <?php } ?>

        </div>
        
        <!-- 답변 섹션 -->
        <?php
        if(!$view['qa_type']) {
            if($view['qa_status'] && $answer['qa_id'])
                include_once($qa_skin_path.'/view.answer.skin.php');
            else
                include_once($qa_skin_path.'/view.answerform.skin.php');
        }
        ?>

    </div>
    

</div>

<?php if (isset($config['cf_editor']) && $config['cf_editor'] === 'uxc_toastuieditor') { ?>
<script>
(function() {
    function renderQaMarkdown(attempt) {
        if (!window.toastui || !window.toastui.Editor) {
            if (attempt < 100) {
                window.setTimeout(function() { renderQaMarkdown(attempt + 1); }, 50);
            }
            return;
        }

        document.querySelectorAll('.qa-markdown-content').forEach(function(container) {
            if (container.dataset.rendered === 'true') return;

            var source = container.querySelector('.qa-markdown-source');
            var viewer = container.querySelector('.qa-markdown-viewer');
            if (!source || !viewer) return;

            new toastui.Editor({
                el: viewer,
                viewer: true,
                initialValue: source.value,
                usageStatistics: false
            });

            container.dataset.rendered = 'true';
            container.style.opacity = '1';
            if (document.documentElement.classList.contains('darkMode')) {
                container.classList.add('toastui-editor-dark');
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        renderQaMarkdown(0);
    });
})();
</script>
<?php } ?>

<script>
$(function() {
    $("a.view_image").click(function() {
        window.open(this.href, "large_image", "location=yes,links=no,toolbar=no,top=10,left=10,width=10,height=10,resizable=yes,scrollbars=no,status=no");
        return false;
    });

    // 이미지 리사이즈
    $("#bo_v_atc").viewimageresize();
    
    // 첨부파일
    $("a.view_file_download").click(function() {
        var href = $(this).attr("href")+"&js=on";
        $(this).attr("href", href);
        
        return true;
    });
});
</script>
