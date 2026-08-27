<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 선택옵션으로 인해 셀합치기가 가변적으로 변함
$colspan = 6;

if ($is_checkbox) $colspan++;

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$qa_skin_url.'/style.css">', 0);
?>

<!-- QA 게시판 목록 -->
<div class="qaListWrap">
	<?php if ($category_option) { ?>
    <!-- 카테고리 시작 { -->
    <div class="catagory">
        <ul>
            <?php echo $category_option ?>
        </ul>
    </div>
    <!-- } 카테고리 끝 -->
    <?php } ?>
    
    <!-- 도구 모음 시작 { -->
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
                <button type="button" onclick="location.href='<?php echo $write_href ?>';" title="문의등록">
                    <i class='bx bx-edit-alt'></i>
                    <span class="sound_only">문의등록</span>
                </button>
            <?php } ?>   
            <button type="button" id="searchPopOn" title="검색">
                <i class='bx bx-search-alt-2'></i>
                <span class="sound_only">검색</span>
            </button>
            <?php if ($admin_href) { ?>
                <button type="button" onclick="location.href='<?php echo $admin_href ?>';" title="관리자">
                    <i class="bx bx-cog"></i>
                    <span class="sound_only">관리자</span>
                </button>
            <?php } ?>
        </div>
    </div>
    <!-- } 도구 모음 끝 -->

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
				        <input type="hidden" name="sca" value="<?php echo $sca ?>">
                        <input type="hidden" name="sop" value="and">
            <label for="sfl" class="sr-only">검색대상</label>

            <div class="mbBody bg-clg padding-x">
                <div class="mbBodyContents">
                    <div class="searchForm">
                        <select name="sfl" id="sfl">
                            <?php echo get_qa_sfl_select_options($sfl); ?>
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
	
    <!-- 목록 시작 { -->
    <form name="fqalist" id="fqalist" action="./qadelete.php" onsubmit="return fqalist_submit(this);" method="post">
    <input type="hidden" name="stx" value="<?php echo $stx; ?>">
    <input type="hidden" name="sca" value="<?php echo $sca; ?>">
    <input type="hidden" name="page" value="<?php echo $page; ?>">
    <input type="hidden" name="token" value="<?php echo get_text($token); ?>">
            
    <div class="listWrap">
        <div class="listBox" data-board="qa_list">
        <?php
        for ($i=0; $i<count($list); $i++) {
                $status_class = $list[$i]['qa_status'] ? 'answered' : 'waiting';
        ?>
            <a href="<?php echo $list[$i]['view_href']; ?>" class="cardBox <?php echo $status_class ?> bg-wh round-m padding-m">
                <div class="titleBox">
            <?php if ($is_checkbox) { ?>
                    <div class="checkBox" onclick="event.preventDefault(); event.stopPropagation();">
                        <div class="opt">
                            <input type="checkbox" name="chk_qa_id[]" value="<?php echo $list[$i]['qa_id'] ?>" id="chk_qa_id_<?php echo $i ?>">
                            <label for="chk_qa_id_<?php echo $i ?>"></label>
                        </div>
                    </div>
                    <?php } ?>
                    
                    <div class="user">
                        <?php 
                        if ($list[$i]['mb_id']) {
                            // 회원인 경우 프로필 이미지 사용
                            $profile_img = get_member_profile_img($list[$i]['mb_id']);
                            if ($profile_img) {
                                echo $profile_img;
                            } else {
                                echo '<div class="profile_default">' . mb_substr($list[$i]['name'], 0, 1) . '</div>';
                            }
                        } else {
                            // 비회원인 경우 첫 글자 표시
                            echo '<div class="profile_default">' . mb_substr($list[$i]['name'], 0, 1) . '</div>';
                        }
                        ?>
                    </div>
                    
                    <div class="contentInfo">
                        <div class="title">
                            <?php if ($list[$i]['category']) { ?>
                            <span class="categoryTag"><?php echo $list[$i]['category']; ?></span>
                            <?php } ?>
                            <span class="subject"><?php echo $list[$i]['subject']; ?></span>
                            <?php if ($list[$i]['icon_file']) { ?>
                            <i class="bx bx-download fileIcon"></i>
            <?php } ?>
                        </div>
                        <div class="add">
                            <!-- <span class="number"><?php echo $list[$i]['num']; ?></span> -->
                            <span class="date"><?php echo $list[$i]['date']; ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="infoBox">
                    <div class="statusBox">
                        <span class="status <?php echo $status_class ?>">
                            <?php echo ($list[$i]['qa_status'] ? '답변완료' : '답변대기'); ?>
                        </span>
                    </div>
                </div>
            </a>
        <?php
        }
        ?>

            <?php if ($i == 0) { ?>
            <div class="cardBox empty bg-wh round-m padding-m">
                <div class="emptyMessage">
                    <i class="bx bx-message-square-dots"></i>
                    <span>등록된 문의가 없습니다.</span>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
    
    <!-- 페이징 -->
    <div class="listFooter">
	<?php echo $list_pages; ?>
    </div>
	
    <!-- 하단 버튼 -->
    <div class="admBtn">
        <div class="buttonWrap">
        	<?php if ($is_checkbox) { ?>
            <button type="submit" name="btn_submit" value="선택삭제" title="선택삭제" onclick="document.pressed=this.value" class="button warning">
                <i class="bx bx-trash"></i>
                <span>선택삭제</span>
            </button>
            <?php } ?>
            <?php if ($write_href) { ?>
            <button type="button" onclick="location.href='<?php echo $write_href ?>';" class="button prime" title="문의등록">
                <i class="bx bx-edit-alt"></i>
                <span>문의등록</span>
            </button>
            <?php } ?>
        </div>
    </div>
    </form>
</div>

<?php if($is_checkbox) { ?>
<noscript>
<p>자바스크립트를 사용하지 않는 경우<br>별도의 확인 절차 없이 바로 선택삭제 처리하므로 주의하시기 바랍니다.</p>
</noscript>
<?php } ?>

<script>
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

<?php if ($is_checkbox) { ?>
function all_checked(sw) {
    var f = document.fqalist;

    for (var i=0; i<f.length; i++) {
        if (f.elements[i].name == "chk_qa_id[]")
            f.elements[i].checked = sw;
    }
}

function fqalist_submit(f) {
    var chk_count = 0;

    for (var i=0; i<f.length; i++) {
        if (f.elements[i].name == "chk_qa_id[]" && f.elements[i].checked)
            chk_count++;
    }

    if (!chk_count) {
        alert(document.pressed + "할 게시물을 하나 이상 선택하세요.");
        return false;
    }

    if(document.pressed == "선택삭제") {
        if (!confirm("선택한 게시물을 정말 삭제하시겠습니까?\n\n한번 삭제한 자료는 복구할 수 없습니다"))
            return false;
    }

    return true;
}
<?php } ?>
</script>
<!-- } QA 게시판 목록 끝 -->