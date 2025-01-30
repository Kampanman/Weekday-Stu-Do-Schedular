// トップへ戻るボタン
var pageTopStyle =
  "position: fixed;"+
  "bottom: 2%;"+
  "right: 3%;"+
  "width: 50px;"+
  "height: 50px;";
var linkStyle =
  "border-radius: 50%;"+
  "background: #006e9f;"+
  "color: #fff;"+
  "line-height: 50px;"+
  "padding: 10px;"+
  "text-decoration: none;";

$('body').append(
  `<div id='page-top' style='${pageTopStyle}'><a href='#top' style='${linkStyle}'>↑TOP↑</a></div>`
);

// TOPに戻るボタン
  var topBtn = $('#page-top');
  topBtn.hide();

  //スクロールが500に達したらボタン表示
  $(window).scroll(function () {
      ($(this).scrollTop() > 500) ? topBtn.fadeIn() : topBtn.fadeOut();
  });

  //スムーススクロールでページトップへ
  topBtn.click(function () {
      $('body,html').animate({
          scrollTop: 0
      }, 500);
      return false;
  });