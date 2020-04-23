<?php
//ini_set('log_errors','on');
ini_set('log_errors','off');
ini_set('error_log','php.log');

//デバック
$debug_flg = true;
function debug($str){
 global $debug_flg;
    if(!empty($debug_flg)){
        error_log('デバック'.$str);
    }
}
//================================
// セッション準備・セッション有効期限を延ばす
//================================
session_save_path("/var/tmp/");
//セッションファイルの有効期限を設定
ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 30);
ini_set('session.cookie_lifetime ', 60*60*24*30);
session_start();
session_regenerate_id();

$err_msg = array();

define('MSG01','入力必須です');
define('MSG02','メール形式で入力してください');
define('MSG03','そのメールアドレスはすでに使われています');
define('MSG04','半角英数字で入力してください');
define('MSG05','パスワード（再入力）が違います');
define('MSG06','255文字以内で入力してください');
define('MSG07','エラーが発生しました。しばらく経ってからやり直してください。');
define('MSG08','6文字以上で入力してください');
define('MSG09','文字以内で入力してください');
define('MSG10','選択してください');
define('MSG11','パスワードまたはメールアドレスが違います');
define('SUC01','プロフィールが変更されました');


//メール形式
function validEmail($str,$key){
 if(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\?\*\[|\]%'=~^\{\}\/\+!#&\$\._-])*@([a-zA-Z0-9_-])+\.([a-zA-Z0-9\._-]+)+$/",$str)){
    global $err_msg;
     $err_msg[$key] = MSG02;
  }
}
//未入力
function validRequired($str,$key){
    if(empty($str)){
        global $err_msg;
        $err_msg[$key] = MSG01;
    }
}
//半角チェック
function validHalf($str,$key){
    if(!preg_match("/^[a-zA-Z0-9]+$/",$str)){
        global $err_msg;
        $err_msg[$key] = MSG04;
    }
}
//再入力チェック
function validReEnter($str1,$str2,$key){
    if($str1 !== $str2){
        global $err_msg;
        $err_msg[$key] = MSG05;
    }
}
//最大入力文字数チェック
function validMaxLen($str,$key,$max = 255){
if(mb_strlen($str) >$max){
   global $err_msg;
    $err_msg[$key] = MSG06;
 }
}
//最小入力文字数チェック
function validMinLen($str,$key,$min = 6){
 if(mb_strlen($str) < $min){
    global $err_msg;
    $err_msg[$key] = MSG08;
 }
}
//固定長チェック
function validLength($str, $key, $len = 30){
  if(mb_strlen($str > $len)){
    global $err_msg;
    $err_msg[$key] = $len . MSG09;
  }
}

//サニタイズ
function sanitize($str){
return htmlspecialchars($str,ENT_QUOTES);
}
//selectboxチェック
function validSelect($str, $key){
  if(!preg_match("/^[1-9]+$/", $str)){
    global $err_msg;
    $err_msg[$key] = MSG10;
  }
}
//メール重複
function validEmailDup($email){
    global $err_msg;
    try{
        $dbh = dbConnect();
        $sql= 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
        $data= array(':email'=>$email);
        $stmt = queryPost($dbh, $sql, $data);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
       if(!empty(array_shift($result))){
         $err_msg['email'] = MSG03;
       }
    }catch(Exception $e){
      error_log('エラー発生:'.$e->getMessage());

  }



}
//DB接続関数
function dbConnect(){
  //DBへの接続準備
    $db = parse_url($_SERVER['CLEARDB_DATABASE_URL']);
    $db['dbname'] = ltrim($db['path'], '/');
    $dsn = "mysql:host={$db['host']};dbname={$db['dbname']};charset=utf8";
    $user = $db['user'];
    $password = $db['pass'];
  //$dsn = 'mysql:dbname=diary;host=localhost;charset=utf8';
  //$user = 'root';
  //$password = 'root';
  $options = array(
    // SQL実行失敗時にはエラーコードのみ設定
    PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
    // デフォルトフェッチモードを連想配列形式に設定
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // バッファードクエリを使う(一度に結果セットをすべて取得し、サーバー負荷を軽減)
    // SELECTで得た結果に対してもrowCountメソッドを使えるようにする
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
  );
  // PDOオブジェクト生成（DBへ接続）
  $dbh = new PDO($dsn, $user, $password, $options);
  return $dbh;
}

function queryPost($dbh, $sql, $data){
  //クエリー作成
  $stmt = $dbh->prepare($sql);
  //プレースホルダに値をセットし、SQL文を実行
  if(!$stmt->execute($data)){
    $err_msg['common'] = MSG07;
    return 0;
  }
  return $stmt;
}
//ユーザーデータ取得関数
function getUsersData($u_id){
  try{
      $dbh = dbConnect();
      $sql = 'SELECT id,username,email,pic,comment,create_date,update_date FROM users WHERE id = :u_id AND delete_flg = 0';
      $data = array(':u_id'=>$u_id);
      $stmt = queryPost($dbh, $sql, $data);
      if($stmt){
        $result = $stmt->fetch();
          return $result;
      }

  }catch(Exception $e){
    error_log('エラー発生:'.$e->getMessage());
  }
}


//カテゴリーデータ取得
function getCategoryData(){
  global $err_msg;
  try{
    $dbh = dbConnect();
    $sql = 'SELECT id,name FROM category ';
    $data = array();
    $stmt = queryPost($dbh, $sql, $data);
    if($stmt){
      $result = $stmt->fetchAll();
      return $result;
    }else{
      return false;
    }

  }catch(Exception $e){
    error_log('エラー発生:'.$e->getMessage());
  }
}
//画像アップロード
function uploadImg($file,$key){
  //例外
try{
  if(isset($file['error']) && is_int($file['error'])){
    switch($file['error']){
      case UPLOAD_ERR_OK:
           break;
      case UPLOAD_ERR_INI_SIZE:
           throw new RuntimeException('ファイルサイズが大きすぎます');
           break;
      case UPLOAD_ERR_FORM_SIZE:
           throw new RuntimeException('アップロードされたファイルは指定されたMAX_FILE_SIZE を超えています。');
           break;
      case UPLOAD_ERR_PARTIAL:
           throw new RuntimeException('アップロードされたファイルは指定されたMAX_FILE_SIZE を超えています。');
           break;
      default:
           throw new RuntimeException('その他のエラーが発生しました');
           break;
    }
     $type = @exif_imagetype($file['tmp_name']);
     if(!in_array($type,[IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG], true)){
       throw new RuntimeException('画像形式が未対応です');
     }
     $path = 'uploads/'.sha1_file($file['tmp_name']).image_type_to_extension($type);
     if(!move_uploaded_file($file['tmp_name'],$path)){
       throw new RuntimeException('ファイル保存時にエラーが発生しました');
     }
     chmod($path,0644);
     //// 所有者に読み込み、書き込みの権限を与え、その他には読み込みだけ許可する。
     return $path;
  }

  }catch(RuntimeException $e){
    global $err_msg;
    $err_msg[$key] = $e->getMessage();
  }
}
//写真
function showImg($path){
  if(empty($path)){
    return 'img/sample-img.png';
  }else{
    return $path;
  }
}

function getContents($u_id){
  global $err_msg;
  try{
    $dbh = dbConnect();
    $sql = 'SELECT * FROM contens WHERE user_id = :u_id AND delete_flg = 0';
    $data = array(':u_id'=>$u_id);
    $stmt = queryPost($dbh,$sql,$data);
    if($stmt){
      $result = $stmt->fetchAll();
      return $result;
    }else{
      return false;
    }
  }catch(Exception $e){
    error_log('エラー発生:'.$e->getMessage());
  }
}

function getCategoryOne($d_id){//内部結合
  try{
    $dbh = dbConnect();
    $sql = 'SELECT c.id,c.user_id,c.title,c.category_id,c.page,c.pic,c.create_date,c.update_date,a.name FROM contens AS c LEFT OUTER JOIN category AS a ON c.category_id = a.id WHERE c.id = :d_id AND c.delete_flg = 0 AND a.delete_flg = 0';
    $data = array(':d_id'=>$d_id);
    $stmt = queryPost($dbh, $sql, $data);
    if($stmt){
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      return $result;
    }else{
      return false;
    }

  }catch(Exception $e){
      error_log('エラー発生:'.$e->getMessage());
    }
}

//カテゴリー名取得関数??
function getCategoryName($d_id){
  try{
  $dbh = dbConnect();
  $sql = 'SELECT c.id,c.category_id,a.name AS category FROM contens AS c LEFT JOIN category AS a ON c.category_id = a.id WHERE c.id = :d_id AND c.delete_flg = 0 AND a.delete_flg = 0';
  $data = array(':d_id'=>$d_id);
  $stmt = queryPost($dbh, $sql, $data);
  if($stmt){
    $result = $stmt->fetchAll();
    return $result;
  }else{
    return false;
  }

}catch(Exception $e){
    error_log('エラー発生:'.$e->getMessage());
  }
}

//日記表示件数取得 全ての日記が表示されるようになっているがアドレスがマイページだった場合には自分の記事だけ表示できるようにしたい $_SERVER['PHP_SELF']で条件付けする？
//if($_SERVER['PHP_SELF'] === 'mypage.php'){
  //  $sql = 'SELECT id FROM contens WHERE user_id = :u_id';
//}
function getPageList($currentMinNum = 1,$category,$date,$span = 4){
  try{//件数取得用
    $dbh = dbConnect();
    $sql = 'SELECT id FROM contens';//カテゴリー選択時の件数
    if(!empty($category)) $sql .= ' WHERE category_id = '.$category;
    if(!empty($date)){
      switch ($date) {//昇順降順は件数に影響しない
        case 1:
          $sql .= ' ORDER BY create_date ASC';//昇順
          break;
        case 2:
          $sql .= ' ORDER BY create_date DESC';//降順
          break;
      }
    }
    $data = array();
    $stmt = queryPost($dbh, $sql, $data);
    $rst['total'] = $stmt->rowCount();
    $rst['total_page'] = ceil($rst['total']/$span);
    if(!$stmt){
     return false;
   }
   // ページング用のSQL文作成
       $sql = 'SELECT c.id,c.user_id,c.title,c.category_id,c.page,c.pic,c.create_date,c.update_date,a.name FROM contens AS c LEFT OUTER JOIN category AS a ON c.category_id = a.id';
       //条件によってSQLを変えてあげる
       if(!empty($category)) $sql .= ' WHERE category_id = '.$category;
       if(!empty($date)){
         switch ($date) {
           case 1:
             $sql .= ' ORDER BY create_date ASC';//昇順　古いデータから
             break;
           case 2:
             $sql .= ' ORDER BY create_date DESC';//降順
             break;
         }
       }
       $sql .= ' LIMIT '. $span.' OFFSET '. $currentMinNum;
       // $stmt = $dbh->prepare($sql);
       // $stmt->bindValue(':span', $span, PDO::PARAM_INT);
       // $stmt->bindValue(':currentMinNum', $currentMinNum, PDO::PARAM_INT);
       $data = array();
    // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);
      if($stmt){
      // クエリ結果のデータを全レコードを格納
      $rst['data'] = $stmt->fetchAll();//fetch()だといちデータしか取れない
      return $rst;
    }else{
      return false;
    }
  }catch(Exception $e){
    error_log('エラー発生:' . $e->getMessage());
  }
}
//マイページ用件数表示関数
function getMypageList($u_id,$currentMinNum = 1,$category,$date,$span = 4){
  //件数取得
  //ページ表示
  //カテゴリー 表示順序判定
  try{
    $dbh = dbConnect();
    $sql = 'SELECT id FROM contens WHERE user_id = '.$u_id;
    if(!empty($category)) $sql .= ' AND category_id = '.$category;
    if(!empty($date)){
        switch ($date) {
          case 1://昇順
             $sql .= ' ORDER BY create_date ASC';
            break;

          case 2://降順
             $sql .= ' ORDER BY create_date DESC';
            break;
        }
    }
    $data = array();
    $stmt = queryPost($dbh, $sql, $data);
    $rst['total'] = $stmt->rowCount();
    $rst['total_page'] = ceil($rst['total']/$span);
    if(!$stmt){
     return false;
   }


    $sql = 'SELECT c.id,c.user_id,c.title,c.category_id,c.page,c.pic,c.create_date,c.update_date,c.delete_flg,a.name FROM contens AS c LEFT OUTER JOIN category AS a ON c.category_id = a.id WHERE user_id = :u_id';

    if(!empty($category)) $sql .= ' AND category_id = '.$category;
    if(!empty($date)){
      switch ($date) {
        case 1:
          $sql .= ' ORDER BY create_date ASC';//昇順　古いデータから
          break;
        case 2:
          $sql .= ' ORDER BY create_date DESC';//降順
          break;
      }
    }
    $sql .= ' LIMIT '. $span.' OFFSET '. $currentMinNum;

    $data = array(':u_id'=>$u_id);
    $stmt = queryPost($dbh, $sql, $data);

      if($stmt){
        $rst['data'] = $stmt->fetchAll();
        return $rst;
      }

  }catch(Exception $e){
    error_log('エラー発生'.$e->getMessage());
   }
  }

//フォーム入力保持
function getFormData($str,$flg = false){
  //動き確認のためデバック多めにつける
  if($flg){
    $method = $_GET;
  }else{
    $method = $_POST;//基本ポスト
  }
  global $err_msg;
global $dbFormData;
//$dbFormDataはDBから情報を取得する関数の名前として使っているそのページページで必要とする情報が違うので中身は毎回違う
//DBからデータを取ってくる
//ユーザデータあり
if(!empty($dbFormData)){
//エラーあり
  if(!empty($err_msg[$str])){
    //送信あり
    if(isset($method[$str])){
       return sanitize($method[$str]);
       //エラーあり送信なし 基本あり得ない
    }else{
       return sanitize($dbFormData[$str]);
    }

    //エラーなし
  }else{
    //送信データがあり、DBの情報と違う場合 isset($method[$str]) &&なしだとpostされてないのにPOST情報表示しなくてはいけないので開いた瞬間エラーになる
    if(isset($method[$str]) && isset($method[$str]) !== $dbFormData[$str]){
      return sanitize($method[$str]);
    }else{//DBデータ＝送信データ
      return sanitize($dbFormData[$str]);
     }
    }
  }else{//DBデータなし 送信を表示
      if(isset($method[$str])){
        return sanitize($method[$str]);
      }
   }
}
//エラーメッセージ表示関数
function showErrMsg($key){
  global $err_msg;
  if(!empty($err_msg[$key])){
    return $err_msg[$key];
  }
}


//ログイン有無
function isLogin(){
  if(!empty($_SESSION['login_date'])){

    if(($_SESSION['login_date'] + $_SESSION['login_limit']) < time()){
      session_destory();
      return false;
    }else{
      return true;
    }
  }else{
    return false;
  }
}
//お気に入り有無
function isLike($u_id,$d_id){
  try{
      $dbh = dbConnect();
      $sql = 'SELECT * FROM likes WHERE user_id = :u_id AND diary_id = :d_id';
      $data = array(':u_id'=>$u_id,':d_id'=>$d_id);
      $stmt = queryPost($dbh,$sql,$data);

      if($stmt->rowCount()){
        return true;
      }else{
        return false;
      }
  }catch(Exception $e){
    error_log('エラー発生'.$e->getMessage());
  }
}
//お気に入り記事取得関数
function getMyLikePage($u_id,$currentMinNum = 1,$category,$date,$span = 4){
//これだとページネーション 検索が反応しない
//全体の件数の取得 カテゴリー＆ソートSQLを変更する
  try{
    $dbh = dbConnect();
    $sql = 'SELECT c.id,c.user_id AS contens_u_id,c.title,c.category_id,c.page,c.pic,c.create_date,c.update_date,l.diary_id,l.user_id AS likes_u_id FROM contens AS c LEFT JOIN likes AS l ON c.id = l.diary_id WHERE l.user_id = :u_id AND c.delete_flg = 0';

    if(!empty($category)) $sql .= ' AND category_id = '.$category;
    if(!empty($date)){
      switch ($date) {
        case 1:
          $sql .= ' ORDER BY create_date ASC';//昇順　古いデータから
          break;
        case 2:
          $sql .= ' ORDER BY create_date DESC';//降順
          break;
      }
    }
    $sql .= ' LIMIT '. $span.' OFFSET '. $currentMinNum;

    $data = array(':u_id'=>$u_id);
    $stmt = queryPost($dbh,$sql,$data);
    $rst['total'] = $stmt->rowCount();
    $rst['total_page'] = ceil($rst['total']/$span);

    if($stmt){
      $rst['data'] = $stmt->fetchAll();
      return $rst;
    }else{
      return false;
    }

  }catch(Exception $e){
    error_log('エラー発生'.$e->getMessage());
  }
}
//カテゴリー名取得関数
function getCatName($c_name){
  switch ($c_name) {
    case 1:
      return 'プログラミング';
      break;
    case 2:
        return '趣味';
      break;
    case 3:
        return 'ペット';
      break;
    case 4:
        return '読書';
      break;
    case 5:
        return '日常';
      break;
    case 6:
        return 'その他';
      break;
    default:
        return '指定されてません';
      break;
  }
}

//コメントデータ取得
function getCommentData($d_id){
    try{
        $dbh = dbConnect();
        $sql ='SELECT c.id AS comment_id , c.bord_id, c.email, c.name, c.comment, c.to_user, c.from_user, c.create_date, u.id AS user_id, u.username, u.email, u.pic FROM comment AS c LEFT JOIN users AS u ON c.from_user = u.id WHERE c.bord_id = :d_id AND c.delete_flg = 0';
        $data = array(':d_id'=>$d_id);
        $stmt = queryPost($dbh,$sql,$data);
        if($stmt){
            $result = $stmt->fetchAll();
            return $result;
        }
    }catch(Exception $e){
        error_log('エラー発生'.$e->getMessage());
    }
}
//コメント一覧取得関数
function getCommentList($u_id){
    try{
        $dbh = dbConnect();
        $sql = 'SELECT * FROM comment WHERE to_user = :u_id AND delete_flg = 0 ORDER BY id DESC LIMIT 3';
        $data = array(':u_id'=>$u_id);
        $stmt = queryPost($dbh,$sql,$data);
        if($stmt){
            $result = $stmt->fetchAll();
            return $result;
        }
        
    }catch(Exception $e){
        error_log('エラー発生'.$e->getMessage());
    }
}