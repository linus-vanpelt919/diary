<div id="sidebar">
  <form class="" action="" method="get">
    <p class="category-name">カテゴリー</p>
    <select class="search-category search-style" name="c_id">
      <option value="0" <?php if(getFormData('c_id',true) == 0){echo 'selected'; }?> >選択してください</option>
      <?php foreach ($dbCategoryData as $key => $val): ?>
      <option value="<?php echo $val['id']; ?>"<?php if(getFormData('c_id',true) == $val['id']){echo 'selected'; }?>><?php echo $val['name']; ?></option>
    <?php endforeach; ?>
  </select><!-- getFormDataはただの入力保持 -->
    <p class="category-name">表示順</p>
    <select class="search-day search-style" name="date">
      <option value="0" <?php if(getFormData('date',true) == 0){echo 'selected';} ?> >選択してください</option>
      <option value="2"<?php if(getFormData('date',true) == 2){echo 'selected';} ?>>日付の新しい順</option>
      <option value="1"<?php if(getFormData('date',true) == 1){echo 'selected';} ?>>日付の古い順</option>
    </select>
    <button class="button" type="submit" name="">検索する</button>
  </form>
  <ul class="side-nav">
    <li><a href="writeDiary.php">日記を書く</a></li>
    <li><a href="mypage.php">マイページ</a></li>
    <li><a href="index.php">公開中の日記を見る</a></li>
    <li><a href="profEdit.php">プロフィール編集</a></li>
    <li><a href="pageLike.php">お気に入り一覧</a></li>
    <li><a href="#">パスワード変更</a></li>
    <li><a href="logout.php">ログアウト</a></li>
    <li><a href="widthdraw.php">退会</a></li>
  </ul>
  <div class="comment-list">
    <p>コメント一覧</p>
    <ul class="side-comment">
    <?php foreach($getNewComment as $key => $val) :?>
      <li><a href="detailPage.php?d_id=<?php echo $val['bord_id'] ;?>"><?php echo mb_substr($val['comment'],0,20); ?>
      <?php echo $val['name'] ;?>さまより</a></li>
      <?php endforeach ;?>
    </ul>
  </div>
</div>
