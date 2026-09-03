<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

include_once(G5_THEME_PATH.'/head.sub.php');
include_once(G5_LIB_PATH.'/latest.lib.php');
include_once(G5_LIB_PATH.'/outlogin.lib.php');
include_once(G5_LIB_PATH.'/poll.lib.php');
include_once(G5_LIB_PATH.'/visit.lib.php');
include_once(G5_LIB_PATH.'/connect.lib.php');
include_once(G5_LIB_PATH.'/popular.lib.php');

// 테마 옵션 설정 로드
include_once(G5_THEME_PATH.'/include/head_theme_option.php');

?>

<?php
if(defined('_INDEX_')) { // index에서만 실행
    include G5_BBS_PATH.'/newwin.inc.php'; // 팝업레이어
}
?>

<!-- 상단 시작 { -->
<div class="wrap">

  <div class="drawerNav" id="drawerNav">
    <div class="group">
      <div class="siteTitle">
        <a href="<?php echo G5_URL; ?>" aria-label="홈으로 가기" class="logo">
          <h1><i class='bx <?php echo $theme_logo_icon; ?>' data-item="logoIcon"></i> <strong
              data-item="logoTxt"><?php echo $theme_logo_text; ?></strong></h1>
        </a>
      </div>
      <div class="itemGroup">
        <?php echo outlogin('theme/side'); // 외부 로그인 ?>
      </div>
      <div class="itemGroup">
        <?php
                    include G5_THEME_PATH.'/ui_include/ui_sideGnb.php'; // ui_sideGnb
                ?>
      </div>
    </div>
  </div>
  <div id="drawerDim" class="drawerDim"></div>

  <!-- searchPop -->
  <div class="searchPop" data-ui="uxPopSearch">
    <div class="search">
      <h2>SEARCH</h2>
      <fieldset id="hd_sch2">
        <legend>사이트 내 전체검색</legend>
        <form name="fsearchbox" method="get" action="<?php echo G5_BBS_URL ?>/search.php"
          onsubmit="return fsearchbox_submit(this);">
          <input type="hidden" name="sfl" value="wr_subject||wr_content">
          <input type="hidden" name="sop" value="and">
          <label for="sch_stx" class="sr-only">검색어 필수</label>
          <input type="text" name="stx" id="sch_stx" maxlength="20" required="" placeholder="검색어를 입력해주세요">
          <button type="submit" id="sch_submit2" value="검색"><i class='bx bx-up-arrow-alt'></i><span
              class="sr-only">검색</span></button>
        </form>

        <script>
        function fsearchbox_submit(f) {
          if (f.stx.value.length < 2) {
            alert("검색어는 두글자 이상 입력하십시오.");
            f.stx.select();
            f.stx.focus();
            return false;
          }

          // 검색에 많은 부하가 걸리는 경우 이 주석을 제거하세요.
          var cnt = 0;
          for (var i = 0; i < f.stx.value.length; i++) {
            if (f.stx.value.charAt(i) == ' ')
              cnt++;
          }

          if (cnt > 1) {
            alert("빠른 검색을 위하여 검색어에 공백은 한개만 입력할 수 있습니다.");
            f.stx.select();
            f.stx.focus();
            return false;
          }

          return true;
        }
        </script>
      </fieldset>
    </div>

    <div class="dim" data-ui="popHide"></div>
  </div>

  <!-- Header -->
  <header class="header" data-layout="header">
    <!-- tnbWrap -->
    <div class="tnbWrap" data-section="tnbWrap">
      <div class="tnb resWidth">
        <div class="tools" data-section="tnbLeftTools">
          <div class="tnbLeftItems">
            <div class="quickLinks">
              <a href="#" class="tnbLink" title="즐겨찾기 추가" onclick="bookmark()">
                <i class="bx bx-bookmark"></i>즐겨찾기
              </a>
            </div>
            <div class="visitorInfo">
              <span class="visitCount">
                <i class="bx bx-time-five"></i><strong><?php echo date('H:i'); ?></strong>
              </span>
              <span class="onlineCount">
                <i class='bx bx-calendar-check'></i><?php echo date('m/d'); ?> <strong><?php 
                                $weekday = array('일','월','화','수','목','금','토');
                                echo $weekday[date('w')];
                                ?>요일</strong>
              </span>
            </div>
          </div>
        </div>
        <div class="tools" data-section="tnbRightTools">
          <div class="tnbRightItems" data-item="header_login">
            <?php include G5_THEME_PATH.'/ui_module/ui_weather/weather.php'; ?>
            <?php echo outlogin('theme/base_top'); // 외부 로그인 ?>
          </div>
        </div>
      </div>
    </div>

    <!-- headerWrap -->
    <div class="headerWrap resWidth">
      <!-- staWrap -->
      <div class="staWrap">
        <!-- titleWrap -->
        <div class="titleWrap">
          <div class="title">
            <button type="button" id="drawerNavOn" title="모바일메뉴" class="moNavi">
              <i class="bx bx-menu"></i>
            </button>
            <a href="<?php echo G5_URL; ?>" aria-label="홈으로 가기">
              <h1><i class='bx <?php echo $theme_logo_icon; ?>' data-item="logoIcon"></i> <strong
                  data-item="logoTxt"><?php echo $theme_logo_text; ?></strong></h1>
            </a>
          </div>
          <!-- headerGnb -->
          <?php
                        include G5_THEME_PATH.'/ui_include/ui_headerGnb.php'; // ui_headerGnb
                    ?>
        </div>

        <div class="tools">
          <div class="buttonWrap">
            <button type="button" data-btn="btnSearch" class="btnTools" title="검색"><i class="bx bx-search"></i></button>
            <?php if ($is_member) {  ?>
            <button type="button" class="btnTools" title="마이페이지"
              onclick="location='<?php echo G5_BBS_URL ?>/uxc_mypage.php'"><i class='bx bx-user'></i></button>
            <?php } ?>
            <?php if($is_admin == 'super') { ?>
            <button type="button" class="btnTools" title="테마설정"
              onclick="location='<?php echo G5_BBS_URL ?>/uxc_theme_config.php'"><i class='bx bx-cog'></i></button>
            <?php } ?>
            <button type="button" class="btnTools settingview" title="모드 전환" onclick="settingview()"><i
                class="bx bx-sun"></i><i class="bx bx-moon"></i>
              <div id="theme" class="sr-only">light</div>
            </button>
          </div>
        </div>
      </div>
    </div>



  </header>


  <!-- widget_mainSlider -->
  <?php if (defined("_INDEX_")) { ?>
  <?php
            include G5_THEME_PATH.'/ui_module/ui_mainSlider/ui_mainSlider.php';
        ?>
  <?php } ?>

  <?php if (!defined("_INDEX_")) { ?>
  <!-- ui_kvWrap 수정 -->
  <?php
            include G5_THEME_PATH.'/ui_include/ui_kvWrap.php'; // ui_kvWrap
        ?>
  <?php } ?>

  <!-- container -->
  <div class="container resWidth" data-layout="container">
    <!-- sideWrap -->
    <?php if (!defined("_INDEX_")) { ?>
    <?php if ($board['bo_subject']) { ?>
    <aside class="sideWrap" data-section="sideWrap" id="">
      <div class="itemGroup">
        <!-- sideGnb -->
        <?php
                        include G5_THEME_PATH.'/ui_include/ui_subSnb.php'; // menu
                    ?>
      </div>

      <div class="itemGroup">

      </div>
    </aside>
    <?php } ?>
    <?php } ?>
    <!-- contentsWrap -->
    <main class="contentsWrap" id="contentsWrap" data-section="contentsWrap">
      <!-- 타이틀 -->
