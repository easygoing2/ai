<?php
if (!defined('_GNUBOARD_')) exit;

/**
 * Head Sub Theme Option
 * Theme option scripts: Dark mode, Side menu, Google Translate
 */

// Parse cf_7 config for start_dark_mode setting
$other_config_sub = array();
if (!empty($config['cf_7']) && (strpos($config['cf_7'], '{') === 0 || strpos($config['cf_7'], '[') === 0)) {
    $other_config_sub = json_decode($config['cf_7'], true);
    if (!is_array($other_config_sub)) {
        $other_config_sub = array();
    }
}
$start_dark_mode = isset($other_config_sub['start_dark_mode']) && $other_config_sub['start_dark_mode'] == '1';
?>
<script>
(function() {
  try {
    var storedTheme = localStorage.getItem("theme");
    var startDarkMode = <?php echo $start_dark_mode ? 'true' : 'false'; ?>;

    if (storedTheme === "darkMode") {
      document.documentElement.classList.add("darkMode");
    } else if (storedTheme === "lightMode") {
      document.documentElement.classList.remove("darkMode");
    } else if (!storedTheme && startDarkMode) {
      localStorage.setItem("theme", "darkMode");
      document.documentElement.classList.add("darkMode");
    }
  } catch (e) {
    console.error('Theme load error:', e);
  }
})();

// Side menu state
(function() {
  try {
    var storedSideMenu = localStorage.getItem("sideMenu");

    if (storedSideMenu === "off") {
      document.documentElement.classList.add("sideMenuOff");
    } else if (storedSideMenu === "on") {
      document.documentElement.classList.remove("sideMenuOff");
    }
  } catch (e) {
    console.error('SideMenu load error:', e);
  }
})();
</script>

<!-- Google Translate -->
<script>
(function() {
    var lang = localStorage.getItem('selectedLanguage');
    if (lang && lang !== 'ko') {
        var cookie = 'googtrans=/ko/' + lang;
        document.cookie = cookie + '; path=/';
        document.cookie = cookie + '; path=/; domain=' + location.hostname;
    } else if (lang === 'ko') {
        var expire = '; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/';
        document.cookie = 'googtrans=' + expire;
        document.cookie = 'googtrans=' + expire + '; domain=' + location.hostname;
    }
})();

function googleTranslateElementInit() {
    new google.translate.TranslateElement({
        pageLanguage: 'ko',
        includedLanguages: 'ko,en,ja,zh-CN,zh-TW,es,fr,de,ru,pt,vi,th,id,ar,hi,ms,tl,it,nl,pl,tr,sv,uk,bn,fa,he,ur',
        layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
        autoDisplay: false
    }, 'google_translate_element');
}
</script>
<script src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
<div id="google_translate_element" style="display:none;"></div>
