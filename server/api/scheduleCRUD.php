<?php
include('../db.php');
include('../functions.php');
/**
 * スケジュールのCRUD用API
 */

// apiに直接アクセスした場合、画面には0とだけ表示されるように設定
$res = 0;

try {
  // 初期レスポンス
  $res = ['type' => 'fail', 'message' => ''];

  // クライアント側からのtypeパラメータに応じて機能を振り分ける
  if ($_POST['type'] == 'regist') {
    // スケジュール登録
    $insertSql = "INSERT INTO " . $data_table . " ("
      . "id, start_date, times, contents, comment, created_at, created_user_id"
      . ") VALUES (:id, :start_date, :times, :contents, :comment, NOW(), :created_user_id)";
    $stmt = $connection->prepare($insertSql);

    // トランザクション開始
    $connection->beginTransaction();

    // レコード挿入
    if ($stmt->execute([
      ':id' => $_POST['id'],
      ':start_date' => $_POST['start_date'],
      ':times' => $_POST['times'],
      ':contents' => $_POST['contents'],
      ':comment' => $_POST['comment'],
      ':created_user_id' => $_POST['created_user_id'],
    ])) {
      // コミット
      $connection->commit();

      $res['type'] = "success";
      $res['request'] = "regist";
      $res['post'] = $_POST;
      $res['message'] = "スケジュール登録を完了";
    } else {
      throw new Exception("登録処理に失敗しました");
    }
  } else if ($_POST['type'] == 'update') {
    // スケジュール更新
    $updateSql = "UPDATE " . $data_table . " SET "
      . "contents = :contents, comment = :comment, updated_at = NOW(), updated_user_id = :created_user_id "
      . "WHERE id = :id AND created_user_id = :created_user_id";
    $stmt = $connection->prepare($updateSql);

    $connection->beginTransaction();

    // レコード更新
    if ($stmt->execute([
      ':id' => $_POST['id'],
      ':contents' => $_POST['contents'],
      ':comment' => $_POST['comment'],
      ':created_user_id' => $_POST['created_user_id'],
    ])) {
      $connection->commit();

      $res['type'] = "success";
      $res['request'] = "update";
      $res['post'] = $_POST;
      $res['message'] = "スケジュール更新を完了";
    } else {
      throw new Exception("更新処理に失敗しました");
    }
  } else if ($_POST['type'] == 'delete') {
    // スケジュール削除
    $soloDeleteSql = (isset($_POST['note_id'])) ? " AND id = :note_id" : "";
    $deleteSql = "DELETE FROM " . $data_table . " WHERE created_user_id = :id" . $soloDeleteSql;
    $stmt = $connection->prepare($deleteSql);

    $connection->beginTransaction();
    $paramArray = [':id' => $_POST["id"]];
    if (isset($_POST['note_id'])) $paramArray[':note_id'] = $_POST['note_id'];
    if ($stmt->execute($paramArray)) {
      $connection->commit();

      $res['type'] = "success";
      $res['message'] = "スケジュールの削除を完了しました。";
    } else {
      throw new Exception("削除処理に失敗しました");
    }
  } else if ($_POST['type'] == 'delete_over60') {
    // スケジュール削除（登録から60日以上経過した分）
    $deleteSql = "DELETE FROM " . $data_table . " WHERE id IN " . $_POST["delete_note_ids"];
    $stmt = $connection->prepare($deleteSql);

    $connection->beginTransaction();
    if ($stmt->execute()) {
      $connection->commit();

      $res['type'] = "success";
      $res['message'] = "スケジュールの削除を完了しました。";
    } else {
      throw new Exception("削除処理に失敗しました");
    }
  } else if ($_POST['type'] == 'ids') {
    // id一覧取得
    $idsGetSql = "SELECT id FROM " . $data_table . " WHERE created_user_id = :created_user_id";
    $stmt = $connection->prepare($idsGetSql);
    $stmt->execute([':created_user_id' => $_POST['created_user_id']]);
    $objects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $ids = [];
    foreach ($objects as $item) {
      $ids[] = $item['id'];
    }

    $res['type'] = "success";
    $res['message'] = "ログインユーザーの登録スケジュールのIDリストを取得しました。";
    $res['list'] = $ids;
  } else {
    // スケジュール一覧取得
    $dataGetSql = "SELECT id, start_date, times, contents, comment, created_at "
      . "FROM " . $data_table . " WHERE created_user_id = :created_user_id";
    $stmt = $connection->prepare($dataGetSql);
    $stmt->execute([':created_user_id' => $_POST['created_user_id']]);
    $objects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 格納用の配列を生成してそちらにオブジェクトを格納する
    $schedules = [];
    foreach ($objects as $item) {
      $this_day = new DateTime($item['start_date']);
      $add_day = 0;

      switch ($item['times']) {
        case "02":
          $add_day = 1;
          break;
        case "03":
          $add_day = 2;
          break;
        case "04":
          $add_day = 4;
          break;
      }

      // start_dateに日付を加えて該当の日時に変換する
      $this_day->modify("+{$add_day} days");
      $year = $this_day->format('Y');
      $month = $this_day->format('m');
      $day = $this_day->format('d');
      $weekdays = ["月", "火", "水", "木", "金", "土", "日"];
      $weekday = $weekdays[$this_day->format('N') - 1];
      $item['first_studied'] = "{$year}-{$month}-{$day}（{$weekday}）";

      // リクエストされた日付との差を数値で取得する
      if (isset($_POST['request_date'])) {
        $datetime1 = new DateTime($this_day->format('Y-m-d'));
        $datetime2 = new DateTime($_POST['request_date']);
        $interval = $datetime1->diff($datetime2);
        $plusMinus = ($interval->invert) ? -1 : 1;
        $diff_days = ($plusMinus == -1) ? ($interval->days + 1) : $interval->days;
        $item['days_diff'] = $diff_days * $plusMinus;
      }
      $schedules[] = $item;
    }
    $objects = null;

    $res['type'] = "success";
    $res['message'] = "ログインユーザーの登録スケジュールを取得しました。";
    $res['list'] = $schedules;
  }
} catch (Exception $e) {
  echo $e->getMessage();
}

echo json_encode($res);

exit;
