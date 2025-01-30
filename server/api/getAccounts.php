<?php
include('../db.php');
include('../functions.php');
/**
 * 既存アカウント取得API
 */

// apiに直接アクセスした場合、画面には0とだけ表示されるように設定
$res = 0;
try {
  // 初期レスポンス
  $res = ['accounts' => null];

  if ($_POST['id'] == 1) {
    // idが1のアカウント（最初期登録アカウント）以外を取得する
    $getSql = "SELECT id, name, "
      . "DATE_FORMAT(created_at, '%Y-%m-%d') AS created_at, is_stopped FROM " . $account_table
      . " WHERE id > :id";
    $stmt = $connection->prepare($getSql);
    $stmt->execute([':id' => $_POST['id']]);
    $res['accounts'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  echo json_encode($res);
} catch (Exception $e) {
  echo $e->getMessage();
}

exit;
