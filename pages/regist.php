<?php
include('../server/properties.php');
$page_name = "会員登録ページ";
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
  <title><?php echo $contents_name . " | " . $page_name ?></title>
  <?php echo $requiredLinks ?>
  <style scoped>
    body,
    .v-application {
      background-color: #080a40 !important;
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
    <v-app>
      <div :style="styles.mg1ems">
        <div :style="styles.mg1ems">
          <div class="headerIcon" align="center">
            <i class="material-icons" title="ログイン画面に戻ります" @click="returnToLogin()">undo</i>
          </div>
        </div>
      </div>
      <!-- メッセージエリア -->
      <div id="messageArea" class="fader">
        <slogan>
          <template #sub>
            <?php echo $contents_name ?>
          </template>
          <template #main><?php echo $page_name ?></template>
        </slogan>
      </div>

      <!-- 共通クロックエリア -->
      <div id="clockArea" class="fader"><digi-clock /></div>

      <!-- 会員登録フォームエリア -->
      <div id="wrapper">
        <div id="registFormArea" class="fader">
          <card-sec>
            <template #title><tag-title><?php echo $contents_name . " " ?>会員登録フォーム</tag-title></template>
            <template #contents>
              <v-text-field
                label="ユーザー名" max="30"
                placeholder="ユーザー名を入力するのだ（30文字以内）"
                v-model="form.name"></v-text-field>
              <v-text-field
                label="ログインID"
                placeholder="ログインIDを入力するのだ（メールアドレス形式）"
                v-model="form.login_id"></v-text-field>
              <v-text-field
                label="パスワード" type="password"
                placeholder="パスワードを入力するのだ（半角英数混在 6文字以上16文字以内）"
                v-model="form.password"></v-text-field>
              <v-text-field
                label="コメント" max="100"
                placeholder="何かコメントしたいことがあれば記入しといて（100文字以内）"
                v-model="form.comment"></v-text-field>
              <div align="center">
                <p v-if="registJudge == 'mailError'" :style="styles.redPointer" @click="registJudge = ''"><b>{{ message.loginIdInvalid }}</b></p>
                <p v-if="registJudge == 'passError'" :style="styles.redPointer" @click="registJudge = ''"><b>{{ message.passwordInvalid }}</b></p>
                <p v-if="registJudge == 'fail'" :style="styles.redPointer" @click="registJudge = ''"><b>{{ message.axiosFail }}</b></p>
                <p v-if="registJudge == 'success'" style="color:#0082ff;"><b>{{ message.registSuccess }}</b></p>
                <br v-if="registJudge.length > 0" />
                <v-btn
                  :style="palette.brownFront"
                  :disabled="form.name == '' || form.login_id == '' || form.password == '' || registJudge == 'success'"
                  @click="confirmRegist()">登録</v-btn>
                <v-btn
                  :style="palette.brownBack"
                  :disabled="form.name == '' || form.login_id == '' || form.password == '' || registJudge == 'success'"
                  @click="reset_formInput">リセット</v-btn>
              </div>
            </template>
          </card-sec>
        </div>
      </div>

      <!-- 登録内容確認ダイアログ -->
      <div class="dialogParts">
        <dialog-frame :target="dialog.instance.confirm" :title="'登録実行確認'" :contents="dialog.phrase.confirm">
          <v-btn @click="registAxios" :style="palette.brownFront">OK</v-btn>
          <v-btn @click="dialog.instance.confirm = false" :style="palette.brownBack">キャンセル</v-btn>
        </dialog-frame>
      </div>
    </v-app>
  </div>
  <!-- Vue Area -->

  <script src="https://cdn.jsdelivr.net/npm/vue@2.x/dist/vue.js"></script>
  <!-- ↓ 非同期通信を実行するために必要 -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.18.0/axios.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.js"></script>
  <script type="module">
    import slogan from '../static/js/mod_slogan.js';
    import cardSection from '../static/js/mod_cardSection.js';
    import colorPalette from '../static/js/mod_colorPalette.js';
    import tagTitle from '../static/js/mod_tagTitle.js';
    import digiClock from '../static/js/mod_digiClock.js';
    import dialogFrame from '../static/js/mod_dialogFrame.js';

    // #vueForCommon内でVue.jsの機能を有効化する
    const login = new Vue({
      el: '#vueArea',
      vuetify: new Vuetify(),
      data: function() {
        return {
          dialog: {
            instance: {
              confirm: false,
            },
            phrase: {
              confirm: "新規登録を実行します。よろしいですか？",
            },
          },
          form: {
            name: '',
            login_id: '',
            password: '',
            comment: '',
          },
          headerObject: {
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded'
            },
          },
          message: {
            registSuccess: "ＯＫ！会員登録に成功！ようこそ！",
            loginIdInvalid: "ログインIDはメールアドレスの形式通りに入力してくれ。",
            passwordInvalid: "パスワードは形式通りに入力してくれ。",
            axiosFail: "",
          },
          palette: colorPalette,
          registJudge: '',
          styles: {
            mg1ems: 'margin-top: 1em; margin-bottom: 1em;',
            redPointer: 'color:red;cursor:pointer;',
          },
        };
      },
      methods: {
        returnToLogin() {
          history.back();
        },
        confirmRegist() {
          this.dialog.instance.confirm = true;
        },
        reset_formInput() {
          // ログインフォームの入力値と非同期通信後失敗メッセージを初期化
          this.form = {
            name: '',
            login_id: '',
            password: '',
            comment: '',
          };
          this.message.axiosFail = '';
        },
        registAxios() {
          // login_idがメールアドレス形式になっているかを判定する（不正ならば処理を打ち切る）
          const emailRegex = new RegExp(/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/);
          if (!emailRegex.test(this.form.login_id)) {
            this.registJudge = "mailError";
            return;
          }

          // passwordの形式が正しいかを判定する（不正ならば処理を打ち切る）
          const passRegex = new RegExp(/^(?=.*[a-zA-Z])(?=.*[0-9])[a-zA-Z0-9.?/-]{6,16}$/);
          if (!passRegex.test(this.form.password)) {
            this.registJudge = "passError";
            return;
          }

          // 入力されている内容を送信する
          let data = {
            name: this.form.name,
            login_id: this.form.login_id,
            password: this.form.password,
            comment: this.form.comment,
          };

          // axiosでPHPのAPIにパラメータを送信する為、次のようにする
          let params = new URLSearchParams();
          Object.keys(data).forEach(function(key) {
            params.append(key, this[key]);
          }, data);

          // ajax通信実行
          axios
            .post('../server/api/accountRegist.php', params, this.headerObject)
            .then(response => {
              this.dialog.instance.confirm = false;
              if (response.data.type == 'fail') {
                this.registJudge = 'fail';
                this.message.axiosFail = response.data.message + 'ようだ。';
              } else {
                this.registJudge = 'success';
                setTimeout(function() {
                  const locationFor = "./login.php";
                  location.href = locationFor;
                }, 3000);
              }
            }).catch(error => alert("通信に失敗しました。"));
        },
      },
    });
  </script>
</body>

</html>