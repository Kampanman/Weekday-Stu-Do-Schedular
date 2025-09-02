/**
 * コンポーネント：登録スケジュール一覧データテーブル
 */

let dataTableForSchedules = Vue.component('table-schedules', {
  template: `<div class="tableArea" style="padding:1em">
    <v-card>
      <v-data-table
        :headers="headers"
        :items="items"
        :items-per-page="10"
        class="elevation-1"
      >
      <template v-slot:item.id="{ item }">
        <div class="d-flex align-center">
          <span v-text="item.id"></span>
          &nbsp;&nbsp;
          <i class="material-icons"
            title="スケジュールを閲覧します"
            :style="styles.iconsHover"
            @click="viewSchedule(item.id)"
          >visibility</i>
        </div>
      </template>
      <template v-slot:item.before_after="{ item }">
        <span v-if="item.days_diff > 0" v-text="item.days_diff + '日前'"></span>
        <span v-if="item.days_diff < 0" v-text="(item.days_diff) * -1  + '日後'"></span>
      </template>
      <template v-slot:item.change="{ item }">
        <i class="material-icons"
          title="フォームにスケジュール情報を反映します"
          :id="'edit_' + item.id"
          :style="styles.iconsHover"
          @click="editSchedule(item.id)" v-if="item.days_diff < 7">edit</i>
        <i class="material-icons"
          title="選択したスケジュールを削除します"
          :id="'delete_' + item.id"
          :style="styles.iconsHover"
          @click="deleteSchedule(item.id)">delete</i>
      </template>
      </v-data-table>
    </v-card>

    <section id="over60Area" v-if="isExistOver60 == true">
      <div align="center">
        <br />
        <v-btn color="error" @click="openModalOfdeleteOver60Confirm">古いスケジュールの一括削除</v-btn>
      </div>

      <!-- 削除確認ダイアログ -->
      <div class="dialogParts intoCard">
        <dialog-frame :target="dialog.instance.deleteOldsConfirm" :title="'削除確認'" :contents="dialog.phrase.deleteOldsConfirm">
          <v-btn @click="deleteOver60DaysAgo" :style="palette.brownFront">OK</v-btn>
          <v-btn @click="dialog.instance.deleteOldsConfirm = false" :style="palette.brownBack">キャンセル</v-btn>
        </dialog-frame>
      </div>

      <!-- 削除完了or失敗ダイアログ -->
      <div class="dialogParts intoCard">
        <dialog-frame :target="dialog.instance.deleteOldsComplete" :title="dialog.judge.deleteOldsComplete ? '削除完了' : 'エラー'"
          :contents="dialog.judge.deleteOldsComplete ? dialog.phrase.deleteOldsComplete : dialog.phrase.deleteOldsFail">
          <v-btn @click="dialog.instance.deleteOldsComplete = false" :style="palette.brownFront">OK</v-btn>
        </dialog-frame>
      </div>
    </section>
  </div>`,
  data: function () {
    return {
      headerObject: {
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      },
      dialog: {
        instance: {
          deleteOldsConfirm: false,
          deleteOldsComplete: false,
        },
        judge: {
          deleteOldsComplete: false,
        },
        phrase: {
          deleteOldsConfirm: "60日以上前の登録スケジュールを削除します。よろしいですか？",
          deleteOldsComplete: "スケジュールの一括削除が完了しました。",
          deleteOldsFail: "スケジュールを一括削除できませんでした。",
        },
      },
      headers: [
        { text: "スケジュールID", width: '25%', value: "id", align: "start" },
        { text: "週頭月曜日", width: '25%', value: "start_date", align: "start" },
        { text: "初回学習日", width: '25%', value: "first_studied" },
        { text: "本日との差日", width: '10%', value: "before_after" },
        { text: "状態変更", width: '15%', value: "change", sortable: false, filterable: false }
      ],
      isExistOver60: false,
      items: [],
      styles: {
        iconsHover: "cursor: pointer; margin-right: 0.5em;",
      },
    };
  },
  created: function () {
    this.init();
  },
  props: {
    selects: {
      type: Array,
      required: false,
      default: () => [] // 値が渡されない場合のデフォルト値を空の配列に設定する
    },
    palette: {
      type: Object,
      required: true,
    },
  },
  methods: {
    // 画面初期表示処理
    async init() {
      this.items = this.selects;
      this.getOver60DaysAgo();
    },
    createSendObject(selected_id, type) {
      const selected = this.items.filter(item => item.id == selected_id);
      return {
        type: type,
        id: selected[0].id,
        start_date: selected[0].start_date,
        times: selected[0].times,
        first_studied: selected[0].first_studied,
        contents: selected[0].contents,
        comment: selected[0].comment,
      }
    },
    editSchedule(selected_id) {
      const updateInfo = this.createSendObject(selected_id, 'update');
      this.$emit('send-update', updateInfo);
    },
    viewSchedule(selected_id) {
      const viewInfo = this.createSendObject(selected_id, 'view');
      this.$emit('send-view', viewInfo);
    },
    deleteSchedule(selected_id) {
      const deleteInfo = this.createSendObject(selected_id, 'delete');
      this.$emit('send-delete', deleteInfo);
    },
    openModalOfdeleteOver60Confirm() {
      this.dialog.instance.deleteOldsConfirm = true;
    },
    deleteOver60DaysAgo() {
      // 登録から60日以上経過したスケジュールのIDを取得してIN句の後に
      const deleteSchedules = this.getOver60DaysAgo();
      let deleteIds = [];
      deleteSchedules.forEach(item => deleteIds.push(`"${item.id}"`));
      const deleteInQuery = `(${deleteIds.join(',')})`;

      // 入力されている内容を送信する
      let data = {
        type: 'delete_over60',
        delete_note_ids: deleteInQuery,
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
          this.dialog.instance.deleteOldsConfirm = false;
          this.dialog.judge.deleteOldsComplete = (response.data.type == 'success') ? true : false;
          this.dialog.instance.deleteOldsComplete = true;

          // 登録・更新完了時は3秒後にリロードする
          if (response.data.type == 'success') {
            setTimeout(function () {
              location.reload();
            }, 3000);
          }
        }).catch(error => alert("通信に失敗しました。"));
    },
    getOver60DaysAgo() {
      // 登録から60日以上経過したスケジュールを取得する
      const over60s = this.items.filter(item => item.days_diff >= 60).map(item => {
        return { id: item.id, days_diff: item.days_diff }
      });
      if (over60s.length > 0) this.isExistOver60 = true;

      return over60s;
    },
  },
});

export default dataTableForSchedules;
