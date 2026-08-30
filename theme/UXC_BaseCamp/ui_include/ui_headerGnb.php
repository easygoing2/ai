<div class="gnbWrap" data-section="header_gnb">
    <!-- staGnb -->
    <div class="gnb resWidth">
        <nav>
            <ul class="gnb-menu">
                <?php
                // 전역변수 선언
                global $member, $is_admin;
                
                // 회원 레벨 안전하게 가져오기 (비회원은 1)
                $member_level = 1;
                if (isset($member) && is_array($member) && isset($member['mb_level'])) {
                    $member_level = (int)$member['mb_level'];
                }
                
                $menu_datas = get_menu_db(0, true);
                $i = 0;
                
                if (is_array($menu_datas)) {
                    foreach ($menu_datas as $row) {
                        if (empty($row) || !is_array($row)) continue;
                        
                        // 메뉴 권한 체크
                        $menu_level = 0;
                        if (isset($row['me_level']) && is_numeric($row['me_level'])) {
                            $menu_level = (int)$row['me_level'];
                        }
                        if ($menu_level > 0 && $member_level < $menu_level) continue;
                        
                        $has_sub = (isset($row['sub']) && is_array($row['sub']) && count($row['sub']) > 0) ? true : false;
                        $main_link = isset($row['me_link']) ? $row['me_link'] : '#';
                        $main_target = isset($row['me_target']) ? $row['me_target'] : 'self';
                        $main_name = isset($row['me_name']) ? $row['me_name'] : '';
                        // 1depth 자체 페이지와 하위 2depth 페이지 모두 활성 상태로 표시
                        $is_active = is_menu_active($main_link);

                        // 서브메뉴 active 체크를 위한 버퍼링
                        ob_start();
                        if ($has_sub) {
                            echo '<ul class="dropdown-menu">';
                            foreach ($row['sub'] as $row2) {
                                if (empty($row2) || !is_array($row2)) continue;

                                // 서브메뉴 권한 체크
                                $sub_menu_level = 0;
                                if (isset($row2['me_level']) && is_numeric($row2['me_level'])) {
                                    $sub_menu_level = (int)$row2['me_level'];
                                }
                                if ($sub_menu_level > 0 && $member_level < $sub_menu_level) continue;

                                $sub_link = isset($row2['me_link']) ? $row2['me_link'] : '#';
                                $sub_target = isset($row2['me_target']) ? $row2['me_target'] : 'self';
                                $sub_name = isset($row2['me_name']) ? $row2['me_name'] : '';

                                // 서브메뉴 active 체크
                                $sub_active = false;
                                if (isset($row2['me_link']) && !empty($row2['me_link'])) {
                                    $parsed2 = parse_url($row2['me_link']);
                                    if (is_array($parsed2) && isset($parsed2['query'])) {
                                        parse_str($parsed2['query'], $q2);
                                        if (isset($q2['bo_table']) && isset($_GET['bo_table']) && $q2['bo_table'] === $_GET['bo_table']) {
                                            $sub_active = true;
                                        }
                                    }

                                    if (!$sub_active && is_array($parsed2) && isset($parsed2['path'])) {
                                        $path_parts2 = explode('/', trim($parsed2['path'], '/'));
                                        if (count($path_parts2) >= 1 && isset($_GET['bo_table']) && $path_parts2[0] === $_GET['bo_table']) {
                                            $sub_active = true;
                                        }
                                    }
                                }

                                if ($sub_active) $is_active = true;
                                ?>
                                <li>
                                    <a href="<?php echo htmlspecialchars($sub_link); ?>"
                                        target="_<?php echo htmlspecialchars($sub_target); ?>"
                                        class="<?php echo $sub_active ? 'active' : ''; ?>">
                                        <?php echo htmlspecialchars($sub_name); ?>
                                    </a>
                                </li>
                                <?php
                            }
                            echo '</ul>';
                        }
                        $sub_output = ob_get_clean();
                        ?>
                        <li class="<?php echo $has_sub ? 'has-dropdown' : ''; ?>">
                            <a href="<?php echo htmlspecialchars($main_link); ?>"
                                target="_<?php echo htmlspecialchars($main_target); ?>"
                                class="<?php echo $has_sub ? 'subDepth' : ''; ?><?php echo $is_active ? ' on' : ''; ?>">
                                <?php echo htmlspecialchars($main_name); ?>
                                <?php if ($has_sub) echo '<i class="bx bx-chevron-down"></i>'; ?>
                            </a>
                            <?php echo $sub_output; ?>
                        </li>
                        <?php
                        $i++;
                    }
                }
                
                if ($i == 0) {
                    ?>
                    <li class="gnb_empty">메뉴 준비 중입니다.</li>
                    <?php
                }
                ?>
            </ul>
        </nav>

    </div>
</div>
