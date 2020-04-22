<?php
require('function.php');

//詳細ページはお気に入りボタンを押したときに自動ログアウトしないようにauth.phpはrequireしない


//ログインチェック
if(isset($_POST['diaryId']) && isset($_SESSION['user_id']) &&isLogin()){
  $d_id = $_POST['diaryId'];
  $u_id = $_SESSION['user_id'];
  try{
    $dbh = dbConnect();
    $sql = 'SELECT * FROM likes WHERE diary_id = :d_id AND user_id = :u_id';
    $data = array(':d_id'=>$d_id,':u_id'=>$u_id);
    $stmt = queryPost($dbh,$sql,$data);
    $resulyCount = $stmt->rowCount();
    if(!empty($resulyCount)){
      $sql = 'DELETE FROM likes WHERE diary_id = :d_id AND user_id = :u_id';
      $data = array(':d_id'=>$d_id,':u_id'=>$u_id);
      $stmt = queryPost($dbh, $sql, $data);
    }else{
      $sql = 'INSERT INTO likes (diary_id,user_id,create_date) VALUES(:d_id,:u_id,:date)';
      $data = array(':d_id'=>$d_id,':u_id'=>$u_id,':date'=>date('Y-m-d H:i:s'));
      $stmt = queryPost($dbh, $sql, $data);
    }
  }catch(Exception $e){
    error_log('エラー発生'.$e->getMessage());
  }
}
?>

 ?>
