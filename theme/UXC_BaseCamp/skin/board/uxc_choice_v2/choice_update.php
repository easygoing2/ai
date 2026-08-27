<?php
// 그누보드 공통 파일 포함
include_once('../../../../../common.php');

// JSON 응답 헤더 설정
header('Content-Type: application/json; charset=utf-8');

// 에러 응답 함수
if (!function_exists('send_error')) {
    function send_error($message) {
        die(json_encode(array('error' => $message)));
    }
}

// 성공 응답 함수
if (!function_exists('send_success')) {
    function send_success($message) {
        die(json_encode(array('success' => true, 'message' => $message)));
    }
}

// POST 요청만 허용
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_error('잘못된 접근입니다.');
}

// 로그인 체크
if (!$member['mb_id']) {
    send_error('로그인이 필요합니다.');
}

// 파라미터 받기
$bo_table = isset($_POST['bo_table']) ? preg_replace('/[^a-z0-9_]/i', '', $_POST['bo_table']) : '';
$wr_id = isset($_POST['wr_id']) ? (int)$_POST['wr_id'] : 0;
$comment_id = isset($_POST['comment_id']) ? (int)$_POST['comment_id'] : 0;

// 필수 파라미터 체크
if (!$bo_table || !$wr_id || !$comment_id) {
    send_error('필수 정보가 누락되었습니다.');
}

// 게시판 설정 로드
$sql = " select * from {$g5['board_table']} where bo_table = '{$bo_table}' ";
$board = sql_fetch($sql);

if (!$board['bo_table']) {
    send_error('존재하지 않는 게시판입니다.');
}

// 원글 정보 가져오기
$write_table = $g5['write_prefix'] . $bo_table;
$sql = " select * from {$write_table} where wr_id = '{$wr_id}' and wr_is_comment = 0 ";
$write = sql_fetch($sql);

if (!$write['wr_id']) {
    send_error('글이 존재하지 않습니다.');
}

// 글 작성자 본인 확인
if ($write['mb_id'] != $member['mb_id']) {
    send_error('글 작성자만 채택할 수 있습니다.');
}

// 이미 채택된 답변이 있는지 확인
if ($write['wr_1'] && $write['wr_1'] != '0') {
    send_error('이미 채택된 답변이 있습니다.');
}

// 댓글 정보 가져오기
$sql = " select * from {$write_table} where wr_id = '{$comment_id}' and wr_parent = '{$wr_id}' and wr_is_comment = 1 ";
$comment = sql_fetch($sql);

if (!$comment['wr_id']) {
    send_error('댓글이 존재하지 않습니다.');
}

// 자신의 댓글은 채택 불가
if ($comment['mb_id'] == $member['mb_id']) {
    send_error('자신의 댓글은 채택할 수 없습니다.');
}

// 채택 처리 (wr_1 필드에 채택된 댓글 ID 저장)
$sql = " update {$write_table} set wr_1 = '{$comment_id}' where wr_id = '{$wr_id}' ";
sql_query($sql);

// 채택된 댓글 작성자에게 포인트 지급 (옵션)
if ($board['bo_comment_point'] > 0 && $comment['mb_id']) {
    // 채택 보너스 포인트 (댓글 포인트의 2배)
    $point = $board['bo_comment_point'] * 2;
    insert_point($comment['mb_id'], $point, "{$board['bo_subject']} {$wr_id}-{$comment_id} 댓글 채택", $bo_table, $comment_id, '채택');
}

// 성공 응답
send_success('답변이 채택되었습니다.');
?>