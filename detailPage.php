<?php
require('function.php');

require('auth.php');
//コンテンツ情報取得
//日記詳細ページのGETパラメータを表示
$d_id = (!empty($_GET['d_id']))? $_GET['d_id']: '';
//取得したGETパラメータを元にデータを表示する
$u_id = $_SESSION['user_id'];
//日記を見ている人のユーザーid

// $getContentInfo = getContents($u_id,$d_id);
$getContentInfo = getCategoryOne($d_id);
$category_name = $getContentInfo['name'];
$getPara = urlencode($d_id);//urlにgetを追加するときに文字化けしてしまうので文字化け防止用
$getUserComment = getCommentData($d_id);

//post 編集->headerで編集ページに遷移 updateで登録 or 削除->delete関数を実行する 実行前に必ず本当に記事を消していいか確認する
//編集ボタン
if(!empty($_POST['re_write'])){
  header("Location:writeDiary.php?d_id=".$getPara);//編集ページへ遷移 getパラを付与することができるのか urlencodeなら可能 文字化けを防ぐ
    exit;
}


//削除ボタン
if(!empty($_POST['delete_page'])){
  //例外処理
  try{
    //投稿を削除
    $dbh = dbConnect();
    $sql = 'DELETE FROM contens WHERE id = :d_id LIMIT 1';
    $data = array(':d_id'=>$d_id);
    $stmt = queryPost($dbh,$sql,$data);
    if($stmt){
      header("Location:mypage.php");
      exit;
    }
  }catch(Exception $e){
    error_log('エラー発生:'.$e->getMessage());
  }
}

//コメント送信ボタンが押された時
  if(!empty($_POST['submit'])){

    //変数に代入
    $comment = $_POST['comment'];
    $name = (!empty($_POST['name']))? $_POST['name'] : '名無し';
    $email = (!empty($_POST['email']))? $_POST['email'] : '';
    $from_user = $u_id;
     $to_user = $getContentInfo['user_id'];
      //今回は日記ページの持ち主を相手にする
      
    //バリデーション 未入力 今回必須なのはcommentのみ//何文字？
    validRequired($comment,'comment');
    validMaxLen($comment,'comment');
    validLength($name,'name');
    validMaxLen($email,'email');
//    validEmail($email,'email');

      if(empty($err_msg)){
        try{
             $dbh = dbConnect();
             $sql = 'INSERT INTO comment(bord_id,email,name,comment,to_user,from_user,create_date) VALUES(:b_id,:email,:name,:comment,:to_user,:from_user,:date)';
              $data = array(':b_id'=>$d_id,':email'=>$email,':name'=>$name,':comment'=>$comment,':to_user'=>$to_user,':from_user'=>$from_user,':date'=>date('Y-m-d H:i:s'));
              $stmt = queryPost($dbh,$sql,$data);
            if($stmt){
                $result = $stmt->fetch();
                //headerしなかったのでリロードするたびにDBに同じ情報が挿入されてしまった。
               header("Location:detailPage.php?d_id=".$getPara);
                exit;
            }
        }catch(Exception $e){
          error_log('エラー発生:'.$e->getMessage());
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
  <article class="blog-article">
    <h2 class="blog-title"><?php echo sanitize($getContentInfo['title']); ?></h2>
    <p class="blog-date"><?php echo sanitize($getContentInfo['update_date'].' '.$getContentInfo['name']); ?></p>
    <i class="fa fa-heart icon-style js-click-like fa-2x <?php if(isLike($u_id,$d_id)){echo 'active';}?>" data-diaryid="<?php echo $getContentInfo['id']; ?>" ></i>
    <article class="blog-img">
      <img src="<?php echo showImg(sanitize($getContentInfo['pic'])); ?>" alt="サンプル画像">
    </article>
    <article class="blog-detail">
      <p><?php echo sanitize($getContentInfo['page']); ?></p>
    </article>
    <!-- 編集削除ボタンはユーザーIdが一致する場合のみ表示 -->
    <?php if($u_id === $getContentInfo['user_id']): ?>
    <form class="btn-flex" action="" method="post">
    <input type="submit" class="btn btn-style" name="re_write" value="編集する">
    <input type="submit" class="btn btn-style js-alert-msg" name="delete_page" value="投稿を削除する">
    </form>
  <?php endif ;?>
  </article>
<!-- コメントがあった場合ここに表示する -->
  <div class="display-area">
        <?php if(!empty($getUserComment)): ?>
            <section class="comment-display-area">
                    <h2 class="comment-title">コメント</h2>
                <?php foreach($getUserComment as $key => $val): ?>
                 <div>
                  <div class="comment-flex">
                    <img src="<?php echo $val['pic'] ;?>" alt="" class="img-icon-style">
                    <div class="comment-info">
                      <p class="comment-user-name"><?php echo $val['name'] ;?>より:</p>
                      <p class="comment-user-date"><?php echo $val['create_date'] ;?></p>
                    </div>
                  </div>
                  <div class="comment-detail">
                    <p><?php echo $val['comment'] ;?></p>
                  </div>
                 <?php endforeach; ?>
                  </div>
            </section>
        <?php endif ;?>
    <div class="comment-area">
      <h2 class="comment-title">COMMENT</h2>
      <div class="area-msg area-style">
         <?php echo showErrMsg('common'); ?>
      </div>
          <form class="comment-form " action="" method="post">
               <label class="<?php if(!empty($err_msg['comment'])) echo 'err' ;?>">
                <span class="subject">コメント(必須)</span>
                <textarea name="comment" class="diary-text comment-height input-bgColor" rows="8" cols="80" placeholder="コメントを記入してください"></textarea>
               </label>
            <div class="area-msg area-style">
             <?php echo showErrMsg('comment'); ?>
            </div>
           <label class="<?php if(!empty($err_msg['name'])) echo 'err' ;?>">
                <span class="subject">名前 *任意</span>
                <input type="text" class="comment-text input-bgColor" name="name" value="">
           </label>
            <div class="area-msg area-style">
             <?php echo showErrMsg('name'); ?>
            </div>
           <label class="<?php if(!empty($err_msg['email'])) echo 'err' ;?>">
                <span class="subject">メールアドレス *任意 (メールアドレスが公開されることはありません)</span>
                <input type="text" class="comment-text input-bgColor" name="email" value="">
           </label>
            <div class="area-msg area-style">
                <?php echo showErrMsg('email'); ?>
            </div>
                <input type="submit" class="btn btn-right" name="submit" value="コメントする">
          </form>
    </div>
  </div>

<?php
require('footer.php');
?>
