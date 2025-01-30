/**
 * コンポーネント：登録アカウント一覧データテーブル
 */

let dataTableForAccount = Vue.component('table-account', {
  template: `<div class="tableArea" style="padding:1em">
    <v-card>
      <v-data-table
        :headers="headers"
        :items="items"
        :items-per-page="10"
        :search="search"
        class="elevation-1"
      >
      <template v-slot:item.status="{ item }">
        <span v-if="item.is_stopped != 1"></span>
        <span v-if="item.is_stopped == 1">停止中</span>
      </template>
      <template v-slot:item.change="{ item }">
        <v-btn
          v-if="item.is_stopped === 1"
          :id="'status_' + item.id"
          color="primary" small @click="changeForRestart(item.id)">起動</v-btn>
        <v-btn
          v-else
          :id="'status_' + item.id"
          color="error" small @click="changeForStop(item.id)">停止</v-btn>
        </template>
      </v-data-table>
    </v-card>
  </div>`,
  data: function () {
    return {
      headerObject: {
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      },
      search: "",
      headers: [
        { text: "アカウント名", width: '50%', value: "name", align: "start" },
        { text: "登録日時", width: '30%', value: "created_at" },
        { text: "状態", width: '15%', value: "status", sortable: false, filterable: false },
        { text: "状態変更", width: '15%', value: "change", sortable: false, filterable: false }
      ],
      items: [],
    };
  },
  created: function () {
    this.init();
  },
  props: ['id'],
  methods: {

    // 画面初期表示処理
    async init() {
      this.getAccounts();
    },
    changeForRestart(selected_id) {
      const data = { type: 'restart', selected_id: selected_id };
      this.stopOrStartAccount(data);
    },
    changeForStop(selected_id) {
      const data = { type: 'stop', id: this.id, selected_id: selected_id };
      this.stopOrStartAccount(data);
    },
    getAccounts() {
      // ログインユーザーのデータをオブジェクトに格納
      let data = { id: this.id };

      // axiosでPHPのAPIにパラメータを送信する場合は、次のようにする
      let params = new URLSearchParams();
      Object.keys(data).forEach(function (key) {
        params.append(key, this[key]);
      }, data);

      // ajax通信実行
      axios
        .post('../../server/api/getAccounts.php', params, this.headerObject)
        .then(response => {
          this.items = response.data.accounts;
        }).catch(error => alert("通信に失敗しました。"));
    },
    stopOrStartAccount(data) {
      let params = new URLSearchParams();
      Object.keys(data).forEach(function (key) {
        params.append(key, this[key]);
      }, data);

      // ajax通信実行
      axios
        .post('../../server/api/accountUpdate.php', params, this.headerObject)
        .then(response => {
          // 呼び出し元にレスポンスデータを渡す
          this.$emit('get-changed', response.data);
        }).catch(error => alert("通信に失敗しました。"));
    }
  },
});

export default dataTableForAccount;
