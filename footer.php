<footer class="footer">
  Copyright <a href="">diary</a> application
</footer>
<script
src="https://code.jquery.com/jquery-3.4.1.min.js"
integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
crossorigin="anonymous"></script>

<script>
$(function(){
  //文字列カウンター
  $('#js-text-count').keyup(function(){
    var count = $(this).val().length;
    $('.show-count').text(count);
  });

//削除確認メッセージ
  $('.js-alert-msg').on('click',function(){
    return confirm("削除してもよろしいですか？");
  });
  $('.js-withdraw-msg').on('click',function(){
    return confirm("退会しますか？");
  });

  //お気に入り登録削除
   var $like,
       likeDiaryId;

  $like = $('.js-click-like') || null;
  likeDiaryId = $like.data('diaryid') || null;
  //取得できなかった時に処理が止まらないようにするため
  if(likeDiaryId !== null && likeDiaryId !== undefined){
    $like.on('click',function(){
       var $this = $(this);
       $.ajax({
         type: "POST",
         url: "ajaxLike.php",
         data: {diaryId: likeDiaryId}
       }).done(function(data){
         console.log('Ajax Success');
         $this.toggleClass('active');
       }).fail(function(msg){
         console.log('Ajax Eror');
       });
  });
 }
//画像ライブプレビュー
var $dropArea = $('.js-drag-area');
var $fileInput = $('.js-input-file');
$dropArea.on('dragover',function(e){
  e.stopPropagation();
  e.preventDefault();
  $(this).css('border','3px #8c908f dashed');
});
$dropArea.on('dragleave',function(e){
  e.stopPropagation();
  e.preventDefault();
  $(this).css('border','none');
});
$fileInput.on('change',function(e){
  $dropArea.css('border','none');
  var file = this.files[0];
  $img = $(this).siblings('.prev-img'),
  fileReader = new FileReader();

  fileReader.onload = function(event){
    $img.attr('src',event.target.result).show();
  };
  fileReader.readAsDataURL(file);

});

});
</script>
  </body>
</html>
