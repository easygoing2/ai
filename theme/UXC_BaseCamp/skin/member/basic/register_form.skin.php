<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/style.css">', 0);
add_javascript('<script src="'.G5_JS_URL.'/jquery.register_form.js"></script>', 0);
if ($config['cf_cert_use'] && ($config['cf_cert_simple'] || $config['cf_cert_ipin'] || $config['cf_cert_hp']))
    add_javascript('<script src="'.G5_JS_URL.'/certify.js?v='.G5_JS_VER.'"></script>', 0);

// 다음 주소 API 추가
if ($config['cf_use_addr'])
    add_javascript('<script src="//t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>', 0);
?>

<!-- 회원정보 입력/수정 시작 { -->
<div class="memberFormWrap">
    <div class="memberFormHeader">
        <h1 class="memberFormTitle">
            <i class='bx bx-user-<?php echo $w==''?'plus':'check'; ?>'></i>
            <?php echo $w==''?'회원가입':'회원정보 수정'; ?>
        </h1>
        <p class="memberFormDesc">
            <?php echo $w==''?'회원가입을 위해 아래 정보를 입력해 주세요.':'회원정보를 수정하실 수 있습니다.'; ?>
        </p>
    </div>

    <form id="fregisterform" name="fregisterform" action="<?php echo $register_action_url ?>" onsubmit="return fregisterform_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
    <input type="hidden" name="w" value="<?php echo $w ?>">
    <input type="hidden" name="url" value="<?php echo $urlencode ?>">
    <input type="hidden" name="agree" value="<?php echo $agree ?>">
    <input type="hidden" name="agree2" value="<?php echo $agree2 ?>">
    <input type="hidden" name="cert_type" value="<?php echo $member['mb_certify']; ?>">
    <input type="hidden" name="cert_no" value="">
    <?php if (isset($member['mb_sex'])) {  ?><input type="hidden" name="mb_sex" value="<?php echo $member['mb_sex'] ?>"><?php }  ?>
    <?php if (isset($member['mb_nick_date']) && $member['mb_nick_date'] > date("Y-m-d", G5_SERVER_TIME - ($config['cf_nick_modify'] * 86400))) { // 닉네임수정일이 지나지 않았다면  ?>
    <input type="hidden" name="mb_nick_default" value="<?php echo get_text($member['mb_nick']) ?>">
    <input type="hidden" name="mb_nick" value="<?php echo get_text($member['mb_nick']) ?>">
    <?php }  ?>
    
    <div class="memberFormContainer">
        <!-- 사이트 이용정보 -->
        <div class="formSection">
            <div class="sectionHeader">
                <h2 class="sectionTitle">
                    <i class='bx bx-lock-alt'></i>
                    사이트 이용정보 입력
                </h2>
            </div>
            <div class="sectionContent">
                <div class="formGroup">
                    <label for="reg_mb_id" class="formLabel">
                        아이디
                        <button type="button" class="tooltipIcon"><i class='bx bx-help-circle'></i></button>
                        <span class="tooltip">영문자, 숫자, _ 만 입력 가능. 최소 3자이상 입력하세요.</span>
                    </label>
                    <input type="text" name="mb_id" value="<?php echo $member['mb_id'] ?>" id="reg_mb_id" <?php echo $required ?> <?php echo $readonly ?> class="frm_input <?php echo $required ?> <?php echo $readonly ?>" minlength="3" maxlength="20" placeholder="아이디를 입력하세요">
                    <span id="msg_mb_id" class="formMessage"></span>
                </div>

                <div class="formRow">
                    <div class="formGroup half">
                        <label for="reg_mb_password" class="formLabel">비밀번호</label>
                        <input type="password" name="mb_password" id="reg_mb_password" <?php echo $required ?> class="frm_input <?php echo $required ?>" minlength="3" maxlength="20" placeholder="비밀번호를 입력하세요">
                    </div>
                    <div class="formGroup half">
                        <label for="reg_mb_password_re" class="formLabel">비밀번호 확인</label>
                        <input type="password" name="mb_password_re" id="reg_mb_password_re" <?php echo $required ?> class="frm_input <?php echo $required ?>" minlength="3" maxlength="20" placeholder="비밀번호를 다시 입력하세요">
                    </div>
                </div>
            </div>
        </div>

        <!-- 개인정보 입력 -->
        <div class="formSection">
            <div class="sectionHeader">
                <h2 class="sectionTitle">
                    <i class='bx bx-user'></i>
                    개인정보 입력
                </h2>
            </div>
            <div class="sectionContent">
                <?php if ($config['cf_cert_use']) { ?>
                <div class="certSection">
                    <div class="certButtons">
                        <?php 
                        if ($config['cf_cert_simple']) {
                            echo '<button type="button" id="win_sa_kakao_cert" class="button shadowline-de round-s sBtn bxicon" data-type=""><i class=\'bx bx-check-shield\'></i>간편인증</button>'.PHP_EOL;
                        }
                        if ($config['cf_cert_hp'])
                            echo '<button type="button" id="win_hp_cert" class="button shadowline-de round-s sBtn bxicon"><i class=\'bx bx-mobile-alt\'></i>휴대폰 본인확인</button>'.PHP_EOL;
                        if ($config['cf_cert_ipin'])
                            echo '<button type="button" id="win_ipin_cert" class="button shadowline-de round-s sBtn bxicon"><i class=\'bx bx-id-card\'></i>아이핀 본인확인</button>'.PHP_EOL;
                        ?>
                    </div>
                    <span class="certReq">(필수) 본인확인을 진행해 주세요.</span>
                    <noscript>본인확인을 위해서는 자바스크립트 사용이 가능해야합니다.</noscript>
                </div>
                <?php } ?>

                <?php if ($config['cf_cert_use'] && $member['mb_certify']) { ?>
                <div class="certComplete">
                    <i class='bx bx-check-circle'></i>
                    <strong><?php 
                        switch ($member['mb_certify']) {
                            case "simple": echo "간편인증"; break;
                            case "ipin": echo "아이핀"; break;
                            case "hp": echo "휴대폰"; break;
                        }
                    ?> 본인확인</strong>
                    <?php if ($member['mb_adult']) { ?> 및 <strong>성인인증</strong><?php } ?> 완료
                </div>
                <?php } ?>

                <div class="formGroup">
                    <label for="reg_mb_name" class="formLabel">
                        이름
                        <?php if ($config['cf_cert_use']) { ?>
                        <span class="labelDesc">본인확인 시 자동입력</span>
                        <?php } ?>
                    </label>
                    <input type="text" id="reg_mb_name" name="mb_name" value="<?php echo get_text($member['mb_name']) ?>" <?php echo $required ?> <?php echo $readonly; ?> class="frm_input <?php echo $required ?> <?php echo $name_readonly ?>" placeholder="이름을 입력하세요">
                </div>

                <?php if ($req_nick) { ?>
                <div class="formGroup">
                    <label for="reg_mb_nick" class="formLabel">
                        닉네임
                        <button type="button" class="tooltipIcon"><i class='bx bx-help-circle'></i></button>
                        <span class="tooltip">공백없이 한글,영문,숫자만 입력 가능 (한글2자, 영문4자 이상)<br> 닉네임을 바꾸시면 앞으로 <?php echo (int)$config['cf_nick_modify'] ?>일 이내에는 변경 할 수 없습니다.</span>
                    </label>
                    <input type="hidden" name="mb_nick_default" value="<?php echo isset($member['mb_nick'])?get_text($member['mb_nick']):''; ?>">
                    <input type="text" name="mb_nick" value="<?php echo isset($member['mb_nick'])?get_text($member['mb_nick']):''; ?>" id="reg_mb_nick" required class="frm_input required" maxlength="20" placeholder="닉네임을 입력하세요">
                    <span id="msg_mb_nick" class="formMessage"></span>
                </div>
                <?php } ?>

                <div class="formGroup">
                    <label for="reg_mb_email" class="formLabel">
                        E-mail
                        <?php if ($config['cf_use_email_certify']) { ?>
                        <button type="button" class="tooltipIcon"><i class='bx bx-help-circle'></i></button>
                        <span class="tooltip">
                            <?php if ($w=='') { echo "E-mail 로 발송된 내용을 확인한 후 인증하셔야 회원가입이 완료됩니다."; } ?>
                            <?php if ($w=='u') { echo "E-mail 주소를 변경하시면 다시 인증하셔야 합니다."; } ?>
                        </span>
                        <?php } ?>
                    </label>
                    <input type="hidden" name="old_email" value="<?php echo $member['mb_email'] ?>">
                    <input type="text" name="mb_email" value="<?php echo isset($member['mb_email'])?$member['mb_email']:''; ?>" id="reg_mb_email" required class="frm_input required" maxlength="100" placeholder="이메일 주소를 입력하세요">
                </div>

                <?php if ($config['cf_use_homepage']) { ?>
                <div class="formGroup">
                    <label for="reg_mb_homepage" class="formLabel <?php echo $config['cf_req_homepage']?'required':''; ?>">홈페이지</label>
                    <input type="text" name="mb_homepage" value="<?php echo get_text($member['mb_homepage']) ?>" id="reg_mb_homepage" <?php echo $config['cf_req_homepage']?"required":""; ?> class="frm_input <?php echo $config['cf_req_homepage']?"required":""; ?>" maxlength="255" placeholder="https://example.com">
                </div>
                <?php } ?>

                <div class="formRow">
                    <?php if ($config['cf_use_tel']) { ?>
                    <div class="formGroup half">
                        <label for="reg_mb_tel" class="formLabel <?php echo $config['cf_req_tel']?'required':''; ?>">전화번호</label>
                        <input type="text" name="mb_tel" value="<?php echo get_text($member['mb_tel']) ?>" id="reg_mb_tel" <?php echo $config['cf_req_tel']?"required":""; ?> class="frm_input <?php echo $config['cf_req_tel']?"required":""; ?>" maxlength="20" placeholder="전화번호">
                    </div>
                    <?php } ?>

                    <?php if ($config['cf_use_hp'] || ($config["cf_cert_use"] && ($config['cf_cert_hp'] || $config['cf_cert_simple']))) { ?>
                    <div class="formGroup <?php echo $config['cf_use_tel']?'half':''; ?>">
                        <label for="reg_mb_hp" class="formLabel <?php echo $hp_required?'required':''; ?>">
                            휴대폰번호
                            <?php if ($config['cf_cert_use']) { ?>
                            <span class="labelDesc">본인확인 시 자동입력</span>
                            <?php } ?>
                        </label>
                        <input type="text" name="mb_hp" value="<?php echo get_text($member['mb_hp']) ?>" id="reg_mb_hp" <?php echo $hp_required; ?> <?php echo $hp_readonly; ?> class="frm_input <?php echo $hp_required; ?> <?php echo $hp_readonly; ?>" maxlength="20" placeholder="휴대폰번호">
                        <?php if ($config['cf_cert_use'] && ($config['cf_cert_hp'] || $config['cf_cert_simple'])) { ?>
                        <input type="hidden" name="old_mb_hp" value="<?php echo get_text($member['mb_hp']) ?>">
                        <?php } ?>
                    </div>
                    <?php } ?>
                </div>

                <?php if ($config['cf_use_addr']) { ?>
                <div class="formGroup">
                    <label class="formLabel <?php echo $config['cf_req_addr']?'required':''; ?>">주소</label>
                    <div class="addressWrap">
                        <div class="addressRow">
                            <input type="text" name="mb_zip" value="<?php echo $member['mb_zip1'].$member['mb_zip2']; ?>" id="reg_mb_zip" <?php echo $config['cf_req_addr']?"required":""; ?> class="frm_input zipcode <?php echo $config['cf_req_addr']?"required":""; ?>" maxlength="6" placeholder="우편번호" readonly>
                            <button type="button" class="button shadowline-de round-s mBtn bxicon" onclick="if(typeof win_zip !== 'function') { alert('win_zip 함수가 정의되지 않았습니다.'); } else { win_zip('fregisterform', 'mb_zip', 'mb_addr1', 'mb_addr2', 'mb_addr3', 'mb_addr_jibeon'); }">
                                <i class='bx bx-search'></i>주소 검색
                            </button>
                        </div>
                        <input type="text" name="mb_addr1" value="<?php echo get_text($member['mb_addr1']) ?>" id="reg_mb_addr1" <?php echo $config['cf_req_addr']?"required":""; ?> class="frm_input <?php echo $config['cf_req_addr']?"required":""; ?>" placeholder="기본주소" readonly>
                        <input type="text" name="mb_addr2" value="<?php echo get_text($member['mb_addr2']) ?>" id="reg_mb_addr2" class="frm_input" placeholder="상세주소">
                        <input type="text" name="mb_addr3" value="<?php echo get_text($member['mb_addr3']) ?>" id="reg_mb_addr3" class="frm_input" readonly placeholder="참고항목">
                        <input type="hidden" name="mb_addr_jibeon" value="<?php echo get_text($member['mb_addr_jibeon']); ?>">
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>

        <!-- 기타 개인설정 -->
        <div class="formSection">
            <div class="sectionHeader">
                <h2 class="sectionTitle">
                    <i class='bx bx-cog'></i>
                    기타 개인설정
                </h2>
            </div>
            <div class="sectionContent">
                <?php if ($config['cf_use_signature']) { ?>
                <div class="formGroup">
                    <label for="reg_mb_signature" class="formLabel <?php echo $config['cf_req_signature']?'required':''; ?>">서명</label>
                    <textarea name="mb_signature" id="reg_mb_signature" <?php echo $config['cf_req_signature']?"required":""; ?> class="frm_input <?php echo $config['cf_req_signature']?"required":""; ?>" placeholder="서명을 입력하세요"><?php echo $member['mb_signature'] ?></textarea>
                </div>
                <?php } ?>

                <?php if ($config['cf_use_profile']) { ?>
                <div class="formGroup">
                    <label for="reg_mb_profile" class="formLabel <?php echo $config['cf_req_profile']?'required':''; ?>">자기소개</label>
                    <textarea name="mb_profile" id="reg_mb_profile" <?php echo $config['cf_req_profile']?"required":""; ?> class="frm_input <?php echo $config['cf_req_profile']?"required":""; ?>" placeholder="자기소개를 입력하세요"><?php echo $member['mb_profile'] ?></textarea>
                </div>
                <?php } ?>

                <?php if ($config['cf_use_member_icon'] && $member['mb_level'] >= $config['cf_icon_level']) { ?>
                <div class="formGroup">
                    <label for="reg_mb_icon" class="formLabel">
                        회원아이콘
                        <button type="button" class="tooltipIcon"><i class='bx bx-help-circle'></i></button>
                        <span class="tooltip">이미지 크기는 가로 <?php echo $config['cf_member_icon_width'] ?>픽셀, 세로 <?php echo $config['cf_member_icon_height'] ?>픽셀 이하로 해주세요.<br>
                        gif, jpg, png파일만 가능하며 용량 <?php echo number_format($config['cf_member_icon_size']) ?>바이트 이하만 등록됩니다.</span>
                    </label>
                    <div class="fileUpload">
                        <input type="file" name="mb_icon" id="reg_mb_icon" class="fileInput">
                        <?php if ($w == 'u' && file_exists($mb_icon_path)) { ?>
                        <div class="filePreview">
                            <img src="<?php echo $mb_icon_url ?>" alt="회원아이콘">
                            <label class="fileDelete">
                                <input type="checkbox" name="del_mb_icon" value="1" id="del_mb_icon">
                                <span>삭제</span>
                            </label>
                        </div>
                        <?php } ?>
                    </div>
                </div>
                <?php } ?>

                <?php if ($member['mb_level'] >= $config['cf_icon_level'] && $config['cf_member_img_size'] && $config['cf_member_img_width'] && $config['cf_member_img_height']) { ?>
                <div class="formGroup">
                    <label for="reg_mb_img" class="formLabel">
                        회원이미지
                        <button type="button" class="tooltipIcon"><i class='bx bx-help-circle'></i></button>
                        <span class="tooltip">이미지 크기는 가로 <?php echo $config['cf_member_img_width'] ?>픽셀, 세로 <?php echo $config['cf_member_img_height'] ?>픽셀 이하로 해주세요.<br>
                        gif, jpg, png파일만 가능하며 용량 <?php echo number_format($config['cf_member_img_size']) ?>바이트 이하만 등록됩니다.</span>
                    </label>
                    <div class="fileUpload">
                        <input type="file" name="mb_img" id="reg_mb_img" class="fileInput">
                        <?php if ($w == 'u' && file_exists($mb_img_path)) { ?>
                        <div class="filePreview">
                            <img src="<?php echo $mb_img_url ?>" alt="회원이미지">
                            <label class="fileDelete">
                                <input type="checkbox" name="del_mb_img" value="1" id="del_mb_img">
                                <span>삭제</span>
                            </label>
                        </div>
                        <?php } ?>
                    </div>
                </div>
                <?php } ?>

                <div class="optSection">
                    <div class="opt">
                        <input type="checkbox" name="mb_mailling" value="1" id="reg_mb_mailling" <?php echo ($w=='' || $member['mb_mailling'])?'checked':''; ?>>
                        <label for="reg_mb_mailling">
                            <span class="sr-only">메일링서비스</span>
                        </label>
                        <span class="optText">정보 메일을 받겠습니다.</span>
                    </div>

                    <?php if ($config['cf_use_hp']) { ?>
                    <div class="opt">
                        <input type="checkbox" name="mb_sms" value="1" id="reg_mb_sms" <?php echo ($w=='' || $member['mb_sms'])?'checked':''; ?>>
                        <label for="reg_mb_sms">
                            <span class="sr-only">SMS 수신여부</span>
                        </label>
                        <span class="optText">휴대폰 문자메세지를 받겠습니다.</span>
                    </div>
                    <?php } ?>

                    <?php if (isset($member['mb_open_date']) && $member['mb_open_date'] <= date("Y-m-d", G5_SERVER_TIME - ($config['cf_open_modify'] * 86400)) || empty($member['mb_open_date'])) { ?>
                    <div class="opt">
                        <input type="checkbox" name="mb_open" value="1" id="reg_mb_open" <?php echo ($w=='' || $member['mb_open'])?'checked':''; ?>>
                        <label for="reg_mb_open">
                            <span class="sr-only">정보공개</span>
                        </label>
                        <span class="optText">
                            다른분들이 나의 정보를 볼 수 있도록 합니다.
                            <button type="button" class="tooltipIcon"><i class='bx bx-help-circle'></i></button>
                            <span class="tooltip">정보공개를 바꾸시면 앞으로 <?php echo (int)$config['cf_open_modify'] ?>일 이내에는 변경이 안됩니다.</span>
                        </span>
                        <input type="hidden" name="mb_open_default" value="<?php echo $member['mb_open'] ?>">
                    </div>
                    <?php } else { ?>
                    <div class="optInfo">
                        <i class='bx bx-info-circle'></i>
                        정보공개는 수정후 <?php echo (int)$config['cf_open_modify'] ?>일 이내, <?php echo date("Y년 m월 j일", isset($member['mb_open_date']) ? strtotime("{$member['mb_open_date']} 00:00:00")+$config['cf_open_modify']*86400:G5_SERVER_TIME+$config['cf_open_modify']*86400); ?> 까지는 변경이 안됩니다.
                        <input type="hidden" name="mb_open" value="<?php echo $member['mb_open'] ?>">
                    </div>
                    <?php } ?>
                </div>

                <?php
                //회원정보 수정인 경우 소셜 계정 출력
                if( $w == 'u' && function_exists('social_member_provider_manage') ){
                    social_member_provider_manage();
                }
                ?>
                
                <?php if ($w == "" && $config['cf_use_recommend']) { ?>
                <div class="formGroup">
                    <label for="reg_mb_recommend" class="formLabel">추천인아이디</label>
                    <input type="text" name="mb_recommend" id="reg_mb_recommend" class="frm_input" placeholder="추천인 아이디를 입력하세요">
                </div>
                <?php } ?>

                <div class="captchaSection">
                    <label class="formLabel">자동등록방지</label>
                    <?php echo captcha_html(); ?>
                </div>
            </div>
        </div>
    </div>

    <div class="formButtons">
        <button type="submit" id="btn_submit" class="button bg-pr color-wh round-s mBtn bxicon" accesskey="s">
            <i class='bx bx-check'></i><?php echo $w==''?'회원가입':'정보수정'; ?>
        </button>
        <a href="<?php echo G5_URL ?>" class="button shadowline-de round-s mBtn bxicon">
            <i class='bx bx-x'></i>취소
        </a>
    </div>
    </form>
</div>

<script>
$(function() {
    $("#reg_zip_find").css("display", "inline-block");
    var pageTypeParam = "pageType=register";

    <?php if($config['cf_cert_use'] && $config['cf_cert_simple']) { ?>
    // 이니시스 간편인증
    var url = "<?php echo G5_INICERT_URL; ?>/ini_request.php";
    var type = "";    
    var params = "";
    var request_url = "";

    $(".win_sa_cert").click(function() {
        if(!cert_confirm()) return false;
        type = $(this).data("type");
        params = "?directAgency=" + type + "&" + pageTypeParam;
        request_url = url + params;
        call_sa(request_url);
    });
    <?php } ?>
    <?php if($config['cf_cert_use'] && $config['cf_cert_ipin']) { ?>
    // 아이핀인증
    var params = "";
    $("#win_ipin_cert").click(function() {
        if(!cert_confirm()) return false;
        params = "?" + pageTypeParam;
        var url = "<?php echo G5_OKNAME_URL; ?>/ipin1.php"+params;
        certify_win_open('kcb-ipin', url);
        return;
    });

    <?php } ?>
    <?php if($config['cf_cert_use'] && $config['cf_cert_hp']) { ?>
    // 휴대폰인증
    var params = "";
    $("#win_hp_cert").click(function() {
        if(!cert_confirm()) return false;
        params = "?" + pageTypeParam;
        <?php     
        switch($config['cf_cert_hp']) {
            case 'kcb':                
                $cert_url = G5_OKNAME_URL.'/hpcert1.php';
                $cert_type = 'kcb-hp';
                break;
            case 'kcp':
                $cert_url = G5_KCPCERT_URL.'/kcpcert_form.php';
                $cert_type = 'kcp-hp';
                break;
            case 'lg':
                $cert_url = G5_LGXPAY_URL.'/AuthOnlyReq.php';
                $cert_type = 'lg-hp';
                break;
            default:
                echo 'alert("기본환경설정에서 휴대폰 본인확인 설정을 해주십시오");';
                echo 'return false;';
                break;
        }
        ?>
        
        certify_win_open("<?php echo $cert_type; ?>", "<?php echo $cert_url; ?>"+params);
        return;
    });
    <?php } ?>
});

// submit 최종 폼체크
function fregisterform_submit(f)
{
    // 회원아이디 검사
    if (f.w.value == "") {
        var msg = reg_mb_id_check();
        if (msg) {
            alert(msg);
            f.mb_id.select();
            return false;
        }
    }

    if (f.w.value == "") {
        if (f.mb_password.value.length < 3) {
            alert("비밀번호를 3글자 이상 입력하십시오.");
            f.mb_password.focus();
            return false;
        }
    }

    if (f.mb_password.value != f.mb_password_re.value) {
        alert("비밀번호가 같지 않습니다.");
        f.mb_password_re.focus();
        return false;
    }

    if (f.mb_password.value.length > 0) {
        if (f.mb_password_re.value.length < 3) {
            alert("비밀번호를 3글자 이상 입력하십시오.");
            f.mb_password_re.focus();
            return false;
        }
    }

    // 이름 검사
    if (f.w.value=="") {
        if (f.mb_name.value.length < 1) {
            alert("이름을 입력하십시오.");
            f.mb_name.focus();
            return false;
        }
    }

    <?php if($w == '' && $config['cf_cert_use'] && $config['cf_cert_req']) { ?>
    // 본인확인 체크
    if(f.cert_no.value=="") {
        alert("회원가입을 위해서는 본인확인을 해주셔야 합니다.");
        return false;
    }
    <?php } ?>

    // 닉네임 검사
    if ((f.w.value == "") || (f.w.value == "u" && f.mb_nick.defaultValue != f.mb_nick.value)) {
        var msg = reg_mb_nick_check();
        if (msg) {
            alert(msg);
            f.reg_mb_nick.select();
            return false;
        }
    }

    // E-mail 검사
    if ((f.w.value == "") || (f.w.value == "u" && f.mb_email.defaultValue != f.mb_email.value)) {
        var msg = reg_mb_email_check();
        if (msg) {
            alert(msg);
            f.reg_mb_email.select();
            return false;
        }
    }

    <?php if (($config['cf_use_hp'] || $config['cf_cert_hp']) && $config['cf_req_hp']) {  ?>
    // 휴대폰번호 체크
    var msg = reg_mb_hp_check();
    if (msg) {
        alert(msg);
        f.reg_mb_hp.select();
        return false;
    }
    <?php } ?>

    if (typeof f.mb_icon != "undefined") {
        if (f.mb_icon.value) {
            if (!f.mb_icon.value.toLowerCase().match(/.(gif|jpe?g|png)$/i)) {
                alert("회원아이콘이 이미지 파일이 아닙니다.");
                f.mb_icon.focus();
                return false;
            }
        }
    }

    if (typeof f.mb_img != "undefined") {
        if (f.mb_img.value) {
            if (!f.mb_img.value.toLowerCase().match(/.(gif|jpe?g|png)$/i)) {
                alert("회원이미지가 이미지 파일이 아닙니다.");
                f.mb_img.focus();
                return false;
            }
        }
    }

    if (typeof(f.mb_recommend) != "undefined" && f.mb_recommend.value) {
        if (f.mb_id.value == f.mb_recommend.value) {
            alert("본인을 추천할 수 없습니다.");
            f.mb_recommend.focus();
            return false;
        }

        var msg = reg_mb_recommend_check();
        if (msg) {
            alert(msg);
            f.mb_recommend.select();
            return false;
        }
    }

    <?php echo chk_captcha_js();  ?>

    document.getElementById("btn_submit").disabled = "disabled";

    return true;
}

jQuery(function($){
    //tooltip
    $(document).on("click", ".tooltipIcon", function(e){
        $(this).next(".tooltip").fadeIn(400).css("display","inline-block");
    }).on("mouseout", ".tooltipIcon", function(e){
        $(this).next(".tooltip").fadeOut();
    });
});
</script>

<!-- } 회원정보 입력/수정 끝 -->