<?php
    // 전역변수 선언
    global $member, $g5, $gr_id, $bo_table, $co_id, $board, $group, $is_admin;
    
    // 회원 레벨 안전하게 가져오기 (비회원은 1)
    $member_level = 1;
    if (isset($member) && is_array($member) && isset($member['mb_level'])) {
        $member_level = (int)$member['mb_level'];
    }
    
    // 메뉴 데이터 가져오기
    $menu_datas = get_menu_db(0, true);
    if (!is_array($menu_datas)) {
        $menu_datas = array();
    }
    
    $current_bo_table = isset($bo_table) ? $bo_table : '';
    $current_co_id = isset($co_id) ? $co_id : '';
    
    // 현재 페이지가 속한 메뉴 그룹 찾기
    $menu_datas2 = array();
    
    // 메뉴 데이터가 있을 때만 처리
    if (is_array($menu_datas) && count($menu_datas) > 0) {
        // 메뉴 데이터에서 현재 페이지 찾기
        foreach ($menu_datas as $row) {
            if (empty($row)) continue;
            
            // 메뉴 권한 체크
            $menu_level = 0;
            if (isset($row['me_level']) && is_numeric($row['me_level'])) {
                $menu_level = (int)$row['me_level'];
            }
            if ($menu_level > 0 && $member_level < $menu_level) continue;
            
            // 서브메뉴 검색
            if (isset($row['sub']) && is_array($row['sub'])) {
                foreach ($row['sub'] as $row2) {
                    if (empty($row2)) continue;
                    
                    // co_id로 찾기
                    if (!empty($current_co_id) && isset($row2['me_link']) && strpos($row2['me_link'], $current_co_id) !== false) {
                        $menu_datas2 = $row['sub'];
                        break 2; // 이중 루프 탈출
                    }
                    
                    // bo_table로 찾기
                    if (!empty($current_bo_table) && isset($row2['me_link']) && strpos($row2['me_link'], $current_bo_table) !== false) {
                        $menu_datas2 = $row['sub'];
                        break 2; // 이중 루프 탈출
                    }
                }
            }
            
            // 메인메뉴에서도 확인
            if (empty($menu_datas2) && isset($row['me_link'])) {
                // bo_table로 찾기
                if (!empty($current_bo_table) && strpos($row['me_link'], $current_bo_table) !== false) {
                    $menu_datas2 = (isset($row['sub']) && is_array($row['sub']) && count($row['sub']) > 0) ? $row['sub'] : array($row);
                    break;
                }
                // co_id로 찾기
                if (!empty($current_co_id) && strpos($row['me_link'], $current_co_id) !== false) {
                    $menu_datas2 = (isset($row['sub']) && is_array($row['sub']) && count($row['sub']) > 0) ? $row['sub'] : array($row);
                    break;
                }
            }
        }
    }
    ?>

    <?php
    // 실제 표시될 메뉴 개수 계산
    $display_menu_count = 0;

    if (count($menu_datas2) > 0) {
        // menu_datas2에서 권한 있는 메뉴만 카운트
        foreach ($menu_datas2 as $row) {
            if (empty($row)) continue;

            // 권한 체크
            $sub_menu_level = 0;
            if (isset($row['me_level']) && is_numeric($row['me_level'])) {
                $sub_menu_level = (int)$row['me_level'];
            }
            if ($sub_menu_level > 0 && $member_level < $sub_menu_level) continue;

            $display_menu_count++;
        }
    } else if(isset($gr_id) && $gr_id && isset($g5['board_table'])) {
        // 그룹 게시판 개수 카운트
        $escaped_gr_id = isset($GLOBALS['connect_db']) ? mysqli_real_escape_string($GLOBALS['connect_db'], $gr_id) : addslashes($gr_id);
        $count_sql = "SELECT COUNT(*) as cnt FROM {$g5['board_table']}
					WHERE gr_id = '".$escaped_gr_id."'
					AND bo_list_level <= '".(int)$member_level."'
					AND bo_use_cert = ''
					AND bo_use = '1'";
        $count_result = @sql_query($count_sql, false);
        if ($count_result) {
            $count_row = sql_fetch_array($count_result);
            $display_menu_count = (int)$count_row['cnt'];
        }
    }

    // 메뉴가 2개 이상일 때만 표시
    if ($display_menu_count > 0) {
    ?>
	<div class="titleWrap">
		<h3>
			<?php echo get_text(uxc_get_current_menu_group() ?: ($group['gr_subject'] ?? '')); ?>
		</h3>
	</div>
    <div class="snbMenuWrap" data-section="snbWrap2">
        <div class="snb resWidth">
            <div class="tabNavigation">
                <?php
                // 찾은 메뉴들 표시
                if (!empty($menu_datas2)) {
                    foreach ($menu_datas2 as $row) {
                        if (empty($row)) continue;
                        
                        // 권한 체크
                        $sub_menu_level = 0;
                        if (isset($row['me_level']) && is_numeric($row['me_level'])) {
                            $sub_menu_level = (int)$row['me_level'];
                        }
                        if ($sub_menu_level > 0 && $member_level < $sub_menu_level) continue;
                        
                        // active 클래스 설정
                        $active_class = '';
                        if (isset($row['me_link'])) {
                            // bo_table 체크
                            if (!empty($current_bo_table) && strpos($row['me_link'], 'bo_table='.$current_bo_table) !== false) {
                                $active_class = 'active';
                            }
                            // co_id 체크
                            else if (!empty($current_co_id) && strpos($row['me_link'], 'co_id='.$current_co_id) !== false) {
                                $active_class = 'active';
                            }
                            // URL 끝부분 체크
                            else if (!empty($current_bo_table) && strpos($row['me_link'], $current_bo_table) !== false) {
                                $active_class = 'active';
                            }
                        }
                        ?>
                        <a href="<?php echo isset($row['me_link']) ? $row['me_link'] : '#'; ?>" class="tabItem <?php echo $active_class; ?>">
                            <?php echo isset($row['me_name']) ? $row['me_name'] : ''; ?>
                        </a>
                        <?php
                    }
                } else if(isset($gr_id) && $gr_id && isset($g5['board_table'])) {
                    // 메뉴에서 찾지 못했지만 그룹ID가 있는 경우
                    // 현재 그룹의 모든 게시판 직접 조회
                    $displayed = false;
                    
                    $escaped_gr_id = isset($GLOBALS['connect_db']) ? mysqli_real_escape_string($GLOBALS['connect_db'], $gr_id) : addslashes($gr_id);
                    $board_sql = "SELECT bo_table, bo_subject FROM {$g5['board_table']} 
                                 WHERE gr_id = '".$escaped_gr_id."'
                                 AND bo_list_level <= '".(int)$member_level."'
                                 AND bo_use_cert = ''
                                 AND bo_use = '1'
                                 ORDER BY bo_order, bo_table";
                    $board_result = @sql_query($board_sql, false);
                    
                    if ($board_result) {
                        while ($board_row = sql_fetch_array($board_result)) {
                            $bo_link = G5_BBS_URL.'/board.php?bo_table='.$board_row['bo_table'];
                            $active_class = ($board_row['bo_table'] == $bo_table) ? 'active' : '';
                            $displayed = true;
                    ?>
                    <a href="<?php echo $bo_link; ?>" class="tabItem <?php echo $active_class; ?>">
                        <?php echo get_text($board_row['bo_subject']); ?>
                    </a>
                    <?php
                        }
                    }
                    
                    // 그래도 게시판이 하나도 표시되지 않았다면 메뉴 설정 안내
                    if(!$displayed) {
                        // 관리자에게만 메뉴 설정 안내 표시
                        if ($member['mb_id'] && $member['mb_level'] >= 10) {
                ?>
                    <a href="<?php echo G5_ADMIN_URL; ?>/menu_list.php" class="tabItem">메뉴를 설정해주세요</a>
                <?php
                        } else {
                            // 일반 사용자에게는 홈 링크만 표시
                ?>
                    <a href="<?php echo G5_URL; ?>" class="tabItem">홈</a>
                <?php
                        }
                    }
                } else {
                    // 그룹이 없을 경우 기본 메뉴 표시
                ?>
                    <a href="<?php echo G5_URL; ?>" class="tabItem">홈</a>
                <?php } ?>
            </div>
        </div>
    </div>
    <?php } // 메뉴가 있을 때만 표시 종료 ?>