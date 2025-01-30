<?php
include('../server/properties.php');
$page_name = "ログインページ";
/* セッション開始 */
session_start();
//セッションの中身をすべて削除
$_SESSION = array();

// 以下でリロード時にセッションを空にできる。
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content=<?php $contents_name . " | " . $page_name ?> />
  <meta name="keywords" content="" />
  <link rel="icon" href=<?php echo '../images/' . $favicon ?>>
  <?php echo $requiredLinks ?>
  <title><?php echo $contents_name . " | " . $page_name ?></title>
  <?php echo $requiredLinks ?>
  <style scoped>
    body {
      background-color: #080a40;
    }

    .headerIcon>.material-icons {
      color: #fff;
      cursor: pointer;
      padding: 3px;
      border: 2px solid #fff;
      border-radius: 5px;
    }

    .headerIcon>.material-icons:hover {
      color: yellow;
      border: 2px solid yellow;
      box-shadow: 0 0 10px #fdff80;
      text-shadow: 0 0 10px #fdff80;
    }

    .sloganSub,
    .sloganMain {
      margin-top: 5px;
      margin-bottom: 10px;
    }

    .sloganSub span {
      color: rgb(141, 0, 0);
      font-size: 25px;
      font-weight: 600;
      -webkit-text-stroke: 0.7px white;
    }

    .sloganMain span {
      font-size: 50px;
      padding: 5px 10px;
      color: white;
      font-weight: 600;
      -webkit-text-stroke: 2px rgb(141, 0, 0);
    }

    .fader {
      animation-name: fadeInAnime;
      animation-duration: 1s;
    }

    @keyframes fadeInAnime {
      from {
        opacity: 0
      }

      to {
        opacity: 1;
      }
    }

    #wrapper {
      margin: 0 auto;
      position: relative;
    }

    /* PC用 */
    @media only screen and (min-width:960px) {

      #wrapper,
      .inner {
        width: 80%;
        padding: 0;
      }

      #wrapper {
        padding-top: 20px;
        padding-bottom: 20px;
      }
    }

    @media only screen and (max-width: 480px) {
      .sloganSub span {
        font-size: 20px;
        -webkit-text-stroke: 0.5px white;
      }

      .sloganMain span {
        font-size: 45px;
        -webkit-text-stroke: 1.5px rgb(141, 0, 0);
      }

      #loginFormArea {
        padding: 1em;
      }
    }
  </style>
</head>

<body>
  <!-- Vue Area -->
  <div id="vueArea">
    <div :style="styles.mg1ems">
      <div class="headerIcon" align="center">
        <i class="material-icons" title="ユーザー新規登録画面にアクセスします" @click="jumpToRegist()">add</i>
      </div>
    </div>
    <!-- メッセージエリア -->
    <div id="messageArea" class="fader">
      <slogan>
        <template #sub>
          おヌシァ ここのユーザーか？
        </template>
        <template #main>フォームを埋めて 証明してみよ</template>
      </slogan>
    </div>

    <!-- 共通クロックエリア -->
    <div id="clockArea" class="fader"><digi-clock /></div>

    <!-- ログインフォームエリア -->
    <div id="wrapper">
      <div id="loginFormArea" class="fader">
        <login-card-sec>
          <template #title><tag-title><?php echo $contents_name . " " ?>ログインフォーム</tag-title></template>
          <template #contents>
            <v-text-field
              label="ログインID"
              placeholder="ログインIDを入力するのだ（メールアドレス形式）"
              v-model="auth.loginID"></v-text-field>
            <v-text-field
              label="パスワード" type="password"
              placeholder="パスワードを入力するのだ（半角英数混在 6文字以上16文字以内）"
              v-model="auth.password"></v-text-field>
            <div align="center">
              <p v-if="loginJudge == 'fail'" :style="styles.redPointer" @click="loginJudge = ''"><b>{{ message.loginFail }}</b></p>
              <p v-if="loginJudge == 'passError'" :style="styles.redPointer" @click="loginJudge = ''"><b>{{ message.passwordInvalid }}</b></p>
              <p v-if="loginJudge == 'stopped'" :style="styles.redPointer" @click="loginJudge = ''"><b>{{ message.stoppedAccount }}</b></p>
              <p v-if="loginJudge == 'success'" style="color:#0082ff;"><b>{{ message.loginSuccess }}</b></p>
              <br v-if="loginJudge.length > 0" />
              <v-btn
                :style="palette.brownFront"
                :disabled="auth.loginID=='' || auth.password=='' || loginJudge == 'success'"
                @click="loginAxios">
                ログイン
              </v-btn>
              <v-btn
                :style="palette.brownBack"
                :disabled="auth.loginID=='' || auth.password=='' || loginJudge == 'success'"
                @click="reset_authInput">
                リセット
              </v-btn>
            </div>
          </template>
        </login-card-sec>
      </div>
    </div>

  </div>
  <!-- Vue Area -->

  <script src="https://cdn.jsdelivr.net/npm/vue@2.x/dist/vue.js"></script>
  <!-- ↓ 非同期通信を実行するために必要 -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.18.0/axios.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.js"></script>
  <script type="module">
    import slogan from '../static/js/mod_slogan.js';
    import loginCardSection from '../static/js/mod_loginCardSection.js';
    import colorPalette from '../static/js/mod_colorPalette.js';
    import tagTitle from '../static/js/mod_tagTitle.js';
    import digiClock from '../static/js/mod_digiClock.js';

    // #vueForCommon内でVue.jsの機能を有効化する
    const login = new Vue({
      el: '#vueArea',
      vuetify: new Vuetify(),
      data: function() {
        return {
          headerObject: {
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded'
            },
          },
          palette: colorPalette,
          styles: {
            mg1ems: 'margin-top: 1em; margin-bottom: 1em;',
            redPointer: 'color:red;cursor:pointer;',
          },
          message: {
            loginSuccess: "ＯＫ！ログインIDとパスワードの認証に成功したぞ！",
            loginFail: "ログインIDかパスワードが間違っているようだぞ。",
            stoppedAccount: "そのアカウントは停止されているようだぞ。",
            passwordInvalid: "パスワードは形式通りに入力してくれ。",
            logoutConfirm: "ログアウトするぞ。問題ないか？",
          },
          auth: {
            loginID: "",
            password: "",
          },
          loginJudge: "",
        };
      },
      methods: {
        jumpToRegist() {
          // 新規会員登録画面に遷移する
          location.href = './regist.php';
        },
        reset_authInput() {
          // ログインフォームの入力値を初期化
          this.auth = {
            loginID: '',
            password: ''
          };
        },
        loginAxios() {
          // パスワードの形式が正しいかを判定する（不正ならば処理を打ち切る）
          let regex = new RegExp(/^(?=.*[a-zA-Z])(?=.*[0-9])[a-zA-Z0-9.?/-]{6,16}$/);
          if (!regex.test(this.auth.password)) {
            this.loginJudge = "passError";
            return;
          }
          // 入力されているログインIDとパスワードを送信する
          let data = {
            login_id: this.auth.loginID,
            password: this.auth.password,
          };

          // axiosでPHPのAPIにパラメータを送信する為、次のようにする
          let params = new URLSearchParams();
          Object.keys(data).forEach(function(key) {
            params.append(key, this[key]);
          }, data);

          // ajax通信実行
          axios
            .post('../server/api/loginJudge.php', params, this.headerObject)
            .then(response => {
              if (response.data == 1) {
                this.loginJudge = "success";
                setTimeout(function() {
                  const locationFor = "./alphanumeric_confirm.php";
                  location.href = locationFor;
                }, 3000);
              } else if (response.data == -1) {
                this.loginJudge = "stopped";
              } else {
                this.loginJudge = "fail";
              };
            }).catch(error => alert("通信に失敗しました。"));
        },
      },
    });
  </script>
</body>

</html>