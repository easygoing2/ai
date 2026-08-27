<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.G5_THEME_URL.'/page/css/style.css">', 0);
?>

<div class="pageWrap">
    <div class="pageHeaderWrap">
        <div class="pageHeader">
            <h2>회사소개</h2>
        </div>
    </div>
    <div class="pageBody">
        <div class="icon-grid">
            <!-- Navigation -->
            <div class="icon-item">
                <i class='bx bx-home'></i>
                <span>bx-home</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-home-alt'></i>
                <span>bx-home-alt</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-menu'></i>
                <span>bx-menu</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-menu-alt-left'></i>
                <span>bx-menu-alt-left</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-menu-alt-right'></i>
                <span>bx-menu-alt-right</span>
            </div>

            <!-- Search & Actions -->
            <div class="icon-item">
                <i class='bx bx-search'></i>
                <span>bx-search</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-search-alt'></i>
                <span>bx-search-alt</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-x'></i>
                <span>bx-x</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-check'></i>
                <span>bx-check</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-plus'></i>
                <span>bx-plus</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-minus'></i>
                <span>bx-minus</span>
            </div>

            <!-- User & Account -->
            <div class="icon-item">
                <i class='bx bx-user'></i>
                <span>bx-user</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-user-circle'></i>
                <span>bx-user-circle</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-user-plus'></i>
                <span>bx-user-plus</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-group'></i>
                <span>bx-group</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-log-in'></i>
                <span>bx-log-in</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-log-out'></i>
                <span>bx-log-out</span>
            </div>

            <!-- Arrows -->
            <div class="icon-item">
                <i class='bx bx-chevron-up'></i>
                <span>bx-chevron-up</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-chevron-down'></i>
                <span>bx-chevron-down</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-chevron-left'></i>
                <span>bx-chevron-left</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-chevron-right'></i>
                <span>bx-chevron-right</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-arrow-back'></i>
                <span>bx-arrow-back</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-right-arrow-alt'></i>
                <span>bx-right-arrow-alt</span>
            </div>

            <!-- Favorites & Social -->
            <div class="icon-item">
                <i class='bx bx-heart'></i>
                <span>bx-heart</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-star'></i>
                <span>bx-star</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-bookmark'></i>
                <span>bx-bookmark</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-share'></i>
                <span>bx-share</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-share-alt'></i>
                <span>bx-share-alt</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-like'></i>
                <span>bx-like</span>
            </div>

            <!-- Files & Actions -->
            <div class="icon-item">
                <i class='bx bx-download'></i>
                <span>bx-download</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-upload'></i>
                <span>bx-upload</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-edit'></i>
                <span>bx-edit</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-trash'></i>
                <span>bx-trash</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-save'></i>
                <span>bx-save</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-copy'></i>
                <span>bx-copy</span>
            </div>

            <!-- Settings -->
            <div class="icon-item">
                <i class='bx bx-cog'></i>
                <span>bx-cog</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-wrench'></i>
                <span>bx-wrench</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-slider'></i>
                <span>bx-slider</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-filter'></i>
                <span>bx-filter</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-filter-alt'></i>
                <span>bx-filter-alt</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-sort'></i>
                <span>bx-sort</span>
            </div>

            <!-- Notifications -->
            <div class="icon-item">
                <i class='bx bx-bell'></i>
                <span>bx-bell</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-notification'></i>
                <span>bx-notification</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-envelope'></i>
                <span>bx-envelope</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-message'></i>
                <span>bx-message</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-comment'></i>
                <span>bx-comment</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-chat'></i>
                <span>bx-chat</span>
            </div>

            <!-- Media -->
            <div class="icon-item">
                <i class='bx bx-play'></i>
                <span>bx-play</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-pause'></i>
                <span>bx-pause</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-stop'></i>
                <span>bx-stop</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-volume-full'></i>
                <span>bx-volume-full</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-volume-mute'></i>
                <span>bx-volume-mute</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-music'></i>
                <span>bx-music</span>
            </div>

            <!-- Security -->
            <div class="icon-item">
                <i class='bx bx-lock'></i>
                <span>bx-lock</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-lock-open'></i>
                <span>bx-lock-open</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-shield'></i>
                <span>bx-shield</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-key'></i>
                <span>bx-key</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-show'></i>
                <span>bx-show</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-hide'></i>
                <span>bx-hide</span>
            </div>

            <!-- Layout & View -->
            <div class="icon-item">
                <i class='bx bx-grid'></i>
                <span>bx-grid</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-grid-alt'></i>
                <span>bx-grid-alt</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-list-ul'></i>
                <span>bx-list-ul</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-list-ol'></i>
                <span>bx-list-ol</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-layout'></i>
                <span>bx-layout</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-columns'></i>
                <span>bx-columns</span>
            </div>

            <!-- Status & Info -->
            <div class="icon-item">
                <i class='bx bx-info-circle'></i>
                <span>bx-info-circle</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-error'></i>
                <span>bx-error</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-error-circle'></i>
                <span>bx-error-circle</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-check-circle'></i>
                <span>bx-check-circle</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-x-circle'></i>
                <span>bx-x-circle</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-help-circle'></i>
                <span>bx-help-circle</span>
            </div>

            <!-- Time -->
            <div class="icon-item">
                <i class='bx bx-time'></i>
                <span>bx-time</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-calendar'></i>
                <span>bx-calendar</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-alarm'></i>
                <span>bx-alarm</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-stopwatch'></i>
                <span>bx-stopwatch</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-timer'></i>
                <span>bx-timer</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-history'></i>
                <span>bx-history</span>
            </div>

            <!-- Documents -->
            <div class="icon-item">
                <i class='bx bx-file'></i>
                <span>bx-file</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-folder'></i>
                <span>bx-folder</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-folder-open'></i>
                <span>bx-folder-open</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-copy-alt'></i>
                <span>bx-copy-alt</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-paste'></i>
                <span>bx-paste</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-cut'></i>
                <span>bx-cut</span>
            </div>

            <!-- Media Files -->
            <div class="icon-item">
                <i class='bx bx-image'></i>
                <span>bx-image</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-video'></i>
                <span>bx-video</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-camera'></i>
                <span>bx-camera</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-microphone'></i>
                <span>bx-microphone</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-headphone'></i>
                <span>bx-headphone</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-photo-album'></i>
                <span>bx-photo-album</span>
            </div>

            <!-- Shopping -->
            <div class="icon-item">
                <i class='bx bx-cart'></i>
                <span>bx-cart</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-shopping-bag'></i>
                <span>bx-shopping-bag</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-store'></i>
                <span>bx-store</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-purchase-tag'></i>
                <span>bx-purchase-tag</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-wallet'></i>
                <span>bx-wallet</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-credit-card'></i>
                <span>bx-credit-card</span>
            </div>

            <!-- Connectivity -->
            <div class="icon-item">
                <i class='bx bx-link'></i>
                <span>bx-link</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-unlink'></i>
                <span>bx-unlink</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-wifi'></i>
                <span>bx-wifi</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-bluetooth'></i>
                <span>bx-bluetooth</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-signal-5'></i>
                <span>bx-signal-5</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-cloud'></i>
                <span>bx-cloud</span>
            </div>

            <!-- Location -->
            <div class="icon-item">
                <i class='bx bx-map'></i>
                <span>bx-map</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-map-pin'></i>
                <span>bx-map-pin</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-location-plus'></i>
                <span>bx-location-plus</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-navigation'></i>
                <span>bx-navigation</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-target-lock'></i>
                <span>bx-target-lock</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-compass'></i>
                <span>bx-compass</span>
            </div>

            <!-- Misc Common -->
            <div class="icon-item">
                <i class='bx bx-refresh'></i>
                <span>bx-refresh</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-loader'></i>
                <span>bx-loader</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-dots-horizontal'></i>
                <span>bx-dots-horizontal</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-dots-vertical'></i>
                <span>bx-dots-vertical</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-power-off'></i>
                <span>bx-power-off</span>
            </div>
            <div class="icon-item">
                <i class='bx bx-rocket'></i>
                <span>bx-rocket</span>
            </div>
        </div>

    </div>
</div>

<style>
/* Icon Grid Styles */
.icon-grid {display:grid; padding:20px; grid-template-columns:repeat(auto-fill, minmax(100px, 1fr));gap:12px;}
.icon-item {display:flex; justify-content:center; align-items:center; flex-direction:column; padding:12px 8px; border:1px solid var(--ui-color-gray-300); border-radius:var(--ui-radius-s); background:var(--ui-color-white); cursor:pointer; transition:all 0.2s ease;}
.icon-item:hover {border-color:var(--ui-color-gray-500); background:var(--ui-color-gray-50); transform:translateY(-2px); box-shadow:0 2px 8px rgba(0, 0, 0, 0.08);}
.icon-item i {margin-bottom:8px; color:var(--ui-color-gray-700); font-size:32px; transition:color 0.2s ease;}
.icon-item:hover i {color:var(--ui-color-gray-900);}
.icon-item span {color:var(--ui-color-gray-800); font-size:10px; font-family:'Courier New', monospace; text-align:center; word-break:break-word; line-height:1.3;}
/* Responsive */
@media (max-width:768px) {
    .icon-grid {padding:15px; grid-template-columns:repeat(auto-fill, minmax(85px, 1fr));gap:10px;}
    .icon-item {padding:10px 6px;}
    .icon-item i {font-size:28px; margin-bottom:6px;}
    .icon-item span {font-size:9px;}
}
@media (max-width:480px) {
    .icon-grid {padding:12px; grid-template-columns:repeat(auto-fill, minmax(75px, 1fr));gap:8px;}
    .icon-item {padding:8px 4px;}
    .icon-item i {margin-bottom:5px; font-size:24px;}
    .icon-item span {font-size:8px;}
}

</style>