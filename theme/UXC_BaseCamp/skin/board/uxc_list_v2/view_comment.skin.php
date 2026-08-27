<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>
<?php
// UXC 댓글 페이징 설정
$uxc_cmt_per_page = defined('UXC_COMMENT_PER_PAGE') ? UXC_COMMENT_PER_PAGE : 20;
$uxc_cmt_total = isset($write['wr_comment']) ? (int)$write['wr_comment'] : 0;
$uxc_cmt_paging = ($uxc_cmt_per_page > 0 && $uxc_cmt_total > $uxc_cmt_per_page);
if ($uxc_cmt_paging) {
    $uxc_cmt_total_pages = (int)ceil($uxc_cmt_total / $uxc_cmt_per_page);
    $uxc_cmt_current_page = $uxc_cmt_total_pages; // 마지막 페이지(최신) 기본 표시
}
?>

<script>
    // 글자수 제한
    var char_min = parseInt(<?php echo (int)$comment_min ?>); // 최소
    var char_max = parseInt(<?php echo (int)$comment_max ?>); // 최대
</script>

<!-- 댓글 시작 { -->
<ajaxcomment>
<section id="bo_vc" class="commentMedia">
    <h2 class="sr-only">댓글목록</h2>
    <?php
    // 페이징 활성화 시 마지막 페이지 댓글만 표시
    if (isset($uxc_cmt_paging) && $uxc_cmt_paging) {
        $uxc_offset = ($uxc_cmt_current_page - 1) * $uxc_cmt_per_page;
        $list = array_slice($list, $uxc_offset, $uxc_cmt_per_page);
    }
    $cmt_amt = count($list);

    // 댓글 수정 데이터 미리 조회 (루프 밖에서 1회만)
    $c_wr_content = '';
    if ($w == 'cu' && $c_id) {
        $sql = " select wr_id, wr_content, mb_id from $write_table where wr_id = '".(int)$c_id."' and wr_is_comment = '1' ";
        $cmt = sql_fetch($sql);
        if ($cmt && ($is_admin || ($member['mb_id'] == $cmt['mb_id'] && $cmt['mb_id']))) {
            $c_wr_content = $cmt['wr_content'];
        }
    }

    for ($i=0; $i<$cmt_amt; $i++) {
        $comment_id = $list[$i]['wr_id'];
        $cmt_depth = strlen($list[$i]['wr_comment_reply']) * 20;
        $comment = $list[$i]['content'];
        $comment = preg_replace("/\[\<a\s.*href\=\"(http|https|ftp|mms)\:\/\/([^[:space:]]+)\.(mp3|wma|wmv|asf|asx|mpg|mpeg)\".*\<\/a\>\]/i", "<script>doc_write(obj_movie('$1://$2.$3'));</script>", $comment);
        if (function_exists('uxc_comment_render_img')) { $comment = uxc_comment_render_img($comment); }
        if (function_exists('uxc_comment_render_video')) { $comment = uxc_comment_render_video($comment); }
        $cmt_sv = $cmt_amt - $i + 1; // 댓글 헤더 z-index 재설정 ie8 이하 사이드뷰 겹침 문제 해결
		$c_reply_href = $comment_common_url.'&amp;c_id='.$comment_id.'&amp;w=c#bo_vc_w';
		$c_edit_href = $comment_common_url.'&amp;c_id='.$comment_id.'&amp;w=cu#bo_vc_w';
		$is_comment_reply_edit = ($list[$i]['is_reply'] || $list[$i]['is_edit'] || $list[$i]['is_del']) ? 1 : 0;
	?>


    <article id="c_<?php echo $comment_id ?>" class="media <?php echo ($member['mb_id']==$list[$i]['mb_id']) ? 'my' :''; ?>" <?php if ($cmt_depth) { ?>style="margin-left:<?php echo $cmt_depth ?>px;"<?php } ?>>
        <div class="media-body">
            <header class="media-heading" style="z-index:<?php echo $cmt_sv; ?>">
                <h2 class="sr-only"><?php echo get_text($list[$i]['wr_name']); ?>님의 <?php if ($cmt_depth) { ?><span class="sound_only">댓글의</span><?php } ?> 댓글</h2>

                <div class="profile_img"><?php echo get_member_profile_img($list[$i]['mb_id']); ?></div>
                <?php echo $list[$i]['name'] ?>
                
                <?php if ($is_ip_view) { ?>
                <span class="sound_only">아이피</span>
                <span>(<?php echo htmlspecialchars($list[$i]['ip'], ENT_QUOTES, 'UTF-8'); ?>)</span>
                <?php } ?>
                
                <span class="sound_only">작성일</span>
                <span class="bo_vc_hdinfo"><time datetime="<?php echo date('Y-m-d\TH:i:s+09:00', strtotime($list[$i]['datetime'])) ?>"><?php echo $list[$i]['datetime'] ?></time></span>
                <?php
                include(G5_SNS_PATH.'/view_comment_list.sns.skin.php');
                ?>
            </header>

            <div class="media-content">
                <!-- 댓글 출력 -->
                <div class="cmt_contents">
                    <p class="text">
                        <?php if (strstr($list[$i]['wr_option'], "secret")) { ?>
                            <i class='bx bx-lock'></i>
                        <?php } ?>
                        <?php echo $comment ?>
                    </p>
                </div>

                <?php if($is_comment_reply_edit) { ?>
                <div class="tools">

                    <?php if ($list[$i]['is_reply']) { ?>
                        <a href="<?php echo $c_reply_href; ?>" onclick="comment_box('<?php echo $comment_id ?>', 'c'); return false;" title="답변" class="button mini borderline">
                            <i class='bx bx-reply'></i>
                        </a>
                    <?php } ?>
                    <?php if ($list[$i]['is_edit']) { ?>
                        <a href="<?php echo $c_edit_href; ?>" onclick="comment_box('<?php echo $comment_id ?>', 'cu'); return false;" title="수정" class="button mini borderline">
                            <i class="bx bx-edit"></i>
                        </a>
                    <?php } ?>
                    <?php if ($list[$i]['is_del']) { ?>
                        <a href="<?php echo $list[$i]['del_link']; ?>" onclick="return comment_delete(this);" title="삭제" class="button mini borderline">
                            <i class="bx bx-trash"></i>
                        </a>
                    <?php } ?>

                </div>
                <?php } ?>
                
            </div>

            <div id="edit_<?php echo $comment_id ?>" class="bo_vc_w"></div><!-- 수정 -->
            <div id="reply_<?php echo $comment_id ?>" class="bo_vc_w"></div><!-- 답변 -->
            <input type="hidden" value="<?php echo strstr($list[$i]['wr_option'],"secret") ?>" id="secret_comment_<?php echo $comment_id ?>">
            <textarea id="save_comment_<?php echo $comment_id ?>" style="display:none"><?php echo get_text($list[$i]['content1'], 0) ?></textarea>
        </div>

    </article>
    <?php } ?>

    <?php if (isset($uxc_cmt_paging) && $uxc_cmt_paging) { ?>
    <nav class="uxc-cmt-paging" aria-label="댓글 페이지">
        <div class="uxc-cmt-paging__info">
            <span class="uxc-cmt-paging__count"><?php echo $uxc_cmt_total ?></span>개의 댓글
        </div>
        <div class="pg_wrap" id="uxc_cmt_pages"><div class="pg"></div></div>
    </nav>
    <?php } ?>
    <?php if ($i == 0 && !(isset($uxc_cmt_paging) && $uxc_cmt_paging)) { //댓글이 없다면 (페이징 활성화 시 제외) ?><p id="bo_vc_empty">등록된 댓글이 없습니다.</p><?php } ?>

</section>
</ajaxcomment>

<script>
$(function() {
    // 댓글 옵션창 열기
    $(".btn_cm_opt").on("click", function() {
        $(this).parent("div").children(".bo_vc_act").show();
    });

    $(".more_opt_close").on("click", function() {
        $('.bo_vc_act').hide();
    });

    // 댓글 옵션창 닫기
    $(document).mouseup(function(e) {
        var container = $(".bo_vc_act");
        if (container.has(e.target).length === 0)
            container.hide();
    });
});
</script>
<!-- } 댓글 끝 -->

<?php if ($is_comment_write) {
    if($w == '')
        $w = 'c';
?>
<!-- 댓글 쓰기 시작 { -->
<aside id="bo_vc_w" class="commentWrite">
    <h2 class="sr-only">댓글쓰기</h2>
    <form name="fviewcomment" id="fviewcomment" action="<?php echo $comment_action_url; ?>" onsubmit="return fviewcomment_submit(this);" method="post" autocomplete="off">
        <input type="hidden" name="w" value="<?php echo $w ?>" id="w">
        <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
        <input type="hidden" name="wr_id" value="<?php echo $wr_id ?>">
        <input type="hidden" name="comment_id" value="<?php echo $c_id ?>" id="comment_id">
        <input type="hidden" name="sca" value="<?php echo $sca ?>">
        <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
        <input type="hidden" name="stx" value="<?php echo $stx ?>">
        <input type="hidden" name="spt" value="<?php echo $spt ?>">
        <input type="hidden" name="page" value="<?php echo $page ?>">
        <input type="hidden" name="is_good" value="">

        <div class="commentBox">
                                        <span class="sr-only">내용</span>

            <div class="textWriter">
                <?php if ($comment_min || $comment_max) { ?><strong id="char_cnt"><span id="char_count"></span>글자</strong><?php } ?>
                <textarea id="wr_content" name="wr_content" maxlength="10000" required class="" title="내용" placeholder="댓글내용을 입력해주세요" <?php if ($comment_min || $comment_max) { ?>onkeyup="check_byte('wr_content', 'char_count');" <?php } ?>><?php echo $c_wr_content; ?></textarea>
                

                <?php if ($is_guest) { ?>
                <div class="isGuest">
                    <label for="wr_name" class="sound_only">이름<strong> 필수</strong></label>
                    <input type="text" name="wr_name" value="<?php echo get_cookie("ck_sns_name"); ?>" id="wr_name" required class="required" placeholder="이름">
                    <label for="wr_password" class="sound_only">비밀번호<strong> 필수</strong></label>
                    <input type="password" name="wr_password" id="wr_password" required class="required" placeholder="비밀번호">
                </div>
                <?php } ?>

                <?php if($board['bo_use_sns'] && ($config['cf_facebook_appid'] || $config['cf_twitter_key'])) { ?>

                    <span class="sound_only">SNS 동시등록</span>
                    <span id="bo_vc_send_sns"></span>
                    <?php } ?>
                    <?php if ($is_guest) { ?>
                    <?php echo $captcha_html; ?>

                <?php } ?>
                
                <div id="uxc_cmt_preview" class="uxc-cmt-preview" style="display:none"></div>
                <div class="writeBox">
                    <div class="opt">
                        <input type="checkbox" name="wr_secret" value="secret" id="wr_secret">
                        <label for="wr_secret"><span class="text">비밀글</span></label>
                    </div>
                    <div class="uxc-cmt-actions">
                        <span class="uxc-cmt-yt-hint"><i class='bx bxl-youtube'></i> 유튜브 링크를 입력하면 자동 삽입</span>
                        <div class="uxc-cmt-resize">
                            <button type="button" class="uxc-cmt-resize-btn" onclick="uxcResizeTextarea(20)" title="입력창 늘리기"><i class='bx bx-plus'></i></button>
                            <button type="button" class="uxc-cmt-resize-btn" onclick="uxcResizeTextarea(-20)" title="입력창 줄이기"><i class='bx bx-minus'></i></button>
                        </div>
                        <?php if ($is_member) { ?>
                        <label for="uxc_cmt_file" class="uxc-cmt-upload-btn button bg-wh color-bl round-s bxicon sBtn line-cg" title="이미지 첨부">
                            <i class='bx bx-image-add'></i>
                            <input type="file" id="uxc_cmt_file" accept="image/*" style="display:none">
                        </label>
                        <?php } ?>
                        <button type="submit" id="btn_submit" class="button bg-pr color-wh-only round-s bxicon sBtn"><i class='bx bx-message-rounded'></i> 댓글등록</button>
                    </div>
                </div>
                <?php if ($comment_min || $comment_max) { ?><script>
                    check_byte('wr_content', 'char_count');
                </script><?php } ?>
                <script>
                    function uxcResizeTextarea(delta) {
                        var ta = document.getElementById('wr_content');
                        if (!ta) return;
                        var newHeight = ta.offsetHeight + delta;
                        if (newHeight < 40) newHeight = 40;
                        if (newHeight > 500) newHeight = 500;
                        ta.style.height = newHeight + 'px';
                    }
                    $(document).on("keyup change", "textarea#wr_content[maxlength]", function() {
                        var str = $(this).val()
                        var mx = parseInt($(this).attr("maxlength"))
                        if (str.length > mx) {
                            $(this).val(str.substr(0, mx));
                            return false;
                        }
                    });
                </script>
            </div>

        </div>

    </form>
</aside>

<script>
var save_before = '';
var save_html = document.getElementById('bo_vc_w').innerHTML;

function good_and_write()
{
    var f = document.fviewcomment;
    if (fviewcomment_submit(f)) {
        f.is_good.value = 1;
        f.submit();
    } else {
        f.is_good.value = 0;
    }
}

function fviewcomment_submit(f)
{
    var pattern = /(^\s*)|(\s*$)/g; // \s 공백 문자
    f.is_good.value = 0;

    // 양쪽 공백 없애기
    document.getElementById('wr_content').value = document.getElementById('wr_content').value.replace(pattern, "");
    if (char_min > 0 || char_max > 0)
    {
        check_byte('wr_content', 'char_count');
        var cnt = parseInt(document.getElementById('char_count').innerHTML);
        if (char_min > 0 && char_min > cnt)
        {
            alert("댓글은 "+char_min+"글자 이상 쓰셔야 합니다.");
            return false;
        } else if (char_max > 0 && char_max < cnt)
        {
            alert("댓글은 "+char_max+"글자 이하로 쓰셔야 합니다.");
            return false;
        }
    }
    else if (!document.getElementById('wr_content').value)
    {
        alert("댓글을 입력하여 주십시오.");
        return false;
    }

    if (typeof(f.wr_name) != 'undefined')
    {
        f.wr_name.value = f.wr_name.value.replace(pattern, "");
        if (f.wr_name.value == '')
        {
            alert('이름이 입력되지 않았습니다.');
            f.wr_name.focus();
            return false;
        }
    }

    if (typeof(f.wr_password) != 'undefined')
    {
        f.wr_password.value = f.wr_password.value.replace(pattern, "");
        if (f.wr_password.value == '')
        {
            alert('비밀번호가 입력되지 않았습니다.');
            f.wr_password.focus();
            return false;
        }
    }

    <?php if($is_guest) echo chk_captcha_js();  ?>

    // 금지단어 필터 체크 (비동기)
    document.getElementById("btn_submit").disabled = "disabled";
    $.ajax({
        url: g5_bbs_url+"/ajax.filter.php",
        type: "POST",
        data: {
            "subject": "",
            "content": f.wr_content.value
        },
        dataType: "json",
        cache: false,
        success: function(data) {
            if (data.content) {
                alert("내용에 금지단어('"+data.content+"')가 포함되어있습니다");
                f.wr_content.focus();
                document.getElementById("btn_submit").disabled = "";
                return;
            }

            // 필터 통과 → 댓글 등록 진행
            set_comment_token(f);
            _fviewcomment_ajax_submit(f);
        },
        error: function() {
            // 필터 서버 오류 시에도 등록 진행 (기존 동작 유지)
            set_comment_token(f);
            _fviewcomment_ajax_submit(f);
        }
    });

    return false;
}

//--- ajax comment update system 시작 ---//
function _fviewcomment_ajax_submit(f)
{
    $.ajax({
        url: f.action,
        type: 'POST',
        data: $(f).serialize(),
        dataType: 'html',
    })
    .done(function(str) {
        // 먼저 alert()가 있는지 체크
        let alertMatch = str.match(/alert\(['"](.+?)['"]\)/);
        if (alertMatch) {
            alert(alertMatch[1]); // 사용자에게 경고창 띄움
            document.getElementById("btn_submit").disabled = "";
            return; // 아래 로직은 실행하지 않음
        }

        var tempDom = $('<output>').append($.parseHTML(str));
        var title = $('title', tempDom).text();

        if (title === '') {
            comment_box('', 'c'); // 1. commentBox 원위치
            f.reset(); // 2. commentBox Form 리셋
            if (typeof uxcUpload !== 'undefined') uxcUpload.clearAll(); // 이미지 미리보기 초기화

            // 3. 코멘트 출력
            $.ajax({
                url: str,
                type: 'GET',
                dataType: 'html'
            })
            .done(function(str2) {
                var tempDom2 = $('<output>').append($.parseHTML(str2));
                $('ajaxcomment').replaceWith($('ajaxcomment', tempDom2));
                if (typeof uxcLightbox !== 'undefined') uxcLightbox.initComment();
            })
        }

        <?php if ($is_guest) { ?>
        $('#captcha_reload').trigger('click'); // 4. 캡차 리로드
        <?php } ?>

        document.getElementById("btn_submit").disabled = "";
    })
}
//--- ajax comment update system 종료 ---//

function comment_box(comment_id, work)
{
    var el_id,
        form_el = 'fviewcomment',
        respond = document.getElementById(form_el);

    // 댓글 아이디가 넘어오면 답변, 수정
    if (comment_id)
    {
        if (work == 'c')
            el_id = 'reply_' + comment_id;
        else
            el_id = 'edit_' + comment_id;
    }
    else
        el_id = 'bo_vc_w';

    if (save_before != el_id)
    {
        if (save_before)
        {
            document.getElementById(save_before).style.display = 'none';
        }

        document.getElementById(el_id).style.display = '';
        document.getElementById(el_id).appendChild(respond);
        //입력값 초기화
        document.getElementById('wr_content').value = '';
        
        // 댓글 수정
        if (work == 'cu')
        {
            document.getElementById('wr_content').value = document.getElementById('save_comment_' + comment_id).value;
            if (typeof char_count != 'undefined')
                check_byte('wr_content', 'char_count');
            if (document.getElementById('secret_comment_'+comment_id).value)
                document.getElementById('wr_secret').checked = true;
            else
                document.getElementById('wr_secret').checked = false;
        }

        document.getElementById('comment_id').value = comment_id;
        document.getElementById('w').value = work;

        if(save_before)
            $("#captcha_reload").trigger("click");

        save_before = el_id;
    }
}

//--- ajax comment delete system 시작 ---//
function comment_delete(that) {
    if (confirm('이 댓글을 삭제하시겠습니까?')) {
        // 삭제 전: 이미지 마커 추출 (이미지 파일 정리용)
        var commentEl = $(that).closest('article.media');
        var commentId = commentEl.length ? commentEl.attr('id').replace('c_', '') : '';
        var imgFiles = [];
        if (commentId) {
            var savedTa = document.getElementById('save_comment_' + commentId);
            if (savedTa) {
                var matches = savedTa.value.match(/\{\{img:([a-zA-Z0-9_\-\.]+)\}\}/g);
                if (matches) {
                    for (var m = 0; m < matches.length; m++) {
                        imgFiles.push(matches[m].replace('{{img:', '').replace('}}', ''));
                    }
                }
            }
        }

        $.ajax({
            url: that.href,
            type: 'GET',
            dataType: 'html',
            success: function(response) {
                // Gallery 원본 방식: 302 리다이렉트를 jQuery가 자동 추적하여
                // 게시글 페이지 전체 HTML을 수신, ajaxcomment 영역을 교체
                var tempDom = $('<div>').append($.parseHTML(response));
                var newComment = tempDom.find('ajaxcomment');

                if (newComment.length > 0) {
                    $('ajaxcomment').replaceWith(newComment);
                    if (typeof uxcLightbox !== 'undefined') uxcLightbox.initComment();
                    // 페이징 동기화
                    if (typeof uxcCmt !== 'undefined') {
                        setTimeout(function() { uxcCmt.sync('delete'); }, 300);
                    }
                } else {
                    // 에러 응답 감지 (alert.php 페이지)
                    var alertMatch = response.match(/alert\(["'](.+?)["']\)/);
                    if (alertMatch) {
                        alert(alertMatch[1]);
                    } else {
                        alert('댓글 목록 갱신에 실패했습니다.');
                    }
                }

                // 이미지 파일 정리
                if (imgFiles.length > 0) {
                    var formData = new FormData();
                    formData.append('action', 'cleanup_images');
                    for (var f = 0; f < imgFiles.length; f++) {
                        formData.append('filenames[]', imgFiles[f]);
                    }
                    fetch(g5_url + '/_uxcamp/lib/uxc_comment_upload.php', { method: 'POST', body: formData });
                }

                <?php if ($is_guest) { ?>
                $('#captcha_reload').trigger('click');
                <?php } ?>
            },
            error: function() {
                alert('댓글 삭제 중 오류가 발생했습니다.');
            }
        });
    }
    return false;
}
//--- ajax comment delete system 종료 ---//

comment_box('', 'c'); // 댓글 입력폼이 보이도록 처리하기위해서 추가 (root님)

<?php if($board['bo_use_sns'] && ($config['cf_facebook_appid'] || $config['cf_twitter_key'])) { ?>

$(function() {
    // sns 등록
    $("#bo_vc_send_sns").load(
        "<?php echo G5_SNS_URL; ?>/view_comment_write.sns.skin.php?bo_table=<?php echo $bo_table; ?>",
        function() {
            save_html = document.getElementById('bo_vc_w').innerHTML;
        }
    );
});
<?php } ?>
</script>
<?php if ($is_member) { ?>
<script>
var uxcUpload = {
    endpoint: g5_url + '/_uxcamp/lib/uxc_comment_upload.php',
    boTable: '<?php echo $bo_table ?>',

    init: function() {
        var fileInput = document.getElementById('uxc_cmt_file');
        if (!fileInput) return;

        fileInput.addEventListener('change', function(e) {
            var file = e.target.files[0];
            if (!file) return;

            if (!file.type.startsWith('image/')) {
                alert('이미지 파일만 업로드 가능합니다.');
                fileInput.value = '';
                return;
            }
            if (file.size > 2 * 1024 * 1024) {
                alert('2MB 이하 파일만 업로드 가능합니다.');
                fileInput.value = '';
                return;
            }

            uxcUpload.upload(file);
            fileInput.value = '';
        });
    },

    upload: function(file) {
        // 업로드 전 개수 체크
        var preview = document.getElementById('uxc_cmt_preview');
        if (preview && preview.children.length >= this.maxImages) {
            alert('이미지는 최대 ' + this.maxImages + '장까지 첨부 가능합니다.');
            return;
        }

        var formData = new FormData();
        formData.append('action', 'upload_comment_image');
        formData.append('bo_table', this.boTable);
        formData.append('uxc_file', file);

        fetch(this.endpoint, { method: 'POST', body: formData })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                var ta = document.getElementById('wr_content');
                ta.value = (ta.value.trim() ? ta.value.trimEnd() + '\n' : '') + '{{img:' + data.data.filename + '}}\n';
                uxcUpload.showPreview(data.data.thumb_url, data.data.filename);
            } else {
                alert(data.error || '업로드에 실패했습니다.');
            }
        })
        .catch(function() {
            alert('업로드 중 오류가 발생했습니다.');
        });
    },

    maxImages: 5,

    showPreview: function(url, filename) {
        var preview = document.getElementById('uxc_cmt_preview');
        if (!preview) return;

        // 이미지 개수 제한
        if (preview.children.length >= this.maxImages) {
            alert('이미지는 최대 ' + this.maxImages + '장까지 첨부 가능합니다.');
            return;
        }

        preview.style.display = 'flex';
        var item = document.createElement('div');
        item.className = 'uxc-cmt-preview__item';
        item.setAttribute('data-filename', filename);

        var img = document.createElement('img');
        img.src = url;
        img.alt = '미리보기';
        item.appendChild(img);

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.title = '삭제';
        btn.setAttribute('data-filename', filename);
        btn.addEventListener('click', function() { uxcUpload.removePreview(this, this.getAttribute('data-filename')); });
        var icon = document.createElement('i');
        icon.className = 'bx bx-x';
        btn.appendChild(icon);
        item.appendChild(btn);

        preview.appendChild(item);
    },

    removePreview: function(btn, filename) {
        var ta = document.getElementById('wr_content');
        ta.value = ta.value.replace('{{img:' + filename + '}}', '').replace(/\n\n+/g, '\n');
        btn.parentElement.remove();
        var preview = document.getElementById('uxc_cmt_preview');
        if (preview && !preview.children.length) preview.style.display = 'none';
    },

    clearAll: function() {
        var preview = document.getElementById('uxc_cmt_preview');
        if (preview) {
            preview.innerHTML = '';
            preview.style.display = 'none';
        }
    }
};

$(function() { uxcUpload.init(); });
</script>
<?php } ?>

<script>
var uxcVideo = {
    ytRegex: /https?:\/\/(?:www\.)?(?:youtube\.com\/(?:watch\?.*v=|embed\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/,

    init: function() {
        var ta = document.getElementById('wr_content');
        if (!ta) return;

        ta.addEventListener('paste', function() {
            setTimeout(function() {
                uxcVideo.checkForUrl(ta.value);
            }, 100);
        });
    },

    checkForUrl: function(text) {
        var lines = text.split('\n');
        for (var i = lines.length - 1; i >= 0; i--) {
            var line = lines[i].trim();
            var match = line.match(this.ytRegex);
            if (match) {
                this.showPreview(match[1]);
                return;
            }
        }
    },

    showPreview: function(videoId) {
        var preview = document.getElementById('uxc_cmt_preview');
        if (!preview) return;

        // 중복 방지
        if (preview.querySelector('[data-vid="' + videoId + '"]')) return;

        preview.style.display = 'flex';
        var item = document.createElement('div');
        item.className = 'uxc-cmt-preview__item uxc-cmt-preview__video';
        item.setAttribute('data-vid', videoId);

        var img = document.createElement('img');
        img.src = 'https://img.youtube.com/vi/' + videoId + '/mqdefault.jpg';
        img.alt = '동영상 미리보기';
        item.appendChild(img);

        var play = document.createElement('i');
        play.className = 'bx bx-play-circle uxc-cmt-preview__play';
        item.appendChild(play);

        preview.appendChild(item);
    }
};

$(function() { uxcVideo.init(); });
</script>
<?php } ?>
<!-- } 댓글 쓰기 끝 -->
<script>
jQuery(function($) {            
    //댓글열기
    $(".cmt_btn").click(function(e){
        e.preventDefault();
        $(this).toggleClass("cmt_btn_op");
        $("#bo_vc").toggle();
    });
});
</script>

<!-- wittazzurri 버튼옵션 수정 시작-->
<script>
function commentDisplay() {
    var redButton = arguments[0].nextElementSibling;
    if (redButton.style.display === "none") redButton.style.display = "block";
    redButton.onmouseover = function() { document.querySelector("body").onmouseup = function() { redButton.style.display = "block"; } }
    redButton.onmouseout = function() { document.querySelector("body").onmouseup = function() { redButton.style.display = "none"; } }
}
</script>
<!-- //wittazzurri 버튼옵션 수정 종료 -->

<?php if (isset($uxc_cmt_paging) && $uxc_cmt_paging) { ?>
<script>
var uxcCmt = {
    boTable: '<?php echo $bo_table ?>',
    wrId: <?php echo (int)$wr_id ?>,
    perPage: <?php echo (int)$uxc_cmt_per_page ?>,
    totalPages: <?php echo (int)$uxc_cmt_total_pages ?>,
    currentPage: <?php echo (int)$uxc_cmt_current_page ?>,
    total: <?php echo (int)$uxc_cmt_total ?>,
    endpoint: g5_url + '/_uxcamp/lib/uxc_comment.php',
    loading: false,

    init: function() {
        this.renderPaging();
    },

    load: function(page) {
        if (this.loading || page === this.currentPage) return;
        this.loading = true;

        var self = this;
        var body = 'action=load_comments&bo_table=' + encodeURIComponent(this.boTable)
            + '&wr_id=' + this.wrId
            + '&page=' + page
            + '&per_page=' + this.perPage;

        fetch(this.endpoint, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: body
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            self.loading = false;
            if (data.success) {
                self.currentPage = data.data.current_page;
                self.totalPages = data.data.total_pages;
                self.total = data.data.total;
                self.renderComments(data.data.html);
                self.renderPaging();
                self.scrollToComments();
            }
        })
        .catch(function() {
            self.loading = false;
            alert('댓글을 불러오는 중 오류가 발생했습니다.');
        });
    },

    renderComments: function(html) {
        var container = document.getElementById('bo_vc');
        if (!container) return;

        // 페이징 nav 보존
        var pagingNav = container.querySelector('.uxc-cmt-paging');
        var emptyMsg = container.querySelector('#bo_vc_empty');

        // 기존 댓글(article) 및 빈 메시지 제거
        var articles = container.querySelectorAll('article.media');
        for (var i = 0; i < articles.length; i++) {
            articles[i].remove();
        }
        if (emptyMsg) emptyMsg.remove();

        // 새 댓글 HTML 삽입 (페이징 nav 앞에)
        if (pagingNav) {
            pagingNav.insertAdjacentHTML('beforebegin', html);
        } else {
            container.insertAdjacentHTML('beforeend', html);
        }

        // 댓글 폼 원위치
        if (typeof comment_box === 'function') {
            comment_box('', 'c');
        }

        // 댓글 이미지 라이트박스 재초기화
        if (typeof uxcLightbox !== 'undefined') uxcLightbox.initComment();
    },

    renderPaging: function() {
        var wrap = document.getElementById('uxc_cmt_pages');
        if (!wrap) return;

        var pg = wrap.querySelector('.pg');
        if (!pg) { pg = document.createElement('div'); pg.className = 'pg'; wrap.appendChild(pg); }

        var html = '';
        var cur = this.currentPage;
        var total = this.totalPages;

        // 처음/이전
        if (cur > 1) {
            html += '<a href="#" onclick="uxcCmt.load(1); return false;" class="pg_page pg_start">처음</a>';
            html += '<a href="#" onclick="uxcCmt.load(' + (cur - 1) + '); return false;" class="pg_page pg_prev">이전</a>';
        }

        // 페이지 번호 (최대 5개)
        var startPage = Math.max(1, cur - 2);
        var endPage = Math.min(total, startPage + 4);
        if (endPage - startPage < 4) {
            startPage = Math.max(1, endPage - 4);
        }

        for (var p = startPage; p <= endPage; p++) {
            if (p === cur) {
                html += '<strong class="pg_current">' + p + '</strong>';
            } else {
                html += '<a href="#" onclick="uxcCmt.load(' + p + '); return false;" class="pg_page">' + p + '</a>';
            }
        }

        // 다음/마지막
        if (cur < total) {
            html += '<a href="#" onclick="uxcCmt.load(' + (cur + 1) + '); return false;" class="pg_page pg_next">다음</a>';
            html += '<a href="#" onclick="uxcCmt.load(' + total + '); return false;" class="pg_page pg_end">맨끝</a>';
        }

        pg.innerHTML = html;

        // 댓글 수 갱신
        var countEl = wrap.closest('.uxc-cmt-paging');
        if (countEl) {
            var cnt = countEl.querySelector('.uxc-cmt-paging__count');
            if (cnt) cnt.textContent = this.total;
        }
    },

    scrollToComments: function() {
        var el = document.getElementById('bo_vc');
        if (el) {
            var top = el.getBoundingClientRect().top + window.pageYOffset - 80;
            window.scrollTo({top: top, behavior: 'smooth'});
        }
    },

    sync: function(operation) {
        var self = this;
        fetch(this.endpoint, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=check_new&bo_table=' + encodeURIComponent(this.boTable)
                + '&wr_id=' + this.wrId
                + '&known_count=0'
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                self.total = data.data.total;
                self.totalPages = Math.max(1, Math.ceil(self.total / self.perPage));
                if (operation === 'delete') {
                    if (self.currentPage > self.totalPages) {
                        self.currentPage = self.totalPages;
                    }
                } else {
                    self.currentPage = self.totalPages;
                }
                self.renderPaging();
            }
        });
    }
};

// 초기화
$(function() {
    uxcCmt.init();
});

// 기존 AJAX 댓글 작성/삭제 후 페이징 동기화
$(document).ajaxComplete(function(event, xhr, settings) {
    if (typeof uxcCmt === 'undefined') return;
    var url = settings.url || '';
    // 댓글 작성(write_comment_update) 또는 삭제(comment_delete) 완료 시
    if (url.indexOf('write_comment_update') !== -1) {
        setTimeout(function() { uxcCmt.sync('write'); }, 500);
    } else if (url.indexOf('comment_delete') !== -1) {
        setTimeout(function() { uxcCmt.sync('delete'); }, 500);
    }
});
</script>
<?php } ?>