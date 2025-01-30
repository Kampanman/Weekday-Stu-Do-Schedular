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

  // ログインユーザーが登録したスケジュール一覧を取得
  $dataGetSql = "SELECT id, start_date, times, contents, comment, created_at "
    . "FROM " . $data_table . " WHERE created_user_id = :created_user_id";
  $stmt = $connection->prepare($dataGetSql);
  $stmt->execute([':created_user_id' => $_SESSION['account_id']]);
  $objects = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $schedules = [];
  foreach ($objects as $item) {
    $this_day = new DateTime($item['start_date']);
    $add_day = 0;

    switch ($item['times']) {
      case "02":
        $add_day = 1;
        break;
      case "03":
        $add_day = 2;
        break;
      case "04":
        $add_day = 4;
        break;
    }

    // start_dateに日付を加えて該当の日時に変換する
    $this_day->modify("+{$add_day} days");
    $year = $this_day->format('Y');
    $month = $this_day->format('m');
    $day = $this_day->format('d');
    $weekdays = ["月", "火", "水", "木", "金", "土", "日"];
    $weekday = $weekdays[$this_day->format('N') - 1];
    $item['first_studied'] = "{$year}-{$month}-{$day}（{$weekday}）";

    // 本日の日付との差を数値で取得する
    $this_datetime = new DateTime($this_day->format('Y-m-d'));
    $today = new DateTime();
    $interval = $this_datetime->diff($today);
    $plusMinus = ($interval->invert) ? -1 : 1;
    $diff_days = ($plusMinus == -1) ? ($interval->days + 1) : $interval->days;
    $item['days_diff'] = $diff_days * $plusMinus;

    $schedules[] = $item;
  }
  $objects = [];
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
  <link rel="stylesheet" href="<?php echo $staticPathFromMypage . '/css/omp_index_styles.css' ?>">
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
              <i class="material-icons" title="ユーザー情報の設定をします" @click="jumpToSettings()">settings</i>
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
        <!-- 表示中コンテンツのタイトル・切替ボタン -->
        <section id="toggleArea">
          <h1 :class="isReady ? 'fader': ''">{{ currentState == 1 ? '週間学習スケジュール照会' : '学習内容 登録・編集フォーム' }}</h1>
          <button @click="toggleState" :class="['toggle-button', currentState == 1 ? 't-green' : 't-white', isReady ? 'fader': '']">
            {{ currentState == 1 ? '学習内容の登録と編集' : '週間学習スケジュールの照会' }}
          </button>
        </section>

        <!-- 表示中コンテンツ -->
        <section>
          <div v-show="currentState == 1" class="fader">
            <!-- currentStateが1の場合の表示内容 -->
            <card-sec :border-setting="'1px solid #00bfa5'">
              <template #title><tag-title>週間学習スケジュール</tag-title></template>
              <template #contents>
                <view-schedules
                  :account_id="form.account_id"
                  :styles="styles"
                  :palette="palette"></view-schedules>
              </template>
            </card-sec>
          </div>
          <div v-show="currentState != 1" class="fader">
            <!-- currentStateが2の場合の表示内容 -->
            <schedule-form
              :forminfo="form"
              :selects="note"
              :styles="palette"
              @send-form="openConfirm"
              @delete-selected="confirmDelete"></schedule-form>
          </div>
        </section>

        <!-- 登録・更新確認ダイアログ -->
        <div class="dialogParts">
          <dialog-frame
            :target="dialog.instance.insertUpdateConfirm"
            :title="(form.type=='regist') ? '登録確認' : '更新確認'"
            :contents="(form.type=='regist') ? dialog.phrase.insertConfirm : dialog.phrase.updateConfirm">
            <v-btn @click="doInsertUpdate" :style="palette.brownFront">OK</v-btn>
            <v-btn @click="dialog.instance.insertUpdateConfirm = false" :style="palette.brownBack">キャンセル</v-btn>
          </dialog-frame>
        </div>

        <!-- 登録完了or失敗ダイアログ -->
        <div class="dialogParts">
          <dialog-frame
            :target="dialog.instance.insertComplete"
            :title="dialog.judge.insertComplete ? '登録完了' : 'エラー'"
            :contents="dialog.judge.insertComplete ? dialog.phrase.insertComplete : dialog.phrase.insertFail">
            <v-btn @click="dialog.instance.insertComplete = false" :style="palette.brownFront">OK</v-btn>
          </dialog-frame>
        </div>

        <!-- 更新完了or失敗ダイアログ -->
        <div class="dialogParts">
          <dialog-frame
            :target="dialog.instance.updateComplete"
            :title="dialog.judge.updateComplete ? '更新完了' : 'エラー'"
            :contents="dialog.judge.updateComplete ? dialog.phrase.updateComplete : dialog.phrase.updateFail">
            <v-btn @click="dialog.instance.updateComplete = false" :style="palette.brownFront">OK</v-btn>
          </dialog-frame>
        </div>

        <!-- 削除確認ダイアログ -->
        <div class="dialogParts">
          <dialog-frame :target="dialog.instance.deleteConfirm" :title="'削除確認'" :contents="dialog.phrase.deleteConfirm">
            <v-btn @click="doDelete" :style="palette.brownFront">いいから消せ</v-btn>
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
        <a href="./settings.php">ユーザー情報設定ページ</a>
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
    import dataTableForSchedules from '../../static/js/mod_dataTableForSchedules.js';
    import scheduleViewer from '../../static/js/mod_scheduleViewer.js';

    new Vue({
      el: '#vueArea',
      vuetify: new Vuetify(),
      data: function() {
        return {
          currentState: 1,
          dialog: {
            instance: {
              insertUpdateConfirm: false,
              insertComplete: false,
              updateComplete: false,
              deleteConfirm: false,
              deleteComplete: false,
              logout: false,
            },
            judge: {
              insertComplete: false,
              updateComplete: false,
              deleteComplete: false,
            },
            phrase: {
              insertConfirm: "これで登録します。よろしいですか？",
              updateConfirm: "これで更新します。よろしいですか？",
              insertComplete: "登録が完了しました。",
              updateComplete: "更新が完了しました。",
              insertFail: "登録に失敗しました。",
              updateFail: "更新に失敗しました。",
              deleteConfirm: "ホントに削除しますよ？後悔しませんね？",
              deleteComplete: "削除が完了しました。",
              deleteFail: "削除に失敗しました。",
              logout: "ログアウトします。よろしいですか？",
            },
          },
          form: {
            account_id: <?php echo $_SESSION['account_id']; ?>,
            input: {
              id: "",
              start_date: "",
              times: "",
              contents: "",
              comment: "",
            },
            pd_essence: null,
            type: "update",
          },
          isReady: false,
          note: <?php echo json_encode($schedules) ?>,
          palette: colorPalette,
          styles: {
            alignFlex: 'align-items:center; display:flex;',
            areaMargin: "margin: 1em 0.5em;",
          },
        };
      },
      methods: {
        cancelLogout() {
          this.dialog.instance.logout = false;
        },
        confirmLogout() {
          this.dialog.instance.logout = true;
        },
        confirmDelete(data) {
          this.form.type = 'delete';
          this.form.input.id = data.input.id;
          this.dialog.instance.deleteConfirm = true;
        },
        doDelete() {
          // 入力されている内容を送信する
          let data = {
            type: this.form.type,
            note_id: this.form.input.id,
            id: this.form.account_id,
          };

          // axiosでPHPのAPIにパラメータを送信する為、次のようにする
          let params = new URLSearchParams();
          Object.keys(data).forEach(function(key) {
            params.append(key, this[key]);
          }, data);

          // ajax通信実行
          axios
            .post('../../server/api/scheduleCRUD.php', params, this.headerObject)
            .then(response => {
              this.dialog.instance.deleteConfirm = false;
              this.dialog.judge.deleteComplete = true;
              this.dialog.instance.deleteComplete = true;

              // 登録・更新完了時は3秒後にリロードする
              this.locationReloadLater(response.data.type);
            }).catch(error => alert("通信に失敗しました。"));
        },
        doInsertUpdate() {
          let data = {
            type: this.form.type,
            id: this.form.input.id,
            start_date: this.form.input.start_date,
            times: this.form.input.times,
            contents: commonFunctions.processString(this.form.input.contents),
            comment: commonFunctions.processString(this.form.input.comment),
            created_user_id: this.form.account_id,
          };

          let params = new URLSearchParams();
          Object.keys(data).forEach(function(key) {
            params.append(key, this[key]);
          }, data);

          axios
            .post('../../server/api/scheduleCRUD.php', params, this.headerObject)
            .then(response => {
              this.dialog.instance.insertUpdateConfirm = false;
              if (response.data.request == 'regist') {
                if (response.data.type == 'success') this.dialog.judge.insertComplete = true;
                this.dialog.instance.insertComplete = true;
              } else {
                if (response.data.type == 'success') this.dialog.judge.updateComplete = true;
                this.dialog.instance.updateComplete = true;
              }

              this.locationReloadLater(response.data.type);
            }).catch(error => alert("通信に失敗しました。"));
        },
        doLogout() {
          location.href = "../login.php";
        },
        getRegistPulldowns() {
          let pdObjects = [];
          const mondaysArray = commonFunctions.getMondayDates();
          mondaysArray.forEach((item) => {
            const daysArrayOfWeek = commonFunctions.getWeekDates(item);
            for (let i = 0; i < 4; i++) {
              let dateIndex = 0;
              if (i == 1) dateIndex = 1;
              if (i == 2) dateIndex = 2;
              if (i == 3) dateIndex = 4;

              let weekday = "（月）";
              if (i == 1) weekday = "（火）";
              if (i == 2) weekday = "（水）";
              if (i == 3) weekday = "（金）";

              pdObjects.push({
                'id_parts': `${item.replaceAll('-','')}0${i+1}`,
                'date': daysArrayOfWeek[dateIndex] + weekday,
              });
            }
          });

          return pdObjects;
        },
        jumpToSettings() {
          location.href = "./settings.php";
        },
        locationReloadLater(status) {
          if (status == 'success') {
            setTimeout(function() {
              location.reload();
            }, 3000);
          }
        },
        openConfirm(data) {
          this.form.type = data.type;
          this.dialog.instance.insertUpdateConfirm = true;
        },
        toggleState() {
          this.currentState = this.currentState === 1 ? 2 : 1;
        }
      },
      mounted() {
        this.isReady = true;
        this.form.pd_essence = this.getRegistPulldowns();
      }
    });
  </script>
</body>

</html>