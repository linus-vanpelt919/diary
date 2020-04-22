<?php

require('function.php');

//ログイン認証
require('auth.php');
//ポスト送信
if(!empty($_POST)){
 //値を変数に代入
 $email = $_POST['email'];
 $pass = $_POST['pass'];
 $login_keep = (!empty($_POST['login_keep'])) ? true : false ;
  //バリチェ
  //メール 未入力 email max
  //パス 未入力 min 半角 max
   validRequired($email,'email');
   validRequired($pass,'pass');

   if(empty($err_msg)){

     validEmail($email,'email');
     validMaxLen($email,'email');
     validHalf($pass,'pass');
     validMinLen($pass,'pass');
     validMaxLen($pass,'pass');

     //DB接続
     if(empty($err_msg)){

      try{
         $dbh = dbConnect();
         $sql = 'SELECT password ,id FROM users WHERE email = :email AND delete_flg = 0';
         $data = array(':email'=>$email);
       $stmt = queryPost($dbh, $sql, $data);
       $result = $stmt->fetch(PDO::FETCH_ASSOC);
      //  //$result['password']でも可
       if(!empty($result) && password_verify($pass,array_shift($result))){
         $sesLimit = 60*60;
         $_SESSION['login_date'] = time();

       if($login_keep){
         $_SESSION['login_limit'] = $sesLimit * 24 * 30;//30日

       }else{
         $_SESSION['login_limit'] = $sesLimit;//1h
       }
         //ユーザーIDを格納
         $_SESSION['user_id'] = $result['id'];
         header("Location:mypage.php");


       }else{
         $err_msg['common'] = MSG11;
       }

      }catch(Exception $e){
        error_log('エラー発生'.$e->getMessage());

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
         <h2 class="title">ログイン画面</h2>
         <form class="form" action="" method="post">
           <div class="area-msg">
             <?php
              if(!empty($err_msg['common'])) echo $err_msg['common'];
             ?>
           </div>
           <label class="<?php if(!empty($err_msg['email'])) echo 'err'; ?>">
             メールアドレス
             <input type="text" class="input-bgColor" name="email" value="<?php if(!empty($err_msg['email'])) echo sanitize($_POST['email']); ?>">
           </label>
           <div class="area-msg">
             <?php
              if(!empty($err_msg['email'])) echo $err_msg['email'];
             ?>
           </div>
           <label class="<?php if(!empty($err_msg['pass'])) echo 'err'; ?>">
             パスワード
             <input type="password" class="input-bgColor" name="pass" value="<?php if(!empty($err_msg['pass'])) echo sanitize($_POST['pass']); ?>">
           </label>
           <div class="area-msg">
             <?php
              if(!empty($err_msg['pass'])) echo $err_msg['pass'];
             ?>
           </div>
           <input type="checkbox" name="login_keep" >ログインしたままにする
           <input type="submit" class="btn" name="" value="ログイン">
         </form>
       </div>

     </div>
     <?php
     require('footer.php');
      ?>
