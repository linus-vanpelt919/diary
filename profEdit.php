<?php
require('function.php');
require('auth.php');
//================================
// 画面処理
//================================
//DBからデータを取ってくる
//データあったら表示入力保持はHTMLないのvalue
$u_id = $_SESSION['user_id'];
$dbFormData = getUsersData($u_id);
//取得できない

//ポスト送信
if(!empty($_POST)){
  //変数に代入
  $username =  $_POST['username'] ;
  $email = $_POST['email'];
  $comment = $_POST['comment'];
  $pic = (!empty($_FILES['pic']['name']))? uploadImg($_FILES['pic'],'pic'): '';
  $pic = (empty($pic) && (!empty($dbFormData['pic'])))? $dbFormData['pic'] : $pic;
  //内容違かったらバリデーション
  if($dbFormData['username'] !== $username){
    //特に制約はないが30文字以内に指定
    validLength('$username','username');
  }
  if($dbFormData['email'] !== $email){
    validRequired($email,'email');
    validEmail($email,'email');
    validMaxLen($email,'email');
    validEmailDup($email);
  }
  if($dbFormData['comment'] !== $comment){
    validMaxLen($comment,'comment',500);
  }

  if(empty($err_msg)){

    try{
      $dbh = dbConnect();
      $sql = 'UPDATE users SET username = :u_name,email = :email,pic = :pic,comment = :comment WHERE id = :u_id AND delete_flg = 0';
      $data = array(':u_name'=>$username,
                    ':email'=>$email,
                    ':pic'=>$pic,
                    ':comment'=>$comment,
                    ':u_id'=>$u_id);
      $stmt = queryPost($dbh,$sql,$data);

      if($stmt){
        //メッセージ表示したい
        header("Location:mypage.php");
        exit;
      }

    } catch(Exception $e){
      error_log('エラー発生'.$e->getMessage());
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
    <div class="comment-area">
      <h2 class="comment-title">プロフィール編集画面</h2>
      <div class="area-msg area-style">
        <?php echo showErrMsg('common'); ?>
      </div>
      <form class="comment-form" action="" method="post" enctype="multipart/form-data">
       <label class="<?php if(!empty($err_msg['username'])) echo 'err' ?>">
        <span class="subject">Username</span>
        <input type="text" name="username" class="comment-text input-bgColor" value="<?php echo getFormData('username'); ?>" placeholder="ニックネーム">
       </label>
        <div class="area-msg area-style">
          <?php echo showErrMsg('username'); ?>
        </div>
       <label class="<?php if(!empty($err_msg['email'])) echo 'err' ?>">
        <span class="subject">Email</span>
        <input type="text" name="email" class="comment-text input-bgColor" value="<?php echo getFormData('email'); ?>" placeholder="email">
       </label>
        <div class="area-msg area-style">
          <?php echo showErrMsg('email'); ?>
        </div>
        <label class="<?php if(!empty($err_msg['comment'])) echo 'err' ?>"> 
        <span class="subject">About yourself</span>
        <textarea name="comment" class="diary-text comment-height input-bgColor" rows="8" cols="80" placeholder="自己紹介など"><?php echo getFormData('comment'); ?></textarea>
        </label>
        <div class="area-msg area-style">
          <?php echo showErrMsg('comment'); ?>
        </div>
        <div class=" text-counter">
          <p class="right-span">
          <span class="show-count">0</span>/500</p></div>
          <div class="area-msg area-style">
            <?php echo showErrMsg('pic'); ?>
          </div>
        <div id="drag-drop-area">
          <div class="drag-drop-inside js-drag-area">
            <label class="img-cover">
              <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
              <div class="img-icon2">
                <input type="file" id="fileInput" name="pic" value="ファイル選択" class="img-icon js-input-file input-bgColor">
                <img src="<?php echo getFormData('pic'); ?>" alt="" class="prev-img icon-img-size">
                ドラッグドロップ
                <br />or
                <br />クリック
              </div>
            </label>
          </div>
        </div>
        <input type="submit" class="btn btn-right" name="" value="登録する">
      </form>
    </div>
  </div>


<?php
  require('footer.php');
  ?>
