<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php');
// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/css/style.css">', 0);

// 댓글 작성자 프로필 이미지 가져오기 함수
if (!function_exists('get_recent_comment_profiles')) {
    function get_recent_comment_profiles($wr_id, $bo_table, $limit = 3) {
        global $g5;

        $wr_id = (int)$wr_id;
        $limit = (int)$limit;

        // 댓글 작성자 mb_id 가져오기 (중복 제거, 최신순)
        $sql = "SELECT DISTINCT mb_id 
                FROM {$g5['write_prefix']}{$bo_table} 
                WHERE wr_parent = '{$wr_id}' 
                AND wr_is_comment = 1 
                AND mb_id != '' 
                ORDER BY wr_id DESC 
                LIMIT {$limit}";
        
        $result = sql_query($sql);
        $profiles = array();
        $total_count = 0;
        
        // 전체 댓글 수 계산
        $count_sql = "SELECT COUNT(DISTINCT mb_id) as total_count 
                      FROM {$g5['write_prefix']}{$bo_table} 
                      WHERE wr_parent = '{$wr_id}' 
                      AND wr_is_comment = 1 
                      AND mb_id != ''";
        $count_result = sql_fetch($count_sql);
        $total_count = $count_result['total_count'];
        
        // 프로필 이미지와 닉네임 가져오기
        while ($row = sql_fetch_array($result)) {
            if ($row['mb_id']) {
                $member_info = get_member($row['mb_id']);
                $nickname = $member_info['mb_nick'] ? $member_info['mb_nick'] : $member_info['mb_name'];
                $nickname = htmlspecialchars($nickname, ENT_QUOTES, 'UTF-8');
                $profile_img = get_member_profile_img($row['mb_id']);

                // alt 속성에 닉네임 추가
                $profile_img = str_replace('alt="profile_image"', 'alt="'.$nickname.'님의 프로필"', $profile_img);
                $profile_img = str_replace('alt=""', 'alt="'.$nickname.'님의 프로필"', $profile_img);
                $profile_img = str_replace('title=""', 'title="'.$nickname.'님"', $profile_img);
                
                $profiles[] = $profile_img;
            }
        }
        
        return array(
            'profiles' => $profiles,
            'total_count' => $total_count
        );
    }
}

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
            <!-- total -->
            <div class="total">
                <span>Total <strong><?php echo number_format($total_count) ?></strong> 건</span>
                <span><strong><?php echo $page ?></strong> 페이지</span>
            </div>
        </div>
        <!-- tools -->
        <div class="tools">
            
            <?php if ($write_href) { ?>
                <button type="button" onclick="location.href='<?php echo $write_href ?>';" class="color-pr" title="등록">
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
            </div>
           
            <?php if ($rss_href) { ?>
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

        <div class="listBox" data-board="uxc_qa">
            <!-- 테이블 헤더 -->
            <div class="listHeader">
                <?php if ($is_checkbox) { ?>
                <div class="col col-check">
                    <div class="opt">
                        <input type="checkbox" id="chkall" onclick="if (this.checked) all_checked(true); else all_checked(false);">
                        <label for="chkall"></label>
                    </div>
                </div>
                <?php } ?>
                <div class="col col-num">번호</div>
                <div class="col col-subject">제목</div>
                <div class="col col-status">상태</div>
                <div class="col col-name">글쓴이</div>
                <div class="col col-date">날짜</div>
                <div class="col col-hit">조회</div>
            </div>
            
            <?php
            for ($i=0; $i<count($list); $i++) {
            ?>
            <!-- cardBox -->
            <div class="cardBox <?php if ($list[$i]['is_notice']) echo "bo_notice"; ?> <?php if ($wr_id == $list[$i]['wr_id']) echo "active"; ?>">
                <?php if ($is_checkbox) { ?>
                <div class="col col-check">
                    <div class="opt">
                        <input type="checkbox" name="chk_wr_id[]" value="<?php echo $list[$i]['wr_id'] ?>" id="chk_wr_id_<?php echo $i ?>" class="selec_chk">
                        <label for="chk_wr_id_<?php echo $i ?>">
                            <span class="sr-only"><?php echo $list[$i]['subject'] ?></span>
                        </label>
                    </div>
                </div>
                <?php } ?>
                
                <div class="col col-num">
                    <?php
                    if (!empty($list[$i]['is_notice'])) // 공지사항
                        echo '<span class="bo_notice_label">공지</span>';
                    else if ($wr_id == $list[$i]['wr_id'])
                        echo "<span class=\"bo_current\">열람</span>";
                    else
                        echo $list[$i]['num'];
                    ?>
                </div>
                
                <div class="col col-subject">
                    <div class="bo_tit">
                        <?php echo $list[$i]['icon_reply']; ?>
                        <a href="<?php echo $list[$i]['href'] ?>">
                            <?php echo $list[$i]['subject'] ?>
                            <?php
                            // 댓글수 표시
                            if ($list[$i]['wr_comment'])
                                echo '<span class="comment_cnt">'.$list[$i]['wr_comment'].'</span>';
                            ?>
                            <?php
                            // 카테고리 표시
                            if ($is_category && $list[$i]['ca_name']) {
                                echo '<span class="bo_cate_link">'.htmlspecialchars($list[$i]['ca_name'], ENT_QUOTES, 'UTF-8').'</span>';
                            }
                            ?>

                            <?php
                                // 아이콘 표시
                                if (!empty($list[$i]['icon_new'])) echo '<span class="iconBox bg-pr color-wh round-s">N</span>';
                                if (!empty($list[$i]['icon_hot'])) echo '<span class="hot_icon">H</span>';
                                if (!empty($list[$i]['icon_file'])) echo '<span class="file_icon"><i class="bx bx-file"></i></span>';
                                if (!empty($list[$i]['icon_link'])) echo '<span class="link_icon"><i class="bx bx-link"></i></span>';
                                if (!empty($list[$i]['icon_secret'])) echo '<span class="secret_icon"><i class="bx bx-lock-alt"></i></span>';
                            ?>
                        </a>

                    </div>
                </div>

                <div class="col col-status">
                    <?php
                    // 답변 상태 표시 (댓글이 있으면 답변 완료, 없으면 답변 대기)
                    if ($list[$i]['wr_comment'] > 0) {
                        echo '<span class="status-badge color-wh-only status-completed">답변 완료</span>';
                    } else {
                        echo '<span class="status-badge color-wh-only status-waiting">답변 대기</span>';
                    }
                    ?>
                </div>

                <div class="col col-name sv_use"><?php echo $list[$i]['name'] ?></div>
                <div class="col col-date"><?php echo $list[$i]['datetime2'] ?></div>
                <div class="col col-hit"><?php echo $list[$i]['wr_hit'] ?></div>
                
                <!-- 모바일용 메타 정보 그룹 -->
                <div class="mobile-meta">
                    <?php
                    // 모바일용 답변 상태 표시
                    if ($list[$i]['wr_comment'] > 0) {
                        echo '<span class="meta-status status-badge status-completed">답변 완료</span>';
                    } else {
                        echo '<span class="meta-status status-badge status-waiting">답변 대기</span>';
                    }
                    ?>
                    <span class="meta-name sv_use"><?php echo $list[$i]['name'] ?></span>
                    <span class="meta-date"><?php echo $list[$i]['datetime2'] ?></span>
                    <span class="meta-hit">조회 <?php echo $list[$i]['wr_hit'] ?></span>
                </div>
            </div>
            <?php } ?>
            
            <!-- empty -->
            <?php if (count($list) == 0) { echo '<div class="cardBox empty">게시물이 없습니다.</div>'; } ?>
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