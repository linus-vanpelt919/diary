<header class="header">
  <div class="site__width">
    <h1 class="title-top"><a href="mypage.php">diary</a></h1>
    <nav id="top-nav">
      <ul class="header-list">
        <?php  //ユーザーIdの有無
        if(empty($_SESSION['user_id'])){
          ?>
          <li><a href="signup.php">ユーザー登録</a></li>
          <li><a href="login.php">ログイン</a></li>
          <?php
        }else{
          ?>
          <li><a href="mypage.php">マイページ</a></li>
          <li><a href="logout.php">ログアウト</a></li>
          <?php
        }
        ?>
      </ul>
    </nav>
  </div>
</header>
