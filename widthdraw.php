<?php
require('function.php');
//ログイン認証
require('auth.php');

//POST
if(!empty($_POST)){
  debug('退会ボタンが押下されました');
  $widthdraw = $_POST['widthdraw'];

try{
  $dbh = dbConnect();
  $sql1= 'UPDATE contens SET delete_flg = 1 WHERE user_id = :u_id';
  $sql2= 'UPDATE users SET delete_flg = 1 WHERE id = :u_id';
  $data = array(':u_id'=>$_SESSION['user_id']);
  $stmt1 = queryPost($dbh, $sql1, $data);
  $stmt2 = queryPost($dbh, $sql2, $data);
if($stmt1 && $stmt2){
  debug('削除完了しました');
  debug('セッションを削除します');
  session_destroy();
  debug('セッション変数の中身：'.print_r($_SESSION,true));
  debug('画面遷移します');
  header("Location:signup.php");
  exit;
}else{
  debug('クエリが失敗しました。');
  $err_msg['common'] = MSG07;
}

}catch(Exception $e){
  error_log('エラー発生'.$e->getMessage());
  $err_msg['common'] = MSG07;

}

}

?>

<?php
require('head.php');
 ?>
<body>
  <?php
  require('header.php');
   ?>

<div id="main" style="min-height:600px;margin-top: 100px;">
<div class="border-style">
  <h1 style="text-align:center;margin-top:10px;">退会</h1>
  <form class="" action="" method="post">
    <input class="btn btn-middle js-withdraw-msg" type="submit" name="widthdraw" value="退会する">
  </form>
</div>

</div>
     <!-- フッター -->
     <?php
       require('footer.php');
      ?>
