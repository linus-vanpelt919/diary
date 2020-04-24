<?php
require('function.php');
//ログイン認証
require('auth.php');
debug('日記作成ページ');
//新規と編集画面を兼用する GETの有無で新規編集を判断する
//内容が違ってたらバリデーションをおこなう
//update文
// $u_id = $_SESSION['user_id'];
$edit_flg = (!empty($_GET)) ? true : false;//trueなら編集画面
debug('$edit_flg'.print_r($edit_flg,true));
if($edit_flg){//編集画面なら
    $d_id = $_GET['d_id'];
}
$dbFormData = getCategoryOne($d_id);
// $dbFormData = getContents($u_id); $d_idで判断したい
$categoryData = getCategoryData();//定義する場所によてエラーPOST送信の前に定義しないといけない

if(!empty($_POST)){//issetとどちらが適切か
debug('POST送信されました');
debug('POST情報：'.print_r($_POST,true));
debug('FILE情報：'.print_r($_FILES,true));
//値を変数に代入
$title = $_POST['title'];
$contents = $_POST['contents'];
$category = $_POST['category_id'];
$pic = (!empty($_FILES['pic']['name'])) ? uploadImg($_FILES['pic'],'pic'): '';
$pic = (empty($pic) && !empty($dbFormData['pic'])) ? $dbFormData['pic'] : $pic;
//バリデーション
if($editflg){//編集
  if($dbFormData['title'] !== $title){
    validRequired($title,'title');
    validLength($title,'title',$len = 30);
  }
  if($dbFormData['contents'] !== $contents){
    validRequired($contents,'contents');
    validLength($contents,'contents',$len = 500);
  }
  if($dbFormData['category_id'] !== $category){
    validSelect($category,'category_id');
  }

}else{//新規

  validRequired($title,'title');
  validRequired($contents,'contents');
  validLength($title,'title',$len = 30);
  validLength($contents,'contents',$len = 500);
  validSelect($category,'category_id');
}


if(empty($err_msg)){
  debug('バリデーションOKです');
  //例外処理
  try{
    $dbh = dbConnect();
  if($edit_flg){
    debug('DB更新です');
    $sql = 'UPDATE contens SET user_id = :user_id ,title = :title , category_id = :category, page = :page, pic = :pic,update_date = :update_date WHERE id = :d_id';
    $data = array(':user_id'=>$_SESSION['user_id'],':title'=>$title,':category'=>$category,':page'=>$contents,':pic'=>$pic,':update_date'=>date('Y-m-d H:i:s'),':d_id'=>$d_id);
    $stmt = queryPost($dbh, $sql, $data);
  }else{
    debug('DB登録です');
    $sql = 'INSERT INTO contens(user_id,title,category_id,page,pic,create_date,update_date) VALUES(:user_id,:title,:category,:page,:pic,:create_date,:update_date)';
    $data = array(':user_id'=>$_SESSION['user_id'],':title'=>$title,':category'=>$category,':page'=>$contents,':pic'=>$pic,':create_date'=>date('Y-m-d H:i:s'),':update_date'=>date('Y-m-d H:i:s'));
    $stmt = queryPost($dbh,$sql,$data);

  }
    //SQL成功マイページへ遷移
    if($stmt){
      debug('マイページへ遷移します');
      header("Location:mypage.php");
    }
  }catch(Exception $e){
    error_log('エラー発生'.$e->getMessage());
    $err_msg['common'] = MSG07;
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
     <form class="form diary-form" action="" method="post" enctype="multipart/form-data">
       <h1 class="diary-title"><?php echo ($edit_flg)? 'diary（編集）': 'diary（新規）'; ?></h1>
       <div class="area-msg">
         <?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?>
       </div>
       <label class="<?php if(!empty($err_msg['title'])) echo 'err' ?>">
         <input type="text" class="title-text input-bgColor" name="title" value="<?php echo getFormData('title'); ?>" placeholder="タイトル">
       </label>
       <div class="area-msg area-style">
         <?php if(!empty($err_msg['title'])) echo $err_msg['title']; ?>
       </div>
       <p class="category-title">カテゴリー   ＊必須</p>
       <div class="cp_ipselect cp_sl01 <?php if(!empty($err_msg['category_id'])) echo 'err' ?>">
         <select  name="category_id" class="input-bgColor">
             <option value="0"<?php if($dbFormData['category_id'] == 0){echo 'selected' ;} ?>>選択してください</option>
             <?php foreach ($categoryData as $key => $val): ?>
           <option value="<?php echo $val['id']; ?>" <?php if($dbFormData['category_id'] == $val['id']){ echo 'selected'; } ?>><?php echo $val['name']; ?></option>
         <?php endforeach; ?>
         </select>
       </div>
       <div class="area-msg area-style">
         <?php if(!empty($err_msg['category_id'])) echo $err_msg['category_id']; ?>
       </div>
       <label class="<?php if(!empty($err_msg['contents'])) echo 'err' ?>">
         <textarea name="contents" class="diary-text input-bgColor" id="js-text-count" rows="8" cols="80" placeholder="内容" ><?php if(!empty($_POST['contents'])) echo sanitize($_POST['contents']); ?><?php echo getFormData('page'); ?></textarea>
       </label>
       <div class="area-msg area-style">
         <?php if(!empty($err_msg['contents'])) echo $err_msg['contents']; ?>
       </div>
       <div class=" text-counter"><p class="right-span">
         <span class="show-count">0</span>/500</p></div>
         <div id="drag-drop-area">
           <div class="drag-drop-inside js-drag-area">
             <label class="img-cover">
               <input type="hidden" name="MAX_FILE_SIZE" value="3145728" />
               <div class="img-icon2">
                 <input type="file" id="fileInput" name="pic" value="ファイル選択" class="img-icon js-input-file">
                 <img src="" alt="" class="prev-img icon-img-size">
                 ドラッグドロップ
                 <br />or
                 <br />クリック
               </div>
             </label>
           </div>
         </div>
       <input type="submit" class="btn" name="" value="<?php echo ($edit_flg)? '編集する': '投稿する';  ?>">
     </form>



   </div>
  <!-- フッター -->
  <?php
    require('footer.php');
   ?>
