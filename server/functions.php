<?php
/**
 * PHP関数機能集
 */

// htmlでのエスケープ処理
function h($var){
  if(is_array($var)){
    return array_map('h', $var);
  }else{
    return htmlspecialchars($var, ENT_QUOTES, 'UTF-8');
  }
}

// パスワードのハッシュ化
function hashpass($pass){
  // ハッシュ処理の計算コストを指定する
  $options = array('cost' => 10);
  // 方式にPASSWORD_DEFAULTを指定してハッシュ化したパスワードを返す
  return password_hash($pass, PASSWORD_DEFAULT, $options);
}
