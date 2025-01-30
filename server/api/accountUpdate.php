<?php
include('../db.php');
include('../functions.php');
/**
 * 既存アカウント更新・削除・停止API
 */

// apiに直接アクセスした場合、画面には0とだけ表示されるように設定
$res = 0;
try {
  // クライアント側から取得してきたパラメータを定義
  $name = h($_POST["name"]);
  $login_id = h($_POST["login_id"]);
  $hashpass = isset($_POST["password"]) ? hashpass($_POST["password"]) : '';
  $comment = h($_POST["comment"]);

  // 初期レスポンス
  $res = ['type' => 'fail', 'message' => ''];

  // クライアント側からのtypeパラメータに応じて、更新・削除・停止・停止解除のいずれかに振り分ける
  if ($_POST['type'] == 'update') {

    // 他のアカウントとのlogin_idの重複確認
    $judgeSql_loginID = "SELECT COUNT(id) FROM " . $account_table . " WHERE login_id = :login_id AND id <> :id";
    $stmt = $connection->prepare($judgeSql_loginID);
    $stmt->execute([':login_id' => $login_id, ':id' => $_POST["id"]]);
    if ($stmt->fetchColumn() > 0) {
      $res['message'] = "既に同一のログインIDが他のユーザーに使われています。";
      echo json_encode($res);
      exit;
    }

    // 他のアカウントとのnameの重複確認
    $judgeSql_name = "SELECT COUNT(id) FROM " . $account_table . " WHERE name = :name AND id <> :id";
    $stmt = $connection->prepare($judgeSql_name);
    $stmt->execute([':name' => $name, ':id' => $_POST["id"]]);
    if ($stmt->fetchColumn() > 0) {
      $res['message'] = "既に同一のユーザー名が他のユーザーに使われています。";
      echo json_encode($res);
      exit;
    }

    // 更新処理（パラメータにpasswordがあったかどうかで場合分けをする）
    $updatePassSql = ($hashpass == '') ? '' : "password = :password, ";
    $updateSql = "UPDATE " . $account_table . " SET "
      . "name = :name, login_id = :login_id, " . $updatePassSql
      . "comment = :comment, updated_at = NOW(), updated_user_id = :id "
      . "WHERE id = :id";
    $stmt = $connection->prepare($updateSql);

    // トランザクション開始
    $connection->beginTransaction();

    // 紐づけるパラメータを設定したオブジェクト配列を変数に格納する
    $paramArray = [
      ':name' => $name,
      ':login_id' => $login_id,
      ':comment' => $comment,
      ':id' => $_POST["id"]
    ];
    if ($hashpass != '') $paramArray[':password'] = $hashpass;

    // パラメータを紐づけたオブジェクトを投入し、更新を実行する
    if ($stmt->execute($paramArray)) {
      // コミット
      $connection->commit();

      $res['type'] = "success";
      $res['message'] = "ユーザー情報の更新を完了しました。";
    } else {
      throw new Exception("登録処理に失敗しました");
    }
  } else if ($_POST['type'] == 'delete') {

    $deleteSql = "DELETE FROM " . $account_table . " WHERE id = :id";
    $stmt = $connection->prepare($deleteSql);

    $connection->beginTransaction();
    if ($stmt->execute([':id' => $_POST['id']])) {
      $connection->commit();

      $res['type'] = "success";
      $res['message'] = "アカウントを削除しました。ご利用ありがとうございました！";
    } else {
      throw new Exception("登録処理に失敗しました");
    }
  } else if ($_POST['type'] == 'stop') {

    $stopSQL = "UPDATE " . $account_table . " SET "
      . "is_stopped = 1, stopped_at = NOW(), stopped_user_id = :stopped_user_id "
      . "WHERE id = :id";
    $stmt = $connection->prepare($stopSQL);

    $connection->beginTransaction();
    $paramArray = [
      ':stopped_user_id' => $_POST['id'],
      ':id' => $_POST['selected_id']
    ];
    if ($stmt->execute($paramArray)) {
      $connection->commit();

      $res['type'] = "success";
      $res['message'] = "ご指定のアカウントを停止しました。";
    } else {
      throw new Exception("登録処理に失敗しました");
    }
  } else {

    $restartSQL = "UPDATE " . $account_table . " SET is_stopped = 0 WHERE id = :id";
    $stmt = $connection->prepare($restartSQL);

    $connection->beginTransaction();
    if ($stmt->execute([':id' => $_POST['selected_id']])) {
      $connection->commit();

      $res['type'] = "success";
      $res['message'] = "ご指定のアカウントを再開しました。";
    } else {
      throw new Exception("登録処理に失敗しました");
    }
  }

  // リクエスト先とapi直接アクセス時の画面にはこの値を返す
  echo json_encode($res);
} catch (Exception $e) {
  echo $e->getMessage();
}

exit;
