<?php

require('function.php');
require('auth.php');

$currentPageNum = (!empty($_GET['p'])) ? $_GET['p'] : 1;
//1の時は０,１,２,３ GETが２になったら次の四つをページに表示する
$u_id = $_SESSION['user_id'];
//コンテンツ情報を取得 ->これだとページネーションが働かない
$getContentInfo = getContents($u_id);
$category = (!empty($_GET['c_id']))? $_GET['c_id'] : '';
$date = (!empty($_GET['date']))? $_GET['date'] : '';
//トータルデータgetPageList['total'] トータルページ数 getPageList['total_page']
//表示件数
$listSpan = 4;
//先頭を算出
$currentMinNum = (($currentPageNum-1)*$listSpan);
//
$dbCategoryData = getCategoryData();


// $dbPageData = getMypageList($u_id,$currentMinNum,$category,$date);


//お気に入り取得
$dbPageData = getMyLikePage($u_id,$currentMinNum,$category,$date);
?>

<?php
require('head.php');
?>



  <body>
    <?php
    require('header.php');
    ?>
    <div id="main">
      <div class="title-main">
        <h1>お気に入り一覧</h1>
      </div>
      <!-- サイドバー -->
       <?php
        require('sidebar.php');
       ?>

       <!-- foreach ($getContentInfo as $key => $val) だとLIMIT OFFSET していないので全データが表示されてしまう-->
      <?php foreach ($dbPageData['data'] as $key => $val) :?>
       <section>
        <div class="container">
          <div class="img-container">
            <img src="<?php echo showImg(sanitize($val['pic'])); ?>" alt="イメージ画像">
          </div>
          <div class="main-container">
            <div class="title-container">
              <h2><?php echo sanitize($val['title']); ?></h2>
            </div>
            <div class="category-container">
              <span class="category-bgColor"><?php echo sanitize($val['create_date']); ?> <a href="index.php?c_id=<?php echo $val['category_id']; ?>"><?php echo getCatName($val['category_id']); ?></a></span>
            </div>
            <div class="text-container">
              <p><?php echo sanitize(mb_substr($val['page'],0,50)); ?></p>
            </div>
            <p class="entry-read"><a href="detailPage.php?d_id=<?php echo  $val['id']; ?>">Read more</a></p>
          </div>
        </div>
       </section>
     <?php endforeach; ?>


<!-- ページネーション -->
     <ul class="pagenation">
         <?php
              $pageColNum = 5;
              $totalPageNum = $dbPageData['total_page'];
              // 現在のページが、総ページ数と同じ　かつ　総ページ数が表示項目数以上なら、左にリンク４個出す
              if( $currentPageNum == $totalPageNum && $totalPageNum >= $pageColNum){
                $minPageNum = $currentPageNum - 4;
                $maxPageNum = $currentPageNum;
              // 現在のページが、総ページ数の１ページ前なら、左にリンク３個、右に１個出す
              }elseif( $currentPageNum == ($totalPageNum-1) && $totalPageNum >= $pageColNum){
                $minPageNum = $currentPageNum - 3;
                $maxPageNum = $currentPageNum + 1;
              // 現ページが2の場合は左にリンク１個、右にリンク３個だす。
              }elseif( $currentPageNum == 2 && $totalPageNum >= $pageColNum){
                $minPageNum = $currentPageNum - 1;
                $maxPageNum = $currentPageNum + 3;
              // 現ページが1の場合は左に何も出さない。右に５個出す。
              }elseif( $currentPageNum == 1 && $totalPageNum >= $pageColNum){
                $minPageNum = $currentPageNum;
                $maxPageNum = 5;
              // 総ページ数が表示項目数より少ない場合は、総ページ数をループのMax、ループのMinを１に設定
              }elseif($totalPageNum < $pageColNum){
                $minPageNum = 1;
                $maxPageNum = $totalPageNum;
              // それ以外は左に２個出す。
              }else{
                $minPageNum = $currentPageNum - 2;
                $maxPageNum = $currentPageNum + 2;
              }
            ?>
            <?php if($currentPageNum != 1): ?>
              <li class="page page-style"><a href="?p=1">&lt;</a></li>
            <?php endif; ?>
            <?php
               for($i = $minPageNum; $i <= $maxPageNum; $i++):
             ?>
             <li class="page page-style <?php if($currentPageNum == $i) echo 'active'; ?>"><a href="?p=<?php echo $i; ?>"><?php echo $i; ?></a></li>
           <?php
              endfor;
           ?>
           <?php if($currentPageNum != $maxPageNum): ?>
             <li class="page page-style"><a href="?p=<?php echo $maxPageNum; ?>">&gt;</a></li>
           <?php endif; ?>

</ul>
    </div>

<?php
require('footer.php');
 ?>
