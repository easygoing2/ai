<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/style.css">', 0);

if($config['cf_cert_use'] && ($config['cf_cert_simple'] || $config['cf_cert_ipin'] || $config['cf_cert_hp'])) { ?>
    <script src="<?php echo G5_JS_URL ?>/certify.js?v=<?php echo G5_JS_VER; ?>"></script>    
<?php } ?>

<!-- 회원정보 찾기 시작 { -->
<div class="passwordLostWrap">
    <div class="passwordLostBox cardBox bg-wh padding-xl round-x">
        <div class="passwordLostHeader">
            <h1 class="passwordLostTitle">
                <i class='bx bx-search-alt'></i>
                회원정보 찾기
            </h1>
            <p class="passwordLostDesc">
                가입 시 등록한 정보로 아이디와 비밀번호를 찾을 수 있습니다.
            </p>
        </div>

        <div class="passwordLostContent">
            <!-- 이메일로 찾기 -->
            <div class="findMethodBox">
                <form name="fpasswordlost" action="<?php echo $action_url ?>" onsubmit="return fpasswordlost_submit(this);" method="post" autocomplete="off">
                    <div class="methodHeader">
                        <h3 class="methodTitle">
                            <i class='bx bx-envelope'></i>
                            이메일로 찾기
                        </h3>
                    </div>
                    
                    <div class="methodContent">
                        <div class="methodInfo">
                            <p>회원가입 시 등록하신 이메일 주소를 입력해 주세요.</p>
                            <p>해당 이메일로 아이디와 비밀번호 정보를 보내드립니다.</p>
                        </div>
                        
                        <fieldset id="info_fs">
                            <div class="inputBox">
                                <label for="mb_email" class="sr-only">E-mail 주소<strong class="sr-only"> 필수</strong></label>
                                <div class="inputIcon">
                                    <i class='bx bx-envelope'></i>
                                    <input type="text" name="mb_email" id="mb_email" required class="required frm_input" size="30" placeholder="이메일 주소를 입력하세요">
                                </div>
                            </div>
                        </fieldset>
                        
                        <?php echo captcha_html();  ?>
                        
                        <div class="buttonBox">
                            <button type="submit" class="button bg-pr color-wh round-m mBtn fw">
                                <i class='bx bx-send'></i>
                                인증메일 발송
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <?php if($config['cf_cert_use'] != 0 && $config['cf_cert_find'] != 0) { ?> 
            <!-- 본인인증으로 찾기 -->
            <div class="findMethodBox">
                <div class="methodHeader">
                    <h3 class="methodTitle">
                        <i class='bx bx-user-check'></i>
                        본인인증으로 찾기
                    </h3>
                </div>
                
                <div class="methodContent">
                    <div class="methodInfo">
                        <p>본인인증을 통해 회원정보를 찾을 수 있습니다.</p>
                        <p>인증 완료 후 회원정보가 자동으로 입력됩니다.</p>
                    </div>
                    
                    <div class="certButtonGrid">
                        <?php if(!empty($config['cf_cert_simple'])) { ?>
                        <button type="button" id="win_sa_kakao_cert" class="button shadowline-pr round-m mBtn fw win_sa_cert" data-type="">
                            <i class='bx bx-message-square'></i>
                            간편인증
                        </button>
                        <?php } ?>
                        
                        <?php if(!empty($config['cf_cert_hp'])) { ?>
                        <button type="button" id="win_hp_cert" class="button shadowline-pr round-m mBtn fw">
                            <i class='bx bx-mobile'></i>
                            휴대폰 본인확인
                        </button>
                        <?php } ?>
                        
                        <?php if(!empty($config['cf_cert_ipin'])) { ?>
                        <button type="button" id="win_ipin_cert" class="button shadowline-pr round-m mBtn fw">
                            <i class='bx bx-id-card'></i>
                            아이핀 본인확인
                        </button>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>

        <!-- 하단 안내 -->
        <div class="passwordLostFooter">
            <div class="footerLinks">
                <a href="<?php echo G5_BBS_URL ?>/login.php" class="footerLink">
                    <i class='bx bx-log-in'></i>
                    로그인
                </a>
                <span class="divider">|</span>
                <a href="<?php echo G5_BBS_URL ?>/register.php" class="footerLink">
                    <i class='bx bx-user-plus'></i>
                    회원가입
                </a>
            </div>
            
            <div class="helpInfo">
                <p><i class='bx bx-info-circle'></i> 회원정보 찾기에 어려움이 있으신가요?</p>
                <p>고객센터 <strong><?php echo $config['cf_admin_email']; ?></strong>로 문의해주세요.</p>
            </div>
        </div>
    </div>
</div>
<script>    
$(function() {
    $("#reg_zip_find").css("display", "inline-block");
    var pageTypeParam = "pageType=find";

	<?php if($config['cf_cert_use'] && $config['cf_cert_simple']) { ?>
	// TOSS 간편인증
	var url = "<?php echo G5_INICERT_URL; ?>/ini_request.php";
	var type = "";    
    var params = "";
    var request_url = "";
    
	
	$(".win_sa_cert").click(function() {
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
function fpasswordlost_submit(f)
{
    <?php echo chk_captcha_js();  ?>

    return true;
}
</script>
<!-- } 회원정보 찾기 끝 -->