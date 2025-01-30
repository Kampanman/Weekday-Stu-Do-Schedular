<?php
include("../../server/properties.php");
include("../../server/functions.php");
include("../../server/db.php");
$page_name = "マイページ";

/* セッション開始 */
session_start();
if (!isset($_SESSION["inSession"]) || $_SESSION["contents"] != $contents_name) {
  // 当ページは表示せずログインページに遷移
  header('Location: ../login.php');
  exit;
} else {
  $user = $_SESSION["name"];
}

?>
<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content=<?php echo $contents_name . " | " . $page_name ?> />
  <link rel="icon" href=<?php echo '../../images/' . $favicon ?>>
  <title><?php echo $contents_name . " | " . $page_name ?></title>
  <script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>
  <?php echo $requiredLinks ?>
  <link rel="stylesheet" href="<?php echo $staticPathFromMypage . '/css/omp_common.css' ?>">
  <link rel="stylesheet" href="<?php echo $staticPathFromMypage . '/css/omp_settings_styles.css' ?>">
  <style>
    [v-cloak] {
      /* これを設定してv-showと組み合わせることで、mountedが完了するまで対象のエリアを非表示にできる */
      display: none;
    }
  </style>
</head>

<body>
  <div id="vueArea">
    <!-- Vuetifyコンポーネント適用範囲エリア（<v-app>をグローバルに設定しないとコンポーネントの挙動がおかしくなる） -->
    <v-app>
      <!-- ヘッダーエリア -->
      <header>
        <section id="headerAbove">
          <div id="usersetArea">
            <div class="headerIcon">
              <i class="material-icons" title="スケジュールの照会と編集をします" @click="jumpToIndex()">home</i>
            </div>
          </div>
          <div id="logoArea">
            <img src="<?php echo '../../images/' . $bunner ?>" alt="タイトルロゴ" class="logo">
          </div>
          <div id="logoutArea">
            <div class="headerIcon">
              <i class="material-icons" title="ログアウトします" @click="confirmLogout()">logout</i>
            </div>
          </div>
        </section>
        <section id="headerBottom">
          <p>ログインユーザー: <?php echo $_SESSION['name'] ?></p>
          <p>（ログインID: <?php echo $_SESSION['login_id'] ?>）</p>
        </section>
      </header>

      <!-- 共通クロックエリア -->
      <div id="clockArea"><digi-clock clock-color="#4260ff" /></div>

      <!-- メインエリア -->
      <main v-show="isReady" v-cloak>
        <h1 :class="isReady ? 'fader': ''">ユーザー情報設定ページ</h1><br />

        <!-- 表示中コンテンツのタイトル・切替ボタン -->
        <section id="toggleArea" v-if="account_id == 1">
          <button
            @click="toggleState"
            :class="['v-btn', 'toggle-button', currentState == 1 ? 't-blue' : 't-white', isReady ? 'fader': '']">
            {{ currentState == 1 ? '他ユーザーのステータスチェック' : 'マイアカウントの編集' }}
          </button>
        </section>

        <!-- 会員情報編集フォーム -->
        <div id="editFormArea" class="fader" v-if="currentState == 1">
          <card-sec :border-setting="'1px solid #0600bf'">
            <template #title><tag-title>会員情報編集フォーム</tag-title></template>
            <template #contents>
              <div id="changePasswordBtnArea">
                <label>パスワードを編集: </label>
                <v-btn v-if="!form.editable.password" @click="form.editable.password = true" class="toggle-button t-blue">する</v-btn>
                <v-btn v-if="form.editable.password" @click="form.editable.password = false" class="toggle-button t-white">せぬ</v-btn>
              </div>
              <v-text-field
                label="ユーザー名" max="30"
                placeholder="ユーザー名を入力するのだ（30文字以内）"
                v-model="form.name"></v-text-field>
              <v-text-field
                label="ログインID"
                placeholder="ログインIDを入力するのだ（メールアドレス形式）"
                v-model="form.login_id"></v-text-field>
              <v-text-field
                label="コメント" max="100"
                placeholder="何かコメントしたいことがあれば記入しといて（100文字以内）"
                v-model="form.comment"></v-text-field>
              <v-text-field v-if="form.editable.password"
                label="パスワード" type="password"
                placeholder="パスワードを入力するのだ（半角英数混在 6文字以上16文字以内）"
                v-model="form.password"></v-text-field>
              <div align="center">
                <p></p>
                <small>※ 更新成功後、セッションリセットが必要な為ログアウトします。</small>
                <p></p>
                <v-btn
                  :style="palette.brownFront"
                  :disabled="form.name == '' || form.login_id == '' || updateJudge == 'success'"
                  @click="confirmUpdate()">更新</v-btn>
                <v-btn
                  :style="palette.brownBack"
                  :disabled="updateJudge == 'success'"
                  @click="reset_formInput">リセット</v-btn>
              </div>
            </template>
          </card-sec>
          <p v-if="account_id != 1"></p>
          <card-sec :border-setting="'2px solid red'" v-if="account_id != 1">
            <template #title><tag-title>Danger Zone</tag-title></template>
            <template #contents>
              <div id="dangerZoneInner" align="center">
                <v-btn
                  :style="palette.redBack + 'margin: 5px; width: 90%;'"
                  @click="confirmDelete(0)">登録スケジュールの全件削除</v-btn>
                <v-btn
                  v-if="account_id != 1"
                  :style="palette.redFront + 'margin: 5px; width: 90%;'"
                  @click="confirmDelete(1)">アカウント削除（サービス脱退）</v-btn>
              </div>
            </template>
          </card-sec>
        </div>

        <!-- アカウント＆停止状態一覧テーブル -->
        <div id="accountTableArea" class="fader" v-if="currentState == 2">
          <card-sec :border-setting="'1px solid #0600bf'">
            <template #title><tag-title>登録アカウント一覧</tag-title></template>
            <template #contents>
              <table-account :id="account_id" @get-changed="completeStopOrRestart"></table-account>
            </template>
          </card-sec>
        </div>

        <!-- 更新確認ダイアログ -->
        <div class="dialogParts">
          <dialog-frame :target="dialog.instance.updateConfirm" :title="'更新確認'" :contents="dialog.phrase.updateConfirm">
            <v-btn @click="doUpdate" :style="palette.brownFront">OK</v-btn>
            <v-btn @click="dialog.instance.updateConfirm = false" :style="palette.brownBack">キャンセル</v-btn>
          </dialog-frame>
        </div>

        <!-- 更新完了or失敗ダイアログ -->
        <div class="dialogParts">
          <dialog-frame :target="dialog.instance.updateComplete" :title="dialog.judge.updateComplete ? '更新完了' : 'エラー'"
            :contents="dialog.judge.updateComplete ? dialog.phrase.updateComplete : dialog.phrase.updateFail">
            <v-btn @click="dialog.instance.updateComplete = false" :style="palette.brownFront">OK</v-btn>
          </dialog-frame>
        </div>

        <!-- 削除確認ダイアログ -->
        <div class="dialogParts">
          <dialog-frame :target="dialog.instance.deleteConfirm" :title="'削除確認'" :contents="dialog.phrase.deleteConfirm">
            <v-btn @click="doDeleteSchedules" :style="palette.brownFront" v-if="deleteType == 0">いいから消せ</v-btn>
            <v-btn @click="doDeleteAccount" :style="palette.brownFront" v-if="deleteType == 1">いいから消せ</v-btn>
            <v-btn @click="dialog.instance.deleteConfirm = false" :style="palette.brownBack">やっぱやめとく</v-btn>
          </dialog-frame>
        </div>

        <!-- 削除完了or失敗ダイアログ -->
        <div class="dialogParts">
          <dialog-frame :target="dialog.instance.deleteComplete" :title="dialog.judge.deleteComplete ? '削除完了' : 'エラー'"
            :contents="dialog.judge.deleteComplete ? dialog.phrase.deleteComplete : dialog.phrase.deleteFail">
            <v-btn @click="dialog.instance.deleteComplete = false" :style="palette.brownFront">OK</v-btn>
          </dialog-frame>
        </div>

        <!-- ログアウト確認ダイアログ -->
        <div class="dialogParts">
          <dialog-frame :target="dialog.instance.logout" :title="'ログアウト確認'" :contents="dialog.phrase.logout">
            <v-btn @click="doLogout" :style="palette.brownFront">OK</v-btn>
            <v-btn @click="dialog.instance.logout = false" :style="palette.brownBack">キャンセル</v-btn>
          </dialog-frame>
        </div>
      </main>
      <hr />

      <!-- フッターエリア -->
      <footer v-show="isReady" v-cloak class="fader">
        <a href="./index.php">週間学習スケジュール照会ページ</a>
      </footer>
      <v-app>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/vue@2.x/dist/vue.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.18.0/axios.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
  <script type="module">
    import cardSection from '../../static/js/mod_cardSection.js';
    import colorPalette from '../../static/js/mod_colorPalette.js';
    import commonFunctions from '../../static/js/mod_commonFunctions.js';
    import tagTitle from '../../static/js/mod_tagTitle.js';
    import digiClock from '../../static/js/mod_digiClock.js';
    import dialogFrame from '../../static/js/mod_dialogFrame.js';
    import scheduleEditForm from '../../static/js/mod_scheduleEditForm.js';
    import dataTableForAccount from '../../static/js/mod_dataTableForAccount.js';

    new Vue({
      el: '#vueArea',
      vuetify: new Vuetify(),
      data: function() {
        return {
          account_id: <?php echo $_SESSION['account_id'] ?>,
          currentState: 1,
          dialog: {
            instance: {
              updateConfirm: false,
              updateComplete: false,
              deleteConfirm: false,
              deleteComplete: false,
              logout: false,
            },
            judge: {
              updateComplete: false,
            },
            phrase: {
              updateConfirm: "これで更新します。よろしいですか？",
              updateComplete: '',
              updateFail: '',
              deleteConfirm: "ホントに削除しますよ？後悔しませんね？",
              deleteComplete: '',
              deleteFail: '',
              logout: "ログアウトします。よろしいですか？",
            },
          },
          deleteType: 0,
          form: {
            name: '',
            login_id: '',
            comment: '',
            password: '',
            editable: {
              password: false,
            },
          },
          isReady: false,
          registPulldowns: [],
          palette: colorPalette,
          updateJudge: '',
        };
      },
      methods: {
        completeStopOrRestart(data) {
          this.dialog.judge.updateComplete = true;
          this.dialog.phrase.updateComplete = data.message;
          this.dialog.instance.updateComplete = true;
          this.jumpToPathLater("./settings.php");
        },
        confirmLogout() {
          this.dialog.instance.logout = true;
        },
        confirmUpdate() {
          this.dialog.instance.updateConfirm = true;
        },
        confirmDelete(num) {
          this.deleteType = num;
          this.dialog.instance.deleteConfirm = true;
        },
        doLogout() {
          location.href = "../login.php";
        },
        doUpdate() {
          let invalidArray = [];

          // login_idがメールアドレス形式になっているかを判定する
          const emailRegex = new RegExp(/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/);
          if (!emailRegex.test(this.form.login_id)) invalidArray.push('login_idがメールアドレス形式ではありません。');

          if (this.form.editable.password) {
            // passwordの形式が正しいかを判定する
            const passRegex = new RegExp(/^(?=.*[a-zA-Z])(?=.*[0-9])[a-zA-Z0-9.?/-]{6,16}$/);
            if (!passRegex.test(this.form.password)) invalidArray.push('passwordが要件を満たしていません。');
          }

          if (invalidArray.length > 0) {
            alert(invalidArray.join('\n'));
            return;
          }

          // 入力されている内容を送信する
          let data = {
            type: 'update',
            id: this.account_id,
            name: commonFunctions.processString(this.form.name),
            login_id: this.form.login_id,
            comment: commonFunctions.processString(this.form.comment),
          };
          if (this.form.editable.password) data.password = this.form.password;

          // axiosでPHPのAPIにパラメータを送信する為、次のようにする
          let params = new URLSearchParams();
          Object.keys(data).forEach(function(key) {
            params.append(key, this[key]);
          }, data);

          // ajax通信実行
          axios
            .post('../../server/api/accountUpdate.php', params, this.headerObject)
            .then(response => {
              this.dialog.instance.updateConfirm = false;
              this.dialog.instance.updateComplete = true;
              if (response.data.type == 'fail') {
                this.dialog.judge.updateComplete = false;
                this.dialog.phrase.updateFail = response.data.message;
              } else {
                this.dialog.judge.updateComplete = true;
                this.dialog.phrase.updateComplete = response.data.message;
                this.jumpToPathLater("../login.php");
              }
            }).catch(error => alert("通信に失敗しました。"));
        },
        doDeleteAccount() {
          // ログインユーザーのアカウントを設定したオブジェクトを作成する
          let data = {
            type: 'delete',
            id: this.account_id
          };

          let params = new URLSearchParams();
          Object.keys(data).forEach(function(key) {
            params.append(key, this[key]);
          }, data);

          axios
            .post('../../server/api/accountUpdate.php', params, this.headerObject)
            .then(response => {
              this.dialog.instance.deleteConfirm = false;
              this.dialog.instance.deleteComplete = true;
              if (response.data.type == 'fail') {
                this.dialog.judge.deleteComplete = false;
                this.dialog.phrase.deleteFail = response.data.message;
              } else {
                this.dialog.judge.deleteComplete = true;
                this.dialog.phrase.deleteComplete = response.data.message;

                // ユーザーが登録してきたノートも一括削除する
                this.doDeleteSchedules();
              }
            }).catch(error => alert("通信に失敗しました。"));
        },
        doDeleteSchedules() {
          let data = {
            type: 'delete',
            id: this.account_id
          };

          let params = new URLSearchParams();
          Object.keys(data).forEach(function(key) {
            params.append(key, this[key]);
          }, data);

          axios
            .post('../../server/api/scheduleCRUD.php', params, this.headerObject)
            .then(response => {
              // スケジュール全件削除の場合だけ、全件削除を知らせるモーダルを出す
              if (this.deleteType == 0) {
                this.dialog.phrase.deleteComplete = response.data.message;
                this.dialog.instance.deleteConfirm = false;
                this.dialog.judge.deleteComplete = true;
                this.dialog.instance.deleteComplete = true;
              }

              // セッションリセットの為、ログインページに遷移
              this.jumpToPathLater("../login.php");
            }).catch(error => alert("通信に失敗しました。"));
        },
        jumpToIndex() {
          location.href = "./index.php";
        },
        jumpToPathLater(path) {
          setTimeout(function() {
            const locationFor = path;
            location.href = locationFor;
          }, 3000);
        },
        reset_formInput() {
          this.form = {
            name: '<?php echo $_SESSION['name'] ?>',
            login_id: '<?php echo $_SESSION['login_id'] ?>',
            comment: '<?php echo $_SESSION['comment'] ?>',
            password: '',
            editable: {
              password: false,
            },
          };
        },
        toggleState() {
          this.currentState = this.currentState === 1 ? 2 : 1;
        }
      },
      mounted() {
        this.isReady = true;
        this.reset_formInput();
      }
    });
  </script>
</body>

</html>