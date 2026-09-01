<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

?>

</main>
</div>

<!-- Footer -->
<footer class="footerWrap" data-layout="footer">
  <div class="footerBarWrap">
    <div class="footerBar resWidth">
      <div class="link">
        <a href="<?php echo get_pretty_url('content', 'company'); ?>">GNU AI</a>
        <a href="<?php echo get_pretty_url('content', 'privacy'); ?>">개인정보처리방침</a>
        <a href="<?php echo get_pretty_url('content', 'provision'); ?>">서비스이용약관</a>
      </div>
      <div class="link">
        <a href="<?php echo G5_BBS_URL ?>/current_connect.php"><i class='bx bx-user-pin'></i>접속자</a>
        <a href="<?php echo G5_BBS_URL ?>/new.php"><i class='bx bx-file'></i> 최신글</a>
        <a href="<?php echo G5_BBS_URL ?>/qalist.php"><i class='bx bx-message-edit'></i> 1:1문의</a>
        <a href="<?php echo G5_BBS_URL ?>/faq.php"><i class='bx bx-help-circle'></i> FAQ</a>
      </div>
    </div>
  </div>
  <div class="resWidth">

    <div class="footerContent" data-section="footerSection">
      <div class="footerBrand">
        <div class="logo"><i class='bx <?php echo $theme_logo_icon; ?>' data-item="logoIcon"></i> <strong
            data-item="logoTxt"><?php echo $theme_logo_text; ?></strong></div>
        <div class="footerCopyright">
          <p>Copyright © 2026 <?php echo $theme_logo_text; ?>. All rights reserved</p>
        </div>
      </div>

      <div class="footerSectionWrap">
        <div class="themeVersion">
          <span class="versionLabel">테마 버전</span>
          <span class="versionNumber">v<?php echo $theme_config['theme_version']; ?></span>
        </div>
        <div class="themeVersion">
          <span class="versionLabel">Theme Make by</span>
          <span class="versionNumber"><a href="https://uxcamp.net/" class="color-prime"
              target="_blank">UXCAMP</a></span>
        </div>
      </div>

    </div>

  </div>
</footer>
</div>


<!-- 맨 위로 가기 버튼 -->
<button type="button" id="scrollToTop" class="scrollToTop" aria-label="맨 위로 이동" title="맨 위로 이동">
  <i class="bx bx-chevron-up"></i>
</button>

<script>
// 맨 위로 가기 버튼 기능
$(window).on('scroll', function() {
  if ($(this).scrollTop() > 300) {
    $('#scrollToTop').addClass('visible');
  } else {
    $('#scrollToTop').removeClass('visible');
  }
});

$('#scrollToTop').on('click', function() {
  $('html, body').animate({
    scrollTop: 0
  }, 500);
});
</script>

<?php
if ($config['cf_analytics']) {
    echo $config['cf_analytics'];
}
?>
<!-- } 하단 끝 -->

<script>
$(function() {
  // 폰트 리사이즈 쿠키있으면 실행
  font_resize("container", get_cookie("ck_font_resize_rmv_class"), get_cookie("ck_font_resize_add_class"));
});
</script>

<?php
// UI 모듈 JavaScript 로드
include_once(G5_THEME_PATH.'/include/tail_theme_option.php');
?>

<?php
// member 변수가 존재하는지 확인 (대시보드 페이지 오류 방지)
global $member;
if (!isset($member) || !is_array($member)) {
    $member = array('mb_id' => '');
}

include_once(G5_THEME_PATH."/tail.sub.php");
