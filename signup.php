<?php
ini_set('log_errors','on');
ini_set('error_log','php.log');

require('function.php');

//ログイン認証


//ポスト送信

if(!empty($_POST)){

//送信内容を変数に代入
 $email = $_POST['email'];
 $pass = $_POST['pass'];
 $pass_re = $_POST['pass_re'];

//バリデーション
//未入力
validRequired($email,'email');
validRequired($pass,'pass');
validRequired($pass_re,'pass_re');

if(empty($err_msg)){
  debug('未入力チェックOKです');

  //メール形式
   validEmail($email,'email');
  //最大文字数
  validMaxLen($email,'email');
  //半角英数字
  validHalf($pass,'pass');
  //最小文字数
  validMinLen($pass,'pass');
  //パスワード同値チェック
  validReEnter($pass,$pass_re,'pass_re');

   if(empty($err_msg)){
     debug('バリデーションチェックOK');
     validEmailDup($email);

     if(empty($err_msg)){
       debug('Emailに登録はありません。DBに接続します。');
         //例外処理
       try{
         $dbh = dbConnect();
         $sql= 'INSERT INTO users(email,password,login_time,create_date) VALUES(:email,:pass,:login_time,:create_date) ';
         $data = array(':email'=>$email,':pass'=>password_hash($pass, PASSWORD_DEFAULT),':login_time'=>date('Y-m-d,H:i:s'),':create_date'=>date('Y-m-d,H:i:s'));
         $stmt = queryPost($dbh, $sql, $data);

         if($stmt){
           debug('クエリ成功 マイページへ遷移します');
           //ここでセッションに色々詰める
           $sesLimit = 60 * 60;
           $_SESSION['login_date'] = time();
           $_SESSION['login_limit'] = $sesLimit;
           $_SESSION['user_id'] = $dbh->lastInsertId();
           debug('セッション変数の中身：'.print_r($_SESSION,true));


           header("Location:mypage.php");
           exit;
         }
       }catch(Exception $e){
         error_log('エラー発生'.$e->getMessage());
       }

     }
   }
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

     <div id="main">
       <div class="site-width">
         <h2 class="title">ユーザー登録画面</h2>
         <form class="form" action="" method="post">
           <div class="area-msg">
             <?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?>
           </div>
           <label class="<?php if(!empty($err_msg['email'])) echo 'err' ?>">
             メールアドレス
             <input type="text" class="input-bgColor" name="email" value="<?php if(!empty($_POST['email'])) echo sanitize($_POST['email']); ?>">
           </label >
           <div class="area-msg">
               <?php if(!empty($err_msg['email'])) echo $err_msg['email']; ?>
           </div>
           <label class="<?php if(!empty($err_msg['pass'])) echo 'err' ?>">
             パスワード（半角英数字 ６文字以上）
             <input type="password" class="input-bgColor" name="pass" value="<?php if(!empty($_POST['pass'])) echo sanitize($_POST['pass']); ?>">
           </label>
           <div class="area-msg">
             <?php if(!empty($err_msg['pass'])) echo $err_msg['pass']; ?>
           </div>
           <label class="<?php if(!empty($err_msg['pass_re'])) echo 'err' ?>">
             パスワード（再入力）
             <input type="password" class="input-bgColor" name="pass_re" value="<?php if(!empty($_POST['pass_re'])) echo sanitize($_POST['pass_re']); ?>">
           </label>
           <div class="area-msg">
             <?php if(!empty($err_msg['pass_re'])) echo $err_msg['pass_re']; ?>
           </div>

           <input type="submit" class="btn" name="" value="登録する">
         </form>
       </div>

     </div>
     <?php
     require('footer.php');
      ?>
