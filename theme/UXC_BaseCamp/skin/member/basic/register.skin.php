<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/style.css">', 0);
?>

<?php
// 소셜로그인 사용시 소셜로그인 버튼
// @include_once(get_social_skin_path().'/social_register.skin.php');
?>

<!-- 회원가입약관 동의 시작 { -->
<div class="registerWrap">
    <div class="registerHeader">
        <h1 class="registerTitle">
            <i class='bx bx-user-plus'></i>
            회원가입
        </h1>
        <p class="registerDesc">
            <i class='bx bx-info-circle'></i>
            회원가입약관 및 개인정보 수집 및 이용의 내용에 동의하셔야 회원가입 하실 수 있습니다.
        </p>
    </div>

    <form name="fregister" id="fregister" action="<?php echo $register_action_url ?>" onsubmit="return fregister_submit(this);" method="POST" autocomplete="off">
    
    <?php
    // 소셜로그인 사용시 소셜로그인 버튼
    @include_once(get_social_skin_path().'/social_register.skin.php');
    ?>
    
    <div class="agreeBox">
        <!-- 회원가입약관 -->
        <div class="agreeItem">
            <div class="agreeHeader">
                <h2 class="agreeTitle">
                    <i class='bx bx-file-blank'></i>
                    회원가입약관
                </h2>
            </div>
            <div class="agreeContent">
                <textarea><?php echo get_text($config['cf_stipulation']) ?></textarea>
            </div>
            <div class="agreeCheck">
                <div class="opt">
                    <input type="checkbox" name="agree" value="1" id="agree11" class="selec_chk">
                    <label for="agree11">
                        <span class="sr-only">회원가입약관 동의</span>
                    </label>
                    <span class="checkText">회원가입약관의 내용에 동의합니다.</span>
                </div>
            </div>
        </div>

        <!-- 개인정보 수집 및 이용 -->
        <div class="agreeItem">
            <div class="agreeHeader">
                <h2 class="agreeTitle">
                    <i class='bx bx-lock-alt'></i>
                    개인정보 수집 및 이용
                </h2>
            </div>
            <div class="agreeContent">
                <div class="privacyTable">
                    <table>
                        <caption>개인정보 수집 및 이용</caption>
                        <thead>
                        <tr>
                            <th>목적</th>
                            <th>항목</th>
                            <th>보유기간</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>이용자 식별 및 본인여부 확인</td>
                            <td>아이디, 이름, 비밀번호<?php echo ($config['cf_cert_use'])? ", 생년월일, 휴대폰 번호(본인인증 할 때만, 아이핀 제외), 암호화된 개인식별부호(CI)" : ""; ?></td>
                            <td>회원 탈퇴 시까지</td>
                        </tr>
                        <tr>
                            <td>고객서비스 이용에 관한 통지,<br>CS대응을 위한 이용자 식별</td>
                            <td>연락처 (이메일, 휴대전화번호)</td>
                            <td>회원 탈퇴 시까지</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="agreeCheck">
                <div class="opt">
                    <input type="checkbox" name="agree2" value="1" id="agree21" class="selec_chk">
                    <label for="agree21">
                        <span class="sr-only">개인정보 수집 및 이용 동의</span>
                    </label>
                    <span class="checkText">개인정보 수집 및 이용의 내용에 동의합니다.</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 전체 동의 -->
    <div class="agreeAll">
        <div class="opt">
            <input type="checkbox" name="chk_all" id="chk_all" class="selec_chk">
            <label for="chk_all">
                <span class="sr-only">전체 동의</span>
            </label>
            <span class="checkText">
                <strong>회원가입 약관에 모두 동의합니다</strong>
            </span>
        </div>
    </div>
    
    <!-- 버튼 영역 -->
    <div class="buttonArea">
        <button type="submit" class="button bg-pr color-wh round-s mBtn bxicon">
            <i class='bx bx-check'></i>동의하고 계속하기
        </button>
        <a href="<?php echo G5_URL ?>" class="button shadowline-de round-s mBtn bxicon">
            <i class='bx bx-x'></i>취소
        </a>
    </div>

    </form>

    <script>
    function fregister_submit(f)
    {
        if (!f.agree.checked) {
            alert("회원가입약관의 내용에 동의하셔야 회원가입 하실 수 있습니다.");
            f.agree.focus();
            return false;
        }

        if (!f.agree2.checked) {
            alert("개인정보 수집 및 이용의 내용에 동의하셔야 회원가입 하실 수 있습니다.");
            f.agree2.focus();
            return false;
        }

        return true;
    }
    
    jQuery(function($){
        // 모두선택
        $("input[name=chk_all]").click(function() {
            if ($(this).prop('checked')) {
                $("input[name^=agree]").prop('checked', true);
            } else {
                $("input[name^=agree]").prop("checked", false);
            }
        });
    });

    </script>
</div>
<!-- } 회원가입 약관 동의 끝 -->
