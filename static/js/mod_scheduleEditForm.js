/**
 * コンポーネント：スケジュール登録・編集エリア用
 */
let scheduleEditForm = Vue.component("schedule-form", {
  template: `<div class="content">
    <card-sec border-setting="1px solid #00bfa5">
      <template #title><tag-title>学習事項入力フォーム</tag-title></template>
      <template #contents>
        <div id="registBtnArea" :align="(listItems.length > 0) ? 'right' : 'center'">
          <v-btn
            v-if="listItems.length > 0"
            :style="palette.blueFront"
            :disabled="listItems.length == 0"
            @click="changeForRegist()">新規登録</v-btn>
          <p v-if="listItems.length == 0"></p>
          <p v-if="listItems.length == 0"
            :style="ownStyles.nonRegistableArea">来週までの登録枠に空きがありません</p>
        </div><br />
        <table-schedules
          :selects="selects" :palette="palette"
          @send-update="reflectObjectForInputForm" 
          @send-delete="reflectObjectForInputForm"></table-schedules>
        <v-app class="transparent" v-if="editable">
          <div id="selectorArea" :style="'width:200px;' + ownStyles.areaMargin">
            <v-select
              v-if="mode == 'regist'"
              v-model="selectItem"
              :items="listItems"
              label="学習日"
              item-text="date"
              item-value="id"
              return-object
              single-line
            ></v-select>
          </div>
          <div id="contentsArea" :style="ownStyles.areaMargin">
            <v-textarea outlined
              :label="(mode=='update') ? form.input.first_studied + 'の学習事項' : '学習事項'"
              v-model="form.input.contents"
              placeholder="学習事項を入力してください（必須項目）"></v-textarea>
          </div>
          <div id="commentsArea" :style="ownStyles.areaMargin">
            <v-textarea outlined
              :label="(mode=='update') ? form.input.first_studied + 'の備考' : '備考'"
              v-model="form.input.comment"
              placeholder="学習事項に関する備考を入力してください"></v-textarea>
          </div>
          <div id="doExecuteArea" :style="ownStyles.areaMargin" align="center">
            <v-btn @click="doSending"
              :disabled="form.input.contents == ''"
              :style="palette.brownFront"
              v-if="mode == 'regist'">登録</v-btn>
            <v-btn @click="doSending"
              :disabled="form.input.contents == ''"
              :style="palette.brownFront"
              v-if="mode != 'regist'">更新</v-btn>
            <v-btn @click="doReset" :style="palette.brownBack">リセット</v-btn>
          </div>
        </v-app>
      </template>
    </card-sec>
  </div>`,
  data: function() {
    return {
      editable: false,
      form: this.forminfo,
      palette: this.styles,
      listItems: [],
      mode: "update",
      ownStyles: {
        nonRegistableArea: "border: solid 2px red; padding: 2em;",
        areaMargin: "margin: 1em;",
      },
      selectItem: '',
    };
  },
  created: async function () {
    this.checkNoteIdExist();
  },
  props: {
    forminfo: {
      type: Object,
      required: true
    },
    selects: {
      type: Array,
      required: false,
      default: () => [] // 値が渡されない場合のデフォルト値を空の配列に設定する
    },
    styles: {
      type: Object,
      required: true
    }
  },
  methods: {
    changeForRegist() {
      this.editable = true;
      this.mode = "regist";
      this.doReset();
    },
    checkNoteIdExist() {
      let data = {
        type: 'ids',
        created_user_id: this.form.account_id
      };

      let params = new URLSearchParams();
      Object.keys(data).forEach(function(key) {
        params.append(key, this[key]);
      }, data);

      const pdValuesList = this.generateNoteIds(this.form);
      axios
        .post('../../server/api/scheduleCRUD.php', params, this.headerObject)
        .then(response => {
          // 取得したIDリストの要素がpdValuesList内に含まれていない場合にlistItemsに追加する
          pdValuesList.forEach((item) => {
            if (response.data.list.indexOf(item) < 0) {
              // 「_」より後の数値を取得。start_dateの値も設定する
              const after_num = item.substr(item.indexOf('_') + 1);
              const getDate = this.findAndExtractDateInfo(this.form.pd_essence, after_num);
              const startDateNum = after_num.slice(0, 8);
              const year = startDateNum.substring(0, 4);
              const month = startDateNum.substring(4, 6);
              const day = startDateNum.substring(6, 8);
              const startDateStr = `${year}-${month}-${day}`;

              // オブジェクト化してthis.listItemsに格納する（最初のものはselectItemに格納する）
              const pushObject = {
                date: getDate,
                id: item,
                start_date: startDateStr,
                times: item.slice(-2),
              };
              this.listItems.push(pushObject);
            }
          });
          if (this.listItems.length > 0) this.selectItem = this.listItems[0];
        }).catch(error => alert("通信に失敗しました。"));
    },
    doReset() {
      this.form.input.first_studied = "";
      this.form.input.contents = "";
      this.form.input.comment = "";
      if (this.mode == 'regist' && this.listItems.length > 0) this.selectItem = this.listItems[0];
    },
    doSending() {
      // 呼び出し元にフォームのデータを送信する
      this.form.type = this.mode;
      if (this.mode == 'regist') {
        this.form.input.id = this.selectItem.id;
        this.form.input.start_date = this.selectItem.start_date;
        this.form.input.times = this.selectItem.times;
      };
      this.$emit('send-form', this.form);
    },
    findAndExtractDateInfo(array, searchStr) {
      // find()メソッドを使用して、条件に合うオブジェクトを検索
      const foundObject = array.find(obj => obj.id_parts === searchStr);
      // 該当するオブジェクトが見つかった場合
      if (foundObject) {
        // 必要なプロパティを抽出して新しいオブジェクトを作成
        return foundObject.date;
      } else {
        // 該当するオブジェクトが見つからなかった場合
        return null;
      }
    },
    generateNoteIds(item) {
      const eightDigitNum = item.account_id.toString().padStart(8, '0');
      const idHead = `U${eightDigitNum}_`;
      let idsList = [];
      setTimeout(() => {
        // プロパティ「pd_essence」の遅延読み込み対策を要する
        item.pd_essence.forEach(e => {
          idsList.push(idHead + e.id_parts);
        });
      }, 0);

      return idsList;
    },
    reflectObjectForInputForm(data) {
      this.mode = data.type;
      this.form.input.id = data.id;
      if (data.type == 'update') {
        this.form.input.start_date = data.start_date;
        this.form.input.times = data.times;
        this.form.input.first_studied = data.first_studied;
        this.form.input.contents = data.contents;
        this.form.input.comment = data.comment;
        this.editable = true;
      } else {
        this.editable = false;
        this.$emit('delete-selected', this.form);
      }
    },
  },
});

export default scheduleEditForm;