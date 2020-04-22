<?php
//ログイン認証
// $_SESSION['login_limit']
// $_SESSION['login_time']
// $_SESSION['user_id']

//session_start()

 //セッションが有効期限内かどうか
if(!empty($_SESSION['login_date'])){
//有効期限チェック
  if($_SESSION['login_date'] + $_SESSION['login_limit'] < time()){
    session_destroy();
    header("Location:login.php");
    exit;
  }else{
    //セッションを延長
    $_SESSION['login_date'] = time();
    if(basename($_SERVER['PHP_SELF'] === 'mypage.php')){
      header("Location:mypage.php");
      exit;
    }
  }
}else{
  if(basename($_SERVER['PHP_SELF']) !== 'login.php'){
    header("Location:login.php");
    exit;
  }
}
