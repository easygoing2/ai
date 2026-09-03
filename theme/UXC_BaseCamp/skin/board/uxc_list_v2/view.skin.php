<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
// add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/css/style.css">', 0);

// GLightbox 라이트박스 CSS
add_stylesheet('<link rel="stylesheet" href="'.G5_THEME_URL.'/js/plugin/glightbox/glightbox.min.css">', 0);

// 광고 플러그인 라이브러리 파일 추가
if(file_exists(G5_THEME_PATH.'/ui_theme_setting/theme_adinsert/ad_insert.lib.php')) {
    include_once(G5_THEME_PATH.'/ui_theme_setting/theme_adinsert/ad_insert.lib.php');
    add_stylesheet('<link rel="stylesheet" href="'.G5_THEME_URL.'/ui_theme_setting/theme_adinsert/ad_style.css">', 0);
}

// Toast UI Editor 사용 시 에디터 리소스 로드
if (isset($config['cf_editor']) && $config['cf_editor'] === 'uxc_toastuieditor') {
    include_once(G5_PLUGIN_PATH.'/editor/uxc_toastuieditor/board_common.php');
}

// 기본값 설정
// 현재글의 wr_id
$current_id = (int)$view['wr_id'];

// 비밀글 접근 권한 체크를 위한 추가 조건
$secret_sql = "";
if (!$is_admin) {
    if ($is_member) {
        // 회원인 경우: 비밀글이 아니거나 본인 글만
        $secret_sql = " AND (wr_option NOT LIKE '%secret%' OR mb_id = '".sql_real_escape_string($member['mb_id'])."')";
    } else {
        // 비회원인 경우: 비밀글 제외
        $secret_sql = " AND wr_option NOT LIKE '%secret%'";
    }
}

// 이전글
$prev = sql_fetch("SELECT wr_id, wr_subject, wr_option, mb_id FROM {$write_table}
                   WHERE wr_id > '{$current_id}' AND wr_is_comment = 0 {$secret_sql}
                   ORDER BY wr_id ASC LIMIT 1");
$prev_href = ($prev && isset($prev['wr_id'])) ? get_pretty_url($bo_table, $prev['wr_id'], $qstr) : '';

// 다음글
$next = sql_fetch("SELECT wr_id, wr_subject, wr_option, mb_id FROM {$write_table}
                   WHERE wr_id < '{$current_id}' AND wr_is_comment = 0 {$secret_sql}
                   ORDER BY wr_id DESC LIMIT 1");
$next_href = ($next && isset($next['wr_id'])) ? get_pretty_url($bo_table, $next['wr_id'], $qstr) : '';

?>

<script src="<?php echo G5_JS_URL; ?>/viewimageresize.js"></script>

<!-- boardViewWrap -->
<div class="boardViewWrap cardBox" data-board="uxc_view">

    <div class="boardViewHeader">
        <div class="btnGroupWrap">
    
            <div class="btnGroup">

                <?php if ($prev_href) { ?>
                <a href="<?php echo $prev_href ?>" class="button bg-wh shadowline-de round-s bxicon color-gray-800 sBtn">
                    <i class='bx bxs-chevron-left'></i> 이전글<span class="sound_only"><?php echo get_text(cut_str($prev['wr_subject'], 40)); ?></span>
                </a>
                <?php } ?>

                <?php if ($next_href) { ?>
                <a href="<?php echo $next_href ?>" class="button bg-wh shadowline-de round-s bxicon color-gray-800 sBtn">
                    다음글 <i class='bx bxs-chevron-right'></i><span class="sound_only"><?php echo get_text(cut_str($next['wr_subject'], 40)); ?></span>
                </a>
                <?php } ?>

                <?php if ($scrap_href) { ?>
                <a href="<?php echo $scrap_href;  ?>" target="_blank" class="button bg-wh shadowline-de round-s bxicon color-gray-800 sBtn" onclick="win_scrap(this.href); return false;" title="스크랩">
                    <i class='bx bx-copy-alt'></i>
                    <span class="text">스크랩</span>
                </a>
                <?php } ?>
            </div>
    
            <div class="btnGroup">
                <?php if ($copy_href) { ?>
                <a href="<?php echo $copy_href ?>" onclick="board_move(this.href); return false;" title="복사" aria-label="복사" class="button bg-wh shadowline-de round-s bxicon color-gray-800 sBtn">
                    <i class='bx bx-copy'></i>
                    <span class="text">복사</span>
                </a>
                <?php } ?>
                
                <?php if ($move_href) { ?>
                <a href="<?php echo $move_href ?>" onclick="board_move(this.href); return false;" title="이동" aria-label="이동" class="button bg-wh shadowline-de round-s bxicon color-gray-800 sBtn">
                    <i class='bx bx-transfer'></i>
                    <span class="text">이동</span>
                </a>
                <?php } ?>

                <?php if ($delete_href) { ?>
                <a href="<?php echo $delete_href ?>" onclick="del(this.href); return false;" title="삭제" aria-label="삭제" class="button bg-wh shadowline-de round-s bxicon color-gray-800 sBtn">
                    <i class='bx bx-trash'></i>
                    <span class="text">삭제</span>
                </a>
                <?php } ?>

                <button type="button" onclick="location.href='<?php echo $list_href ?>';" title="목록" class="button bg-wh shadowline-de round-s bxicon color-gray-800 sBtn">
                    <i class='bx bx-menu'></i>
                    <span class="text">목록</span>
                </button>

                <?php if ($update_href) { ?>
                <button type="button" onclick="location.href='<?php echo $update_href ?>';" title="수정" class="button bg-wh shadowline-de round-s bxicon color-gray-800 sBtn">
                    <i class='bx bx-edit'></i>
                    <span class="text">수정</span>
                </button>
                <?php } ?>


                <?php if ($write_href) { ?>
                <button type="button" onclick="location.href='<?php echo $write_href ?>';" title="등록" class="button bg-wh shadowline-de round-s bxicon color-gray-800 sBtn">
                    <i class='bx bx-pencil'></i>
                    <span class="text">등록</span>
                </button>
                <?php } ?>
            </div>
        </div>

    </div>
    
    <!-- listboardView -->
    <div class="boardViewBody">
        
        <!-- infoBox -->
        <div class="infoBox">
            <div class="info">
                <div class="pfImg"><?php echo get_member_profile_img($view['mb_id']) ?></div>
                <div class="pfData">
                    <div class="pfName">
                        <strong><?php echo $view['name'] ?></strong>
                        <div class="pdInfo">
                            <?php if ($is_admin == 'super') {  ?>
                                <?php if ($is_ip_view) { ?>
                                    <span class="ip button bg-wh color-bl shadowline-de round-s sBtn"><?php echo htmlspecialchars($ip, ENT_QUOTES, 'UTF-8') ?></span>
                                <?php } ?>
                            <?php } ?>
                            <?php if ($is_member && $view['mb_id']) { ?>
                                <a href="<?php echo G5_BBS_URL; ?>/memo_form.php?me_recv_mb_id=<?php echo $view['mb_id']; ?>" class="button bg-wh color-bl shadowline-de round-s sBtn bxicon win_memo">
                                    <i class='bx bx-message-rounded'></i>
                                    <span>메시지 보내기</span>
                                </a>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="postData">
                        <span><?php echo date("y-m-d H:i", strtotime($view['wr_datetime'])) ?></span>
                        <span><i class='bx bx-message-square-dots'></i> <?php echo number_format($view['wr_comment']) ?>개</span>
                        <span><i class='bx bx-show'></i> <?php echo number_format($view['wr_hit']) ?>회</span>
                    </div>
    
                </div>
    
            </div>
    
            <div class="catagory">
                <?php if (isset($category_name) && $category_name) { ?>
                    <i class='bx bx-purchase-tag-alt'></i>
                    <?php echo htmlspecialchars($view['ca_name'], ENT_QUOTES, 'UTF-8'); ?>
                <?php } ?>
            </div>
        </div>

        <!-- viewContent -->
        <div class="viewContent">
        
            <div class="viewContentBox">
                <strong class="title"><?php echo cut_str(get_text($view['wr_subject']), 999); ?></strong>
                
                <?php
                // 파일 출력
                $v_img_count = count($view['file']);
                if($v_img_count) {
                    echo "<div id=\"bo_v_img\">\n";

                    foreach($view['file'] as $view_file) {
                        echo get_file_thumbnail($view_file);
                    }

                    echo "</div>\n";
                }
                ?>

                <div class="viewContText">
                    <?php if ($view['wr_10']) { ?>
                    <?php
                        if (!function_exists('extractYouTubeID')) {
                            function extractYouTubeID($url) {
                                // 정규식을 사용해서 유튜브 영상 ID를 추출
                                preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([^\&\?\/]+)/', $url, $matches);
                                return $matches[1] ?? '';
                            }
                        }
                        $youtube_id = extractYouTubeID($view['wr_10'] ?? '');
                    ?>

                    <?php if ($youtube_id) { ?>
                    <!-- youtube -->
                    <div class="video-container">
                        <iframe width="100%" src="https://www.youtube.com/embed/<?php echo htmlspecialchars($youtube_id, ENT_QUOTES, 'UTF-8'); ?>" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    </div>
                    <?php } ?>


                    <?php } ?>
                    <!-- content  -->
                    <?php
                    // Toast UI Editor로 작성된 콘텐츠인지 확인
                    $is_markdown = false;
                    $raw_content = $view['wr_content'];

                    if (strpos($raw_content, '<!--TOASTUI_EDITOR_MARKDOWN-->') === 0) {
                        $is_markdown = true;
                        // 마크다운 구분자 제거
                        $markdown_content = str_replace('<!--TOASTUI_EDITOR_MARKDOWN-->', '', $raw_content);
                        $markdown_content = ltrim($markdown_content, "\n");
                    } else if (strpos($raw_content, '<!--TOASTUI_EDITOR_HTML-->') === 0) {
                        // HTML 모드로 작성된 경우
                        $html_content = str_replace('<!--TOASTUI_EDITOR_HTML-->', '', $raw_content);
                        $html_content = ltrim($html_content, "\n");
                    }
                    ?>

                    <?php
                    // 본문 시작 전 광고
                    if(function_exists('uxc_get_ad')) {
                        echo uxc_get_ad('before_content', $board['bo_table']);
                    }
                    ?>

                    <div id="bo_v_con" class="content" <?php echo $is_markdown ? 'data-markdown="true" style="opacity:0; transition:opacity 0.1s ease;"' : '' ?>>
                        <?php if($is_markdown) { ?>
                            <div id="markdown-source" style="display:none;"><?php echo htmlspecialchars($markdown_content) ?></div>
                            <div id="markdown-viewer" class="toastui-editor-contents">
                                <!-- 마크다운 렌더링 영역 -->
                                <div style="padding:20px;text-align:center;color:#999;">콘텐츠를 불러오는 중..</div>
                            </div>
                        <?php } else if (isset($html_content) && $html_content) { ?>
                            <?php echo get_view_thumbnail($html_content); ?>
                        <?php } else { ?>
                            <?php echo get_view_thumbnail($view['content']); ?>
                        <?php } ?>
                    </div>

                    <?php
                    // 본문 끝 후 광고
                    if(function_exists('uxc_get_ad')) {
                        echo uxc_get_ad('after_content', $board['bo_table']);
                    }
                    ?>
                </div>
                
            </div>
        </div>
    
        <div class="viewTools">
            <?php
            $cnt = 0;
            if ($view['file']['count']) {
                for ($i=0; $i<count($view['file']); $i++) {
                    if (isset($view['file'][$i]['source']) && $view['file'][$i]['source'] && !$view['file'][$i]['view'])
                        $cnt++;
                }
            }
            ?>
        
            <?php if($cnt) { ?>
            <!-- 첨부파일 시작 { -->
            <div class="fileBox">
                <h2 class="sr-only">첨부파일</h2>
                <ul>
                <?php
                // 가변 파일
                for ($i=0; $i<count($view['file']); $i++) {
                    if (isset($view['file'][$i]['source']) && $view['file'][$i]['source'] && !$view['file'][$i]['view']) {
                ?>
                    <li>
                        <i class='bx bx-file'></i>
                        <a href="<?php echo $view['file'][$i]['href'];  ?>" class="view_file_download">
                            <strong><?php echo $view['file'][$i]['source'] ?></strong> <?php echo $view['file'][$i]['content'] ?> (<?php echo $view['file'][$i]['size'] ?>)
                        </a>
                        <br>
                        <span class="dataInfo"><?php echo $view['file'][$i]['download'] ?>회 다운로드 | DATE : <?php echo $view['file'][$i]['datetime'] ?></span>
                    </li>
                <?php }} ?>
                </ul>
            </div>
            <!-- } 첨부파일 끝 -->
            <?php } ?>
            
            <?php if(isset($view['link']) && array_filter($view['link'])) { ?>
            <div class="linkBox">
                <h2 class="sr-only">링크</h2>
                <ul>
                    <?php
                    // 링크
                    $cnt = 0;
                    for ($i=1; $i<=count($view['link']); $i++) {
                        if ($view['link'][$i]) {
                            $cnt++;
                            $link = cut_str($view['link'][$i], 70);
                    ?>
                    <li>
                        <i class="bx bx-link-alt" aria-hidden="true"></i>
                        <a href="<?php echo $view['link_href'][$i] ?>" class="view_file_download" target="_blank" style="font-size:12px;">
                            <b><?php echo $link ?></b> (<?php echo $view['link_hit'][$i] ?>회 연결)
                        </a>
                    </li>
                    <?php }} ?>
                </ul>
            </div>
            <?php } ?>

            <?php if ($is_signature) { ?>
                <!-- 서명 -->
                <p><?php echo $signature ?></p>
            <?php } ?>
        
            <!--  추천 비추천 시작 { -->
            <?php if ( $good_href || $nogood_href) { ?>
            <div class="recommendBox">
                <?php if ($good_href) { ?>
                <span class="bo_v_act_gng">
                    <a href="<?php echo $good_href.'&amp;'.$qstr ?>" id="good_button" class="bo_v_good">
                        <i class='bx bx-like' aria-hidden="true"></i>
                        <span class="sound_only">추천</span>
                        <strong><?php echo number_format($view['wr_good']) ?></strong>
                    </a>
                    <b id="bo_v_act_good"></b>
                </span>
                <?php } ?>
                <?php if ($nogood_href) { ?>
                <span class="bo_v_act_gng">
                    <a href="<?php echo $nogood_href.'&amp;'.$qstr ?>" id="nogood_button" class="bo_v_nogood">
                        <i class='bx bx-dislike' aria-hidden="true"></i>
                        <span class="sound_only">비추천</span>
                        <strong><?php echo number_format($view['wr_nogood']) ?></strong>
                    </a>
                    <b id="bo_v_act_nogood"></b>
                </span>
                <?php } ?>
            </div>
            <?php } else {
                if($board['bo_use_good'] || $board['bo_use_nogood']) {
            ?>
            <div class="recommendBox">
                <?php if($board['bo_use_good']) { ?>
                    <span class="bo_v_good">
                        <i class='bx bx-like' aria-hidden="true"></i>
                        <span class="sound_only">추천</span>
                        <strong><?php echo number_format($view['wr_good']) ?></strong>
                    </span>
                <?php } ?>
                <?php if($board['bo_use_nogood']) { ?>
                    <span class="bo_v_nogood">
                        <i class='bx bx-dislike' aria-hidden="true"></i>
                        <span class="sound_only">비추천</span>
                        <strong><?php echo number_format($view['wr_nogood']) ?></strong>
                    </span>
                <?php } ?>
            </div>
            <?php }} ?>
            <!-- }  추천 비추천 끝 -->
            <!-- snsShare -->
            <div class="snsShare">
                <?php include_once(G5_SNS_PATH."/view.sns.skin.php"); ?>
            </div>
        </div>
        

        <!-- commentWrap -->
        <?php
        // 댓글 영역 전 광고
        if(function_exists('uxc_get_ad')) {
            echo uxc_get_ad('before_comment', $board['bo_table']);
        }
        ?>

        <div class="commentWrap">
            <?php
                include_once(G5_BBS_PATH.'/view_comment.php');
            ?>
        </div>

        <?php
        // 댓글 영역 후 광고
        if(function_exists('uxc_get_ad')) {
            echo uxc_get_ad('after_comment', $board['bo_table']);
        }
        ?>
    
    </div>
    

</div>

<script>
    <?php if ($board['bo_download_point'] < 0) { ?>
    $(function() {
        $("a.view_file_download").click(function() {
            if (!g5_is_member) {
                alert("다운로드 권한이 없습니다.\n회원이시라면 로그인 후 이용해 보십시오.");
                return false;
            }

            var msg = "파일을 다운로드 하시면 포인트가 차감(<?php echo number_format($board['bo_download_point']) ?>점)됩니까?\n\n포인트는 게시물당 한번만 차감되며 다음에 다시 다운로드 하셔도 중복하여 차감하지 않습니다.\n\n그래도 다운로드 하시겠습니까?";

            if (confirm(msg)) {
                var href = $(this).attr("href") + "&js=on";
                $(this).attr("href", href);

                return true;
            } else {
                return false;
            }
        });
    });
    <?php } ?>

    function board_move(href) {
        window.open(href, "boardmove", "left=50, top=50, width=500, height=550, scrollbars=1");
    }
</script>

<script>
    $(function() {
        // 추천, 비추천
        $("#good_button, #nogood_button").click(function() {
            var $tx;
            if (this.id == "good_button")
                $tx = $("#bo_v_act_good");
            else
                $tx = $("#bo_v_act_nogood");

            excute_good(this.href, $(this), $tx);
            return false;
        });

        // 이미지 리사이즈
        $("#bo_v_atc").viewimageresize();
    });

    function excute_good(href, $el, $tx) {
        $.post(
            href, {
                js: "on"
            },
            function(data) {
                if (data.error) {
                    alert(data.error);
                    return false;
                }

                if (data.count) {
                    $el.find("strong").text(number_format(String(data.count)));
                    if ($tx.attr("id").search("nogood") > -1) {
                        $tx.text("이 글을 비추천하였습니다.");
                        $tx.fadeIn(200).delay(2500).fadeOut(200);
                    } else {
                        $tx.text("이 글을 추천하셨습니다.");
                        $tx.fadeIn(200).delay(2500).fadeOut(200);
                    }
                }
            }, "json"
        );
    }
</script>

<?php if (isset($config['cf_editor']) && $config['cf_editor'] === 'uxc_toastuieditor') { ?>
<!-- 마크다운 렌더링 스크립트 -->
<script src="<?php echo G5_PLUGIN_URL ?>/editor/uxc_toastuieditor/js/board_markdown.js"></script>
<?php } ?>

<!-- GLightbox 라이트박스 -->
<script src="<?php echo G5_THEME_URL ?>/js/plugin/glightbox/glightbox.min.js"></script>
<script>
var uxcLightbox = {
    board: null,
    comment: null,

    init: function() {
        this.initBoard();
        this.initComment();
    },

    // 게시판 본문 이미지 라이트박스
    initBoard: function() {
        var _dataUrl = '<?php echo G5_DATA_URL; ?>';
        var _boTable = '<?php echo $bo_table; ?>';
        var imgs = document.querySelectorAll('#bo_v_con a.view_image, #bo_v_img a.view_image');
        for (var i = 0; i < imgs.length; i++) {
            var el = imgs[i];
            var href = el.getAttribute('href') || '';
            if (href.indexOf('view_image.php') !== -1) {
                var match = href.match(/fn=([^&]+)/);
                if (match) {
                    var fn = decodeURIComponent(match[1]);
                    // view_image.php 로직과 동일하게 실제 이미지 URL 구성
                    if (fn.indexOf('data/editor') !== -1) {
                        el.setAttribute('href', _dataUrl + '/' + fn.substring(fn.indexOf('editor')));
                    } else if (fn.indexOf('data/qa') !== -1) {
                        el.setAttribute('href', _dataUrl + '/' + fn.substring(fn.indexOf('qa')));
                    } else {
                        el.setAttribute('href', _dataUrl + '/file/' + _boTable + '/' + fn);
                    }
                }
            }
            el.removeAttribute('target');
            el.classList.add('glightbox-board');
        }

        if (imgs.length > 0) {
            if (this.board) this.board.destroy();
            this.board = GLightbox({
                selector: '.glightbox-board',
                touchNavigation: true,
                loop: false,
                closeOnOutsideClick: true,
                openEffect: 'zoom',
                closeEffect: 'fade'
            });
        }
    },

    // 댓글 이미지 라이트박스
    initComment: function() {
        if (this.comment) this.comment.destroy();
        var cmtImgs = document.querySelectorAll('.uxc-cmt-img-link');
        if (cmtImgs.length > 0) {
            this.comment = GLightbox({
                selector: '.uxc-cmt-img-link',
                touchNavigation: true,
                loop: false,
                closeOnOutsideClick: true,
                openEffect: 'zoom',
                closeEffect: 'fade'
            });
        }
    }
};

$(function() { uxcLightbox.init(); });
</script>
<!-- } 게시글 읽기 끝 -->