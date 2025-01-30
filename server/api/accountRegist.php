<?php
include('../db.php');
include('../functions.php');
/**
 * 既存アカウント情報判定＆新規登録API
 */

// apiに直接アクセスした場合、画面には0とだけ表示されるように設定
$res = 0;
try {
  // クライアント側から取得してきたパラメータを定義
  $name = h($_POST["name"]);
  $login_id = h($_POST["login_id"]);
  $hashpass = hashpass($_POST["password"]);
  $comment = h($_POST["comment"]);

  // 初期レスポンス
  $res = ['type' => 'fail', 'message' => ''];

  // login_idの重複確認
  $judgeSql_loginID = "SELECT COUNT(id) FROM " . $account_table . " WHERE login_id = :login_id";
  $stmt = $connection->prepare($judgeSql_loginID);
  $stmt->execute([':login_id' => $login_id]);
  if ($stmt->fetchColumn() > 0) {
    $res['message'] = "既に同一のログインIDが使われている";
    echo json_encode($res);
    exit;
  }

  // nameの重複確認
  $judgeSql_name = "SELECT COUNT(id) FROM " . $account_table . " WHERE name = :name";
  $stmt = $connection->prepare($judgeSql_name);
  $stmt->execute([':name' => $name]);
  if ($stmt->fetchColumn() > 0) {
    $res['message'] = "既に同一のユーザー名が使われている";
    echo json_encode($res);
    exit;
  }

  // 新規登録処理
  $insertSql = "INSERT INTO " . $account_table . " ("
    . "name, login_id, password, comment, created_at, created_user_id, updated_at, updated_user_id"
    . ") VALUES (:name, :login_id, :password, :comment, NOW(), 1, NOW(), 1)";
  $stmt = $connection->prepare($insertSql);

  // トランザクション開始
  $connection->beginTransaction();

  // レコード挿入
  if ($stmt->execute([
    ':name' => $name,
    ':login_id' => $login_id,
    ':password' => $hashpass,
    ':comment' => $comment
  ])) {
    // 挿入したレコードのIDを取得
    $lastInsertId = $connection->lastInsertId();

    // created_user_idとupdated_user_idを更新
    $updateStmtSql = "UPDATE " . $account_table . " SET created_user_id = :id, updated_user_id = :id WHERE id = :id";
    $updateStmt = $connection->prepare($updateStmtSql);
    $updateStmt->execute([':id' => $lastInsertId]);

    // コミット
    $connection->commit();

    $res['type'] = "success";
    $res['message'] = "ユーザー登録を完了";
  } else {
    throw new Exception("登録処理に失敗しました");
  }

  // リクエスト先とapi直接アクセス時の画面にはこの値を返す
  echo json_encode($res);
} catch (Exception $e) {
  echo $e->getMessage();
}

exit;
