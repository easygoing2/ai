<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php');
// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/css/style.css">', 0);

// Toast UI Editor 사용 시에만 리소스 로드
if (isset($config['cf_editor']) && $config['cf_editor'] === 'uxc_toastuieditor') {
    include_once(G5_PLUGIN_PATH.'/editor/uxc_toastuieditor/board_common.php');
}

// 댓글 작성자 프로필 이미지 가져오기 함수
if (!function_exists('get_recent_comment_profiles')) {
    function get_recent_comment_profiles($wr_id, $bo_table, $limit = 3) {
        global $g5;
        
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

// $row = sql_fetch(" select wr_id from {$write_table} where wr_is_comment = 0 order by wr_id desc limit 1 ");
// if ($row['wr_id']) {
//     goto_url(G5_HTTP_BBS_URL.'/board.php?bo_table='.$bo_table.'&amp;wr_id='.$row['wr_id']);
// }

// function is_board_searching() {
//     return isset($_GET['stx']) && trim($_GET['stx']) !== '';
// }

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
        <input type="hidden" name="page" value="<?php echo $page ?>">
        <input type="hidden" name="sw" value="">

        <div class="listBox" data-board="uxc_blog">
            <?php
            for ($i=0; $i<count($list); $i++) {
                // 썸네일 이미지 추출
                $thumb = get_list_thumbnail($board['bo_table'], $list[$i]['wr_id'], $board['bo_gallery_width'], $board['bo_gallery_height'], false, true);
                
                // 본문에서 이미지 추출 (썸네일이 없을 경우)
                if(!$thumb['src']) {
                    $matches = array();
                    if(preg_match("/<img[^>]*src=[\"']?([^>\"']+)[\"']?[^>]*>/i", $list[$i]['wr_content'], $matches)) {
                        $thumb['src'] = $matches[1];
                    }
                }
                
                // 본문 내용 정리
                // Toast UI Editor로 작성된 콘텐츠인지 확인
                $is_markdown = false;
                if (strpos($list[$i]['wr_content'], '<!--TOASTUI_EDITOR_MARKDOWN-->') === 0) {
                    $is_markdown = true;
                    // 마크다운 구분자 제거
                    $content = str_replace('<!--TOASTUI_EDITOR_MARKDOWN-->', '', $list[$i]['wr_content']);
                    $content = ltrim($content, "\n");
                } else if (strpos($list[$i]['wr_content'], '<!--TOASTUI_EDITOR_HTML-->') === 0) {
                    // HTML 모드로 작성된 경우
                    $content = str_replace('<!--TOASTUI_EDITOR_HTML-->', '', $list[$i]['wr_content']);
                    $content = ltrim($content, "\n");
                } else {
                    $content = conv_content($list[$i]['wr_content'], $list[$i]['wr_option']);
                }
            ?>
            <!-- Blog Card -->
            <article class="blog-card <?php if ($list[$i]['is_notice']) echo "bo_notice"; ?> <?php if ($wr_id == $list[$i]['wr_id']) echo "active"; ?>">
                <!-- 카드 헤더 -->
                <div class="card-header">
                    <?php if ($list[$i]['is_notice']) { ?>
                    <span class="notice-badge">공지</span>
                    <?php } ?>
                    <h2 class="blog-title">
                        <?php echo $list[$i]['subject'] ?>
                        <?php if ($list[$i]['wr_comment']) { ?>
                        <span class="comment-count"><?php echo $list[$i]['wr_comment'] ?></span>
                        <?php } ?>
                        <?php
                        // 아이콘 표시
                        if (!empty($list[$i]['icon_new'])) echo '<span class="icon-new">N</span>';
                        if (!empty($list[$i]['icon_hot'])) echo '<span class="icon-hot">H</span>';
                        if (!empty($list[$i]['icon_file'])) echo '<span class="icon-file"><i class="bx bx-paperclip"></i></span>';
                        if (!empty($list[$i]['icon_link'])) echo '<span class="icon-link"><i class="bx bx-link"></i></span>';
                        if (!empty($list[$i]['icon_secret'])) echo '<span class="icon-secret"><i class="bx bx-lock-alt"></i></span>';
                        ?>
                    </h2>
                    <div class="blog-meta">
                        <span class="meta-author">
                            <i class="bx bx-user"></i>
                            <?php echo $list[$i]['name'] ?>
                        </span>
                        <span class="meta-date">
                            <i class="bx bx-calendar"></i>
                            <?php echo $list[$i]['datetime2'] ?>
                        </span>
                        <?php if ($is_category && $list[$i]['ca_name']) { ?>
                        <span class="meta-category">
                            <i class="bx bx-folder"></i>
                            <?php echo $list[$i]['ca_name'] ?>
                        </span>
                        <?php } ?>
                    </div>
                </div>
                
                <!-- 카드 본문 -->
                <div class="card-body">
                    <?php 
                    // 유튜브 영상 처리
                    if ($list[$i]['wr_10']) {
                        function extractYouTubeID($url) {
                            preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([^\&\?\/]+)/', $url, $matches);
                            return $matches[1] ?? '';
                        }
                        $youtube_id = extractYouTubeID($list[$i]['wr_10']);
                        
                        if ($youtube_id) {
                    ?>
                    <div class="blog-youtube">
                        <iframe width="100%" height="400" src="https://www.youtube.com/embed/<?php echo $youtube_id; ?>" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    </div>
                    <?php 
                        }
                    } else if($thumb['src']) { 
                    ?>
                    <div class="blog-thumbnail">
                        <img src="<?php echo $thumb['src'] ?>" alt="<?php echo $list[$i]['subject'] ?>">
                    </div>
                    <?php } ?>
                    
                    <?php if($is_markdown) { ?>
                        <!-- 마크다운 소스를 data 속성에 숨김 -->
                        <div class="blog-content toastui-editor-contents" 
                             data-markdown="<?php echo htmlspecialchars($content); ?>"
                             data-list-item="<?php echo $i ?>"
                             style="min-height: 100px;">
                            <!-- 로딩 표시 -->
                            <div class="markdown-loading" style="padding:10px;text-align:center;color:#999;">
                                <span>콘텐츠를 불러오는 중...</span>
                            </div>
                        </div>
                    <?php } else { ?>
                        <!-- 일반 HTML 콘텐츠 -->
                        <div class="blog-content">
                            <?php echo $content ?>
                        </div>
                    <?php } ?>
                </div>
                
                <!-- 카드 푸터 -->
                <div class="card-footer">
                    <div class="blog-stats">
                        <span class="stat-item">
                            <i class="bx bx-show"></i>
                            조회 <?php echo $list[$i]['wr_hit'] ?>
                        </span>
                        <?php if ($list[$i]['wr_comment']) { ?>
                        <span class="stat-item">
                            <i class="bx bx-comment"></i>
                            댓글 <?php echo $list[$i]['wr_comment'] ?>
                        </span>
                        <?php } ?>
                    </div>
                    <div class="blog-actions">
                        <a href="<?php echo $list[$i]['href'] ?>" class="btn-readmore">
                            자세히 보기 <i class="bx bx-right-arrow-alt"></i>
                        </a>
                        <?php if ($is_checkbox) { ?>
                        <div class="opt">
                            <input type="checkbox" name="chk_wr_id[]" value="<?php echo $list[$i]['wr_id'] ?>" id="chk_wr_id_<?php echo $i ?>" class="selec_chk">
                            <label for="chk_wr_id_<?php echo $i ?>">
                                <span class="sr-only">선택</span>
                            </label>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </article>
            <?php } ?>
            
            <!-- empty -->
            <?php if (count($list) == 0) { echo '<article class="blog-card empty">게시물이 없습니다.</article>'; } ?>
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

    <?php if (isset($config['cf_editor']) && $config['cf_editor'] === 'uxc_toastuieditor') { ?>
    <!-- 마크다운 렌더링 스크립트 -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toast UI Editor가 로드될 때까지 대기
        function waitForToastUI(callback) {
            if (window.toastui && window.toastui.Editor) {
                callback();
            } else {
                setTimeout(() => waitForToastUI(callback), 50);
            }
        }
        
        waitForToastUI(function() {
            // 모든 마크다운 콘텐츠 찾기
            const markdownContents = document.querySelectorAll('[data-markdown="true"]');
            
            markdownContents.forEach(function(container, index) {
                const contentId = container.getAttribute('data-content-id');
                const sourceDiv = document.getElementById(contentId);
                const viewerDiv = document.getElementById(contentId.replace('blog-content', 'blog-viewer'));
                
                if (sourceDiv && viewerDiv) {
                    // 마크다운 텍스트 가져오기 (하이퍼링크 유지)
                    const markdown = sourceDiv.textContent;
                    
                    try {
                        // 임시 켸테이너 생성
                        const tempDiv = document.createElement('div');
                        tempDiv.style.display = 'none';
                        document.body.appendChild(tempDiv);
                        
                        // Editor를 viewer 모드로 생성하여 HTML 변환
                        const tempEditor = new toastui.Editor({
                            el: tempDiv,
                            viewer: true,
                            initialValue: markdown || ' ',
                            usageStatistics: false
                        });
                        
                        // 변환된 HTML 가져오기
                        const htmlContent = tempEditor.getHTML();
                        
                        // 뷰어 div에 HTML 삽입
                        viewerDiv.innerHTML = htmlContent;
                        viewerDiv.classList.add('toastui-editor-contents');
                        
                        // 임시 켸테이너 제거
                        document.body.removeChild(tempDiv);
                        
                        // 코드 하이라이팅 적용
                        setTimeout(() => {
                            if (window.Prism) {
                                Prism.highlightAllUnder(viewerDiv);
                            }
                        }, 100);
                        
                        // 렌더링 완료 후 소스 숨기기
                        sourceDiv.style.display = 'none';
                        
                        // 현재 테마가 다크모드인 경우 toastui-editor-dark 클래스 추가
                        if (localStorage.getItem("theme") === "darkMode") {
                            viewerDiv.classList.add('toastui-editor-dark');
                        }
                        
                    } catch (error) {
                        console.error('Error rendering markdown:', error);
                        // 에러 발생 시 원본 텍스트 표시
                        viewerDiv.innerHTML = '<pre>' + markdown + '</pre>';
                    }
                }
            });
            
            // 현재 테마가 다크모드인 경우 listBox에 toastui-editor-dark 클래스 추가
            const listBox = document.querySelector('.listBox[data-board="uxc_blog"]');
            if (listBox && localStorage.getItem("theme") === "darkMode") {
                listBox.classList.add('toastui-editor-dark');
            }
        });
    });
    </script>
    <?php } ?>

    <!-- searchPop -->
    <div id="searchPop" class="multiModal">
        <div class="modalBox">
     
            <div class="mbHeader">
                <div class="title">
                    <h2>검색</h2>
                </div>
            </div>
    
            <fieldset class="searchBox">
            <form name="fsearch" method="get">
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
                        <input type="text" name="stx" value="<?php echo stripslashes($stx) ?>" required id="stx" size="25" maxlength="20" placeholder="검색어를 입력해주세요">
                    </div>
                </div>
            </div>
            
    
            <div class="buttonWrap">
                <button type="button" id="searchPopClose">닫기</button>
                <button type="submit" value="검색" class="bg-pr">검색</button>
            </div>

            </form>
            </fieldset>

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
    var $activeItem = $listBox.find('ul li.active');

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

<?php if (isset($config['cf_editor']) && $config['cf_editor'] === 'uxc_toastuieditor') { ?>
<!-- 리스트용 마크다운 렌더링 스크립트 -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const markdownContainers = document.querySelectorAll('.blog-content[data-markdown]');
    if (!markdownContainers.length) return;
    
    // Toast UI Editor 로드 대기
    function renderAllMarkdown() {
        if (!window.toastui || !window.toastui.Editor) {
            setTimeout(renderAllMarkdown, 50);
            return;
        }
        
        // 각 마크다운 콘텐츠 렌더링
        markdownContainers.forEach(function(container) {
            let markdown = container.dataset.markdown;
            
            
            try {
                // 임시 div에서 렌더링
                const tempDiv = document.createElement('div');
                tempDiv.style.display = 'none';
                document.body.appendChild(tempDiv);
                
                const viewer = new toastui.Editor({
                    el: tempDiv,
                    viewer: true,
                    initialValue: markdown || '',
                    usageStatistics: false
                });
                
                // HTML 추출
                const html = viewer.getHTML();
                
                // 로딩 메시지 제거하고 렌더링된 HTML 삽입
                container.innerHTML = html;
                
                // 정리
                document.body.removeChild(tempDiv);
                
            } catch (error) {
                console.error('Error rendering markdown:', error);
                container.innerHTML = '<div style="padding:10px;color:#999;">콘텐츠를 불러올 수 없습니다.</div>';
            }
        });
        
        // 코드 하이라이팅 적용
        if (window.Prism) {
            setTimeout(() => {
                Prism.highlightAll();
            }, 100);
        }
    }
    
    renderAllMarkdown();
});
</script>
<?php } ?>