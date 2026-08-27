<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php');
// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/css/style.css">', 0);


?>
<!--listWrap  -->
<div class="boardListWrap">

    <?php if ($is_category) { ?>
    <!-- catagory -->
    <div class="catagory">
        <ul>
            <?php echo $category_option ?>
        </ul>
    </div>
    <?php } ?>

    

    <div class="toolBox">
        <div class="tools">
            <?php if ($is_checkbox) { ?>
            <div class="opt">
                <input type="checkbox" id="chkall" onclick="if (this.checked) all_checked(true); else all_checked(false);">
                <label for="chkall"></label>
            </div>
            <?php } ?>
            <!-- total -->
            <div class="total">
                <span>Total <strong><?php echo number_format($total_count) ?></strong> 건</span>
                <span><strong><?php echo $page ?></strong> 페이지</span>
            </div>
        </div>
        <!-- tools -->
        <div class="tools">
            
            <?php if ($write_href) { ?>
                <button type="button" onclick="location.href='<?php echo $write_href ?>';" title="등록">
                    <i class='bx bx-pencil'></i>
                    <span class="sound_only">등록</span>
                </button>
            <?php } ?>   
            <button type="button" id="searchPopOn" title="검색">
                <i class='bx bx-search-alt-2'></i>
                <span class="sound_only">검색</span>
            </button>
            <div id="btnSort" class="buttonGroup">
                <?php echo subject_sort_link('wr_hit', $qstr2, 1) ?><i class="bx bx-show"></i> 조회순</a>
                <?php echo subject_sort_link('wr_datetime', $qstr2, 1) ?><i class='bx bx-calendar'></i> 날짜순</a>
            </div>            <?php if ($rss_href) { ?>
                <a href="<?php echo $rss_href ?>" title="RSS">
                    <i class='bx bx-rss'></i>
                    <span class="sound_only">RSS</span>
                </a>
            <?php } ?>
        </div>

    </div>
    <!--  -->

    <div class="listWrap">
        <form name="fboardlist" id="fboardlist" action="<?php echo G5_BBS_URL; ?>/board_list_update.php" onsubmit="return fboardlist_submit(this);" method="post">
        <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
        <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
        <input type="hidden" name="stx" value="<?php echo $stx ?>">
        <input type="hidden" name="spt" value="<?php echo $spt ?>">
        <input type="hidden" name="sst" value="<?php echo $sst ?>">
        <input type="hidden" name="sod" value="<?php echo $sod ?>">
        <input type="hidden" name="page" value="<?php echo (int)$page ?>">
        <input type="hidden" name="sw" value="">

        <div class="listBox" data-board="gallery_default">
            <?php
            if (!function_exists('extractYouTubeID')) {
                function extractYouTubeID($url) {
                    preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([^\&\?\/]+)/', $url, $matches);
                    return $matches[1] ?? '';
                }
            }
            ?>
            <?php
                for ($i=0; $i<count($list); $i++) {
                    $thumb = get_list_thumbnail($board['bo_table'], $list[$i]['wr_id'], 640, 360, false, true);
                    if($thumb['src']) {
                        // 1순위: 첨부파일 썸네일
                        $img_content = $thumb['src'];
                    } else if ($list[$i]['wr_10']){
                        // 2순위: YouTube 썸네일
                        $youtube_id = extractYouTubeID($list[$i]['wr_10']);
                        $img_content = $youtube_id ? 'https://img.youtube.com/vi/'.$youtube_id.'/mqdefault.jpg' : '';
                    } else {
                        // 3순위: 에디터 내 첫번째 이미지
                        $img_content = '';
                        $editor_img_src = '';
                        
                        // toastuieditor 함수로 첫번째 이미지 URL 추출
                        if (function_exists('toastuieditor_get_first_image')) {
                            $editor_img_src = toastuieditor_get_first_image($list[$i]['wr_content']);
                        }
                        
                        // toastuieditor 함수가 없거나 실패한 경우 get_editor_image 사용
                        if (!$editor_img_src && function_exists('get_editor_image')) {
                            $editor_img = get_editor_image($list[$i]['wr_content'], 0);
                            if ($editor_img && isset($editor_img[1][0])) {
                                preg_match("/src=[\'\"]?([^>\'\"]+[^>\'\"]+)/i", $editor_img[1][0], $m);
                                if (isset($m[1])) {
                                    $editor_img_src = $m[1];
                                }
                            }
                        }
                        
                        // 이미지가 있으면 썸네일 생성
                        if ($editor_img_src) {
                            // 이미지 파일명을 해시로 생성
                            $filename = 'editor_thumb_'.md5($editor_img_src).'.jpg';
                            $thumb_path = G5_DATA_PATH.'/file/'.$board['bo_table'];
                            $thumb_url = G5_DATA_URL.'/file/'.$board['bo_table'];
                            
                            // 썸네일이 이미 존재하는지 확인
                            if (file_exists($thumb_path.'/'.$filename)) {
                                $img_content = $thumb_url.'/'.$filename;
                            } else {
                                // 원본 이미지가 그누보드 내부 이미지인 경우
                                if (strpos($editor_img_src, G5_URL) !== false) {
                                    $local_path = str_replace(G5_URL, G5_PATH, $editor_img_src);
                                    if (file_exists($local_path)) {
                                        $info = pathinfo($local_path);
                                        $thumb_file = thumbnail($info['basename'], $info['dirname'], $thumb_path, 640, 360, false, true, 'center');
                                        if ($thumb_file) {
                                            $img_content = $thumb_url.'/'.$thumb_file;
                                        }
                                    }
                                }
                                
                                // 썸네일 생성 실패시 원본 사용
                                if (!$img_content) {
                                    $img_content = $editor_img_src;
                                }
                            }
                        }
                    }
                    
                    // 이미지가 없으면 빈 문자열
                    if (!$img_content) {
                        $img_content = '';
                    }

            ?>

            <div class="cardBox bg-wh padding-s round-m <?php if ($list[$i]['is_notice']) echo "bo_notice"; ?> <?php if ($wr_id == $list[$i]['wr_id']) echo "active"; ?>">
                <!-- checkBox -->
                <?php if ($is_checkbox) { ?>
                <div class="checkBox">
                    <div class="opt">
                        <input type="checkbox" name="chk_wr_id[]" value="<?php echo $list[$i]['wr_id'] ?>" id="chk_wr_id_<?php echo $i ?>" class="selec_chk">
                        <label for="chk_wr_id_<?php echo $i ?>">
                            <span class="sr-only"><?php echo $list[$i]['subject'] ?></span>
                        </label>
                    </div>
                </div>
                <?php } ?>
                <!-- thumb -->
                <a href="<?php echo $list[$i]['href'] ?>" class="thumb">
                    <!-- ca_name -->
                    <?php if ($is_category && !empty($list[$i]['ca_name'])) { ?>
                    <span class="labelBox bg-wh color-bl round-s">
                    <?php echo $list[$i]['ca_name'] ?>
                    </span>
                    <?php } ?>
                    <img src="<?php echo htmlspecialchars($img_content, ENT_QUOTES, 'UTF-8') ?>" title="thumb" class="round-m" loading="lazy" onerror="this.onerror=null;this.src='<?php echo $board_skin_url ?>/img/no_img.png'">
                    <?php if ($list[$i]['wr_10']) { ?>
                    <div class="youtube_ico">
                        <img src="<?php echo $board_skin_url ?>/img/icon_youtube.png">
                    </div>
                    <?php } ?>
                </a>
                <div class="cardBoxContent">
                    <div class="titleBox">
                        <?php if ($list[$i]['wr_id']) { ?>    
                        <div class="title">
                            <a href="<?php echo $list[$i]['href'] ?>">
                                <?php if(!empty($list[$i]['icon_secret'])) { ?>
                                    비밀글로 보호된 글입니다.
                                <?php } else { ?>
                                    <strong>
                                        <?php
                                        if (!empty($list[$i]['is_notice'])) // 공지사항
                                            echo '<span class="bo_current">공지</span>';
                                            else if ($wr_id == $list[$i]['wr_id'])
                                                echo "<span class=\"bo_current\">열람</span>";
                                            else if (!empty($list[$i]['icon_secret'])) 
                                                echo '<i class="bx bx-lock-alt bo_current" aria-hidden="true"></i>';
                                        else
                                            // echo $list[$i]['num'];
                                        ?>
                                        <?php echo $list[$i]['subject'] ?>
                                        <?php 
                                        if (!empty($list[$i]['icon_new'])) echo '<span class="iconBox bg-pr color-wh round-s">N</span>';
                                        ?>
                                    </strong>
                                <?php } ?>
                                <p class="desc"><?php 
                                    // Toast UI Editor 함수가 있으면 사용, 없으면 기본 처리
                                    if (function_exists('toastuieditor_clean_list_content')) {
                                        $clean_content = toastuieditor_clean_list_content($list[$i]['wr_content']);
                                    } else {
                                        $clean_content = $list[$i]['wr_content'];
                                    }
                                    
                                    // 이스케이프된 대괄호를 원래대로 복원
                                    $clean_content = str_replace(['\\[', '\\]'], ['[', ']'], $clean_content);
                                    
                                    echo cut_str(strip_tags($clean_content), 80, " . . . ");
                                ?></p>
                            </a>
                        </div>
                        <?php } ?>
                        <!--                         <span class="add">
                            <?php
                            if (!empty($list[$i]['icon_file'])) echo '<i class="bx bx-file" aria-hidden="true"></i>';
                            if (!empty($list[$i]['icon_link'])) echo '<i class="bx bx-link-alt" aria-hidden="true"></i>';
                            if (!empty($thumb['src'])) echo '<i class="bx bx-image-alt" aria-hidden="true"></i>';
                            if (!empty($list[$i]['icon_hot'])) echo '<i class="fa fa-heart-o" aria-hidden="true"></i>';
                            ?>
                            <?php if (!empty($list[$i]['wr_10'])) { ?>
                                <i class='bx bxl-youtube'></i>
                            <?php } ?>
                        </span> -->
                    </div>

                    <div class="infoBox inlineType">
                        <span class="user round-circle"><?php echo get_member_profile_img($list[$i]['mb_id'] ?? '') ?></span>
                        <div class="info">
                            <dl>
                                <dt><i class="bx bx-user"></i></dt>
                                <dd class="sv_use"><?php echo $list[$i]['name'] ?></dd>
                            </dl>
                            <dl>
                                <dt><i class="bx bx-message-square-dots"></i></dt>
                                <dd class="color-pr"><?php echo $list[$i]['wr_comment']; ?></dd>
                            </dl>
                            <dl>
                                <dt><i class="bx bx-time"></i></dt>
                                <dd><?php echo $list[$i]['datetime2'] ?></dd>
                            </dl>
                            <dl>
                                <dt><i class="bx bx-show"></i></dt>
                                <dd><?php echo $list[$i]['wr_hit'] ?></dd>
                            </dl>
                        </div>
                    </div>

                </div>

            </div>

            <?php } ?>
            
            <!-- empty -->
            <?php if (count($list) == 0) { echo '<div class="cardBox bg-wh line-de padding-l round-m">게시물이 없습니다.</div>'; } ?>

        </div>

        <!-- 페이지 -->
        <div class="listFooter">
            <div class="pagination">
                <?php echo $write_pages;  ?>
            </div>
        </div>
        
        <?php if ($is_admin) {  ?>
        <!-- admBtn -->
        <div class="admBtn">
            <div class="levelWrap">
                <dl>
                    <dt>목록보기</dt>
                    <dd><?php echo $board['bo_list_level'] ?></dd>
                </dl>
                <dl>
                    <dt>글읽기</dt>
                    <dd><?php echo $board['bo_read_level'] ?></dd>
                </dl>
                <dl>
                    <dt>글쓰기</dt>
                    <dd><?php echo $board['bo_write_level'] ?></dd>
                </dl>
                <dl>
                    <dt>리플</dt>
                    <dd><?php echo $board['bo_reply_level'] ?></dd>
                </dl>
                <dl>
                    <dt>댓글쓰기</dt>
                    <dd><?php echo $board['bo_comment_level'] ?></dd>
                </dl>
                <dl>
                    <dt>링크</dt>
                    <dd><?php echo $board['bo_link_level'] ?></dd>
                </dl>
                <dl>
                    <dt>업로드</dt>
                    <dd><?php echo $board['bo_upload_level'] ?></dd>
                </dl>
                <dl>
                    <dt>다운로드</dt>
                    <dd><?php echo $board['bo_download_level'] ?></dd>
                </dl>
                <dl>
                    <dt>html</dt>
                    <dd><?php echo $board['bo_html_level'] ?></dd>
                </dl>
            </div>
            <div class="buttonWrap">
                <?php if ($write_href) { ?>
                    <button type="button" onclick="location.href='<?php echo $write_href ?>';" class="color-pr" title="등록">
                        <i class='bx bx-pencil'></i>
                    </button>
                <?php } ?>
                <?php if ($is_checkbox) { ?>
                    <button type="submit" name="btn_submit" value="선택복사" onclick="document.pressed=this.value" title="선택복사">
                        <i class='bx bx-copy'></i>
                    </button>
                    <button type="submit" name="btn_submit" value="선택이동" onclick="document.pressed=this.value" title="선택이동">
                        <i class='bx bx-transfer'></i>
                    </button>
                    <button type="submit" name="btn_submit" value="선택삭제" onclick="document.pressed=this.value" title="선택삭제" class="warning">
                        <i class='bx bx-trash'></i>
                    </button>
                <?php } ?>
        
                <?php if ($admin_href) { ?>
                    <button type="button" onclick="location.href='<?php echo $admin_href ?>';" title="관리자" class="prime">
                        <i class='bx bx-cog'></i>
                    </button>
                <?php } ?>
            </div>
        </div>
        <?php } ?>
        

    </form>
    </div>

    <!-- searchPop -->
    <div id="searchPop" class="multiModal">
        <div class="modalBox">
     
            <div class="mbHeader">
                <div class="title">
                    <h2>검색</h2>
                </div>
            </div>
    
            <form name="fsearch" method="get">
            <fieldset class="searchBox">
            <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
            <input type="hidden" name="sca" value="<?php echo $sca ?>">
            <input type="hidden" name="sop" value="and">
            <label for="sfl" class="sr-only">검색대상</label>

            <div class="mbBody bg-clg padding-x">
                <div class="mbBodyContents">
                    <div class="searchForm">
                        <select name="sfl" id="sfl">
                            <?php echo get_board_sfl_select_options($sfl); ?>
                        </select>

                        <label for="stx" class="sr-only">검색어<strong class="sr-only"> 필수</strong></label>
                        <input type="text" name="stx" value="<?php echo htmlspecialchars(stripslashes($stx), ENT_QUOTES, 'UTF-8') ?>" required id="stx" size="25" maxlength="20" placeholder="검색어를 입력해주세요">
                    </div>
                </div>
            </div>


            <div class="buttonWrap">
                <button type="button" id="searchPopClose">닫기</button>
                <button type="submit" value="검색" class="bg-pr">검색</button>
            </div>

            </fieldset>
            </form>

        </div>
    </div>

 </div>

<script>
    jQuery(function($){
        // 게시판 검색
        $("#searchPopOn").on("click", function() {
            $('#searchPop').removeClass("closing").addClass("active");
        })
        $('#searchPopClose').click(function(){
            $('#searchPop').addClass("closing");
            setTimeout(function() {
                $('#searchPop').removeClass("active closing");
            }, 300);
        });
    });
</script>

<?php if (isset($stx) && trim($stx) !== ''): ?>
    <!-- ✅ 검색이 실행된 경우에만 보여줄 UI -->
    <div class="boardViewWrap">
        <div class="searchResult">
            <i class='bx bx-bot'></i>
            <p><strong><?= get_text($stx); ?></strong> 에 대한 검색 결과입니다.</p>
        </div>
    </div>
<?php endif; ?>

<!--  게시판 검색 끝 -->


<?php if ($is_checkbox) { ?>
<script>
function all_checked(sw) {
var f = document.fboardlist;

for (var i=0; i<f.length; i++) {
	if (f.elements[i].name == "chk_wr_id[]")
		f.elements[i].checked = sw;
}
}

function fboardlist_submit(f) {
    var chk_count = 0;

    for (var i=0; i<f.length; i++) {
        if (f.elements[i].name == "chk_wr_id[]" && f.elements[i].checked)
            chk_count++;
    }

    if (!chk_count) {
        alert(document.pressed + "할 게시물을 하나 이상 선택하세요.");
        return false;
    }

    if(document.pressed == "선택복사") {
        select_copy("copy");
        return false;
    }

    if(document.pressed == "선택이동") {
        select_copy("move");
        return false;
    }

    if(document.pressed == "선택삭제") {
        if (!confirm("선택한 게시물을 정말 삭제하시겠습니까?\n\n한번 삭제한 자료는 복구할 수 없습니다\n\n답변글이 있는 게시글을 선택하신 경우\n답변글도 선택하셔야 게시글이 삭제됩니다."))
            return false;

        f.removeAttribute("target");
        f.action = g5_bbs_url+"/board_list_update.php";
    }

    return true;
}

// 선택한 게시물 복사 및 이동
function select_copy(sw) {
    var f = document.fboardlist;

    if (sw == 'copy')
        str = "복사";
    else
        str = "이동";

    var sub_win = window.open("", "move", "left=50, top=50, width=500, height=550, scrollbars=1");

    f.sw.value = sw;
    f.target = "move";
    f.action = g5_bbs_url+"/move.php";
    f.submit();
}
</script>
<?php } ?>
<script>
$(document).ready(function () {
  var $listBox = $('.listBox');
  var $activeItem = $listBox.find('.cardBox.active');

  var adjust = -280; // 위치 보정값 (필요에 따라 조절)

  if ($activeItem.length) {
    var relativeTop = $activeItem.position().top;

    // 0.5초(500ms) 딜레이 후 스크롤 이동
    setTimeout(function () {
      $listBox.animate({
        scrollTop: $listBox.scrollTop() + relativeTop + adjust
      }, 300);
    }, 300);
  }
});
</script>