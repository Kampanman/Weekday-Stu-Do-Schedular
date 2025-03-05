<?php

/**
 * PHP関数機能集
 */

// htmlでのエスケープ処理
function h($var)
{
  if (is_array($var)) {
    return array_map('h', $var);
  } else {
    return htmlspecialchars($var, ENT_QUOTES, 'UTF-8');
  }
}

// パスワードのハッシュ化
function hashpass($pass)
{
  // ハッシュ処理の計算コストを指定する
  $options = array('cost' => 10);
  // 方式にPASSWORD_DEFAULTを指定してハッシュ化したパスワードを返す
  return password_hash($pass, PASSWORD_DEFAULT, $options);
}

// 格納用の配列を生成してそちらにオブジェクトを格納する
function getSchedulesArray($objects)
{
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

  return $schedules;
}
