<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// 선택삭제으로 인해 셀합치기가 가변적으로 변함
$colspan = 5;

if ($is_admin) $colspan++;

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$new_skin_url.'/style.css">', 0);
?>

<!-- 전체게시물 목록 시작 { -->
<form name="fnewlist" id="fnewlist" method="post" action="#" onsubmit="return fnew_submit(this);">
<input type="hidden" name="sw"       value="move">
<input type="hidden" name="view"     value="<?php echo $view; ?>">
<input type="hidden" name="sfl"      value="<?php echo $sfl; ?>">
<input type="hidden" name="stx"      value="<?php echo $stx; ?>">
<input type="hidden" name="bo_table" value="<?php echo $bo_table; ?>">
<input type="hidden" name="page"     value="<?php echo $page; ?>">
<input type="hidden" name="pressed"  value="">


<div class="newboardList">
    <?php if ($is_admin) { ?>    
    <div class="toolBox">
    	<h2 class="sr-only">New Post</h2>
        <div class="tools">
            <div class="opt">
                <input type="checkbox" id="all_chk" class="selec_chk">
                <label for="all_chk">
                    <span class="text">All Check</span>
                </label>
            </div>
        </div>
        <div class="tools">
            <button type="button" id="searchPopOn" title="검색" class="button shadowline-de round-m mBtn">
                <i class="bx bx-search-alt-2"></i>
            </button>
            <button type="submit" onclick="document.pressed=this.title" title="선택삭제" class="button shadowline-de round-m mBtn">
                <i class="bx bx-x"></i>
                <span class="sound_only">선택삭제</span>
            </button>
        </div>
    </div>
    <?php } ?>        
    <div class="listWrap">
        <ul>
            <?php
            for ($i=0; $i<count($list); $i++)
            {
                $num = $total_count - ($page - 1) * $config['cf_page_rows'] - $i;
                $gr_subject = cut_str($list[$i]['gr_subject'], 20);
                $bo_subject = cut_str($list[$i]['bo_subject'], 20);
                $wr_subject = get_text(cut_str($list[$i]['wr_subject'], 80));
            ?>
            <li class="cardBox bg-w round-m padding-m">
                
                <div class="post">
                    <div class="title">
                        <a href="<?php echo $list[$i]['href'] ?>" class="new_tit"><?php echo $list[$i]['comment'] ?><?php echo $wr_subject ?></a>
                    </div>
                    <div class="info">
						<?php if ($is_admin) { ?>
                        <div class="checkBox">
                            <div class="opt">
                                <input type="checkbox" name="chk_bn_id[]" value="<?php echo $i; ?>" id="chk_bn_id_<?php echo $i; ?>" class="selec_chk">
                                <label for="chk_bn_id_<?php echo $i; ?>">
                                    <span></span>
                                    <b class="sound_only"><?php echo $num?>번</b>
                                </label>
                                <input type="hidden" name="bo_table[<?php echo $i; ?>]" value="<?php echo $list[$i]['bo_table']; ?>">
                                <input type="hidden" name="wr_id[<?php echo $i; ?>]" value="<?php echo $list[$i]['wr_id']; ?>">
                            </div>
                        </div>
                        <?php } ?>
                        <div class="group"><a href="./new.php?gr_id=<?php echo $list[$i]['gr_id'] ?>"><?php echo $gr_subject ?></a></div>
                        <div class="board"><a href="<?php echo get_pretty_url($list[$i]['bo_table']); ?>"><?php echo $bo_subject ?></a></div>
                        <!--<div class="name"><?php echo $list[$i]['name'] ?></div> -->
                    </div>
                </div>
                <div class="date"><?php echo $list[$i]['datetime2'] ?></div>
            </li>
            <?php } ?>
        </ul>
    </div>
    <?php echo $write_pages ?>

</div>


</form>

<!-- searchPop -->
<div id="searchPop" class="multiModal">
    <div class="modalBox">
        <!-- mbHeader -->
        <div class="mbHeader">
            <div class="title">
                <h2>검색</h2>
            </div>
            <div class="tools">
                <button type="button" id="searchPopClose" title="닫기" class="close"><i class="bx bx-x"></i></button>
            </div>
        </div>
        <!-- mbBody -->
        <fieldset id="new_sch">
        <legend>상세검색</legend>
        <form name="fnew" method="get">


        <div class="mbBody">
            <div class="mbBodyContents">
                <div class="searchForm">
                    <?php echo $group_select ?>
                    <label for="view" class="sound_only">검색대상</label>
                    <select name="view" id="view">
                        <option value="">전체게시물
                        <option value="w">원글만
                        <option value="c">코멘트만
                    </select>

                    <label for="mb_id" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
    				<input type="text" name="mb_id" value="<?php echo $mb_id ?>" id="mb_id" required class="frm_input" size="40" placeholder="검색어를 입력해 주세요.">
                </div>
            </div>
        </div>
        
        <!-- buttonWrap -->
        <div class="buttonWrap">
            <button type="submit" class="button mBtn fw bg-pr">검색</button>
        </div>

        </form>
         <script>
		/* 셀렉트 박스에서 자동 이동 해제
		function select_change()
		{
			document.fnew.submit();
		}
		*/
		document.getElementById("gr_id").value = "<?php echo $gr_id ?>";
		document.getElementById("view").value = "<?php echo $view ?>";
		</script>
        </fieldset>

    </div>
</div>


<script>
    jQuery(function($){
        // 게시판 검색
        $("#searchPopOn").on("click", function() {
            $('#searchPop').addClass("active");
        })
        $('#searchPopClose').click(function(){
            $('#searchPop').removeClass("active");
        });
    });
</script>

<?php if ($is_admin) { ?>
<script>
$(function(){
    $('#all_chk').click(function(){
        $('[name="chk_bn_id[]"]').attr('checked', this.checked);
    });
});

function fnew_submit(f)
{
    f.pressed.value = document.pressed;

    var cnt = 0;
    for (var i=0; i<f.length; i++) {
        if (f.elements[i].name == "chk_bn_id[]" && f.elements[i].checked)
            cnt++;
    }

    if (!cnt) {
        alert(document.pressed+"할 게시물을 하나 이상 선택하세요.");
        return false;
    }

    if (!confirm("선택한 게시물을 정말 "+document.pressed+" 하시겠습니까?\n\n한번 삭제한 자료는 복구할 수 없습니다")) {
        return false;
    }

    f.action = "./new_delete.php";

    return true;
}
</script>
<?php } ?>
<!-- } 전체게시물 목록 끝 -->