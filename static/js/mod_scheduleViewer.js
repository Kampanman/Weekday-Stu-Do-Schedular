/**
 * コンポーネント：スケジュール表示エリア
 */
let scheduleViewer = Vue.component("view-schedules", {
  template: `<div class="content">
    <section id="pullDownArea" :style="ownStyles.pullDownArea">
      <div id="pullDownContents" :style="'width:200px;' + styles.areaMargin">
        <v-select
          v-if="pullDown.listItems.length > 0"
          v-model="pullDown.selectItem"
          :items="pullDown.listItems"
          item-text="dateStr"
          item-value="date"
          return-object
          single-line
          @change="getScheduleRecordsByRequest(pullDown.selectItem.date)"></v-select>
      </div>
      <i class="material-icons"
        v-if="showInfo == false"
        title="クリックすると曜日ごとの学習タスク目安が表示されます"
        @click="activateShowInfo()">info</i>
      <i class="material-icons"
        v-if="showInfo == true"
        title="クリックすると曜日ごとの学習タスク目安が閉じられます"
        @click="activateShowInfo()">cancel</i>
    </section>
    <article id="infoArticle" class="fader"
      v-if="showInfo == true"
      :style="ownStyles.infoArticle + styles.dd_border"
      @click="showInfo = false">
      <h3>曜日ごとの学習タスク目安</h3>
      <p></p>
      <ul><li :style="ownStyles.infoArticleInto" v-for="row of infoRows">{{ row.day }}: {{ row.menu }}</li></ul>
    </article>
    <div id="searchArea" :style="styles.alignFlex">
      <div style="margin:5px;"><v-text-field label="検索ワード" type="text" v-model="searchWord" maxlength="50" /></div>
      <v-btn :style="palette.brownBack" @click="resetSearchScheduleTask()" v-if="isSearched == true">リセット</v-btn>
      <v-btn :disabled="searchWord==''" :style="palette.brownFront" @click="searchScheduleTask()" v-else>検索</v-btn>
    </div>
    <p></p>
    <div id="viewSheduleArea">
      <p v-if="isNonSearchedHit == true"
        class="fader" :style="ownStyles.nonViewableArea + ownStyles.areaMargin">表示できるスケジュールはありません</p>
      <div id="sheduleContent" class="fader" v-else>
        <dl style="margin: 1em 0;" v-for="(item, index) of filtered" v-if="item.record != null">
          <dt :style="styles.dt_border + styles.dt_blank + styles.dt_inner + 'background-color: ' + styles.dtBacks[index]">{{ item.name }}</dt>
          <dd :style="styles.dd_border + styles.dd_blank + styles.dd_inner">
            <article v-for="parts of item.record"
              :style="(item.record != null && item.record.length > 1)
                ? (ownStyles.areaMargin + styles.pattern_non_mono)
                : ownStyles.areaMargin">
              <p style='display: flex; flex-wrap: wrap;'><span>【初回学習日】</span><span>{{ parts.first_studied }}</span></p>
              <p><ul><li v-for="line of parts.contents.split('\\n')">{{ line }}</li></ul></p>
              <p v-if="parts.comment != ''">
                <span>【備考】</span><br />
                <span v-for="line of parts.comment.split('\\n')" style="margin-left: 1em;">{{ line }}</span>
              </p>
            </article>
          </dd>
        </dl>
      </div>
    </div>
  </div>`,
  data: function(){
    return {
      filtered: [],
      infoRows: [
        { day: "月", menu: "学習事項1の新規学習" },
        { day: "火", menu: "学習事項2の新規学習 ＆ 学習事項1の復習" },
        { day: "水", menu: "学習事項3の新規学習 ＆ 学習事項2の復習" },
        { day: "木", menu: "学習事項3の復習 ＆ 1週間前の学習事項の復習" },
        { day: "金", menu: "学習事項4の新規学習" },
        { day: "土", menu: "学習事項4の復習 ＆ 2週間前の学習事項の復習" },
        { day: "日", menu: "1カ月前の学習事項の総復習" },
      ],
      isSearched: false,
      isNonSearchedHit: false,
      ownStyles: {
        nonViewableArea: "border: solid 2px red; padding: 2em; text-align: center;",
        areaMargin: "margin: 1em;",
        pullDownArea: "cursor: pointer; display: flex; align-items: center;",
        infoArticle: "cursor: pointer; margin: 1em; padding: 1em; text-align: center; background-color: white;",
        infoArticleInto: "font-size: 15px; text-align: left; margin: 0.5em 1em;"
      },
      pullDown: {
        listItems: [],
        selectItem: "",
      },
      searchWord: "",
      selects: {
        request_date: "",
        request_day_str: "",
        records: [],
      },
      showInfo: false,
    }
  },
  props: {
    account_id: {
      type: Number,
      required: true,
      default: 0,
    },
    styles: {
      type: Object,
      required: true,
    },
    palette: {
      type: Object,
      required: true,
    },
  },
  methods: {
    activateShowInfo() {
      if (!this.showInfo) {
        this.showInfo = true;
        setTimeout(function () {
          const element = document.getElementById("infoArticle");
          if (element.offsetWidth > 630) {
            element.style.backgroundColor = "white";
            element.style.cursor = "pointer";
            element.style.margin = "1em auto";
            element.style.padding = "1em";
            element.style.textAlign = "center";
            element.style.width = "630px";
          }
        }, 0);
      } else {
        this.showInfo = false;
      }
    },
    generateFilteredObject(data) {
      const thisDate = data.request_date;
      const thisDow = data.request_day_str;
      let filteredObject = [];

      // 当日学習タスクのオブジェクトを生成する
      const todayRecord = data.records.filter(item => item.first_studied.indexOf(thisDate) > -1);
      const todayRecordInput = (todayRecord.length > 0) ? todayRecord : null;
      filteredObject.push({ name: "当日学習タスク", record: todayRecordInput });

      // 1日後復習タスクのオブジェクトを生成する
      let oneBeforeRecord = null;
      const regex = new RegExp(/^[火水木土]$/);
      if (regex.test(thisDow)) {
        const recordArray = data.records.filter(item => item.days_diff == 1);
        oneBeforeRecord = recordArray;
      }
      filteredObject.push({ name: "1日後復習タスク", record: oneBeforeRecord });

      // 1週間後復習タスクのオブジェクトを生成する
      const sevenBeforeRecord = (thisDow == "木")
        ? data.records.filter(item => (item.days_diff >= 6 && item.days_diff <= 10) )
        : null;
      filteredObject.push({ name: "1週間後復習タスク", record: sevenBeforeRecord });

      // 2週間後復習タスクのオブジェクトを生成する
      const fourteenBeforeRecord = (thisDow == "土")
        ? data.records.filter(item => (item.days_diff >= 15 && item.days_diff <= 19) )
        : null;
      filteredObject.push({ name: "2週間後復習タスク", record: fourteenBeforeRecord });

      // 1カ月後復習タスクのオブジェクトを生成する
      const thirtyBeforeRecord = (thisDow == "日")
        ? data.records.filter(item => (item.days_diff >= 30 && item.days_diff <= 34) )
        : null;
      filteredObject.push({ name: "1カ月後復習タスク", record: thirtyBeforeRecord });

      // item.recordが[]になってしまっている場合はnullに変換する
      const reFiltered = filteredObject.map(item => {
        const record = (item.record != null && item.record.length == 0) ? null : item.record;
        return { name: item.name, record: record };
      });
      filteredObject = null;

      this.filtered = reFiltered;
      if(!this.isSearched) this.setSearchedHitByFiltered(reFiltered);
    },
    getDayOfWeekOfRequestDate(request) {
      let request_date = new Date(request);
      const weekdays = ["日", "月", "火", "水", "木", "金", "土"];

      return weekdays[request_date.getDay()];
    },
    getViewdayPulldowns() {
      let datesArray = [];
      const today = new Date();

      for (let i = 0; i < 14; i++) {
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const day = String(today.getDate()).padStart(2, '0');
        const dow = this.getDayOfWeekOfRequestDate(`${year}-${month}-${day}`);

        const pushObject = {
          date: `${year}-${month}-${day}`,
          dateStr: `${year}-${month}-${day}（${dow}）`,
        };
        datesArray.push(pushObject);
        today.setDate(today.getDate() + 1);
      }

      this.pullDown.listItems = datesArray;
      this.pullDown.selectItem = datesArray[0];
    },
    getScheduleRecordsByRequest(request_date) {
      let data = {
        type: 'select',
        created_user_id: this.account_id,
        request_date: request_date,
      };

      // axiosでPHPのAPIにパラメータを送信する為、次のようにする
      let params = new URLSearchParams();
      Object.keys(data).forEach(function (key) {
        params.append(key, this[key]);
      }, data);

      // ajax通信実行
      axios
        .post('../../server/api/scheduleCRUD.php', params, this.headerObject)
        .then(response => {
          this.selects.request_date = data.request_date;
          this.selects.request_day_str = this.getDayOfWeekOfRequestDate(data.request_date);
          this.selects.records = response.data.list;
          this.generateFilteredObject(this.selects);
        }).catch(error => alert("通信に失敗しました。"));
    },
    resetSearchScheduleTask() {
      this.isSearched = false;
      this.isNonSearchedHit = false;
      this.searchWord = "";
      this.generateFilteredObject(this.selects);
    },
    searchScheduleTask() {
      this.isSearched = true;
      this.generateFilteredObject(this.selects);

      // 検索語が含まれているレコードに絞り込む
      let reFiltered = [];
      this.filtered.forEach(item => {
        let filteredItem = item;
        if (item.record != null) {
          filteredItem = {
            name: item.name,
            record: item.record.filter(row => row.contents.indexOf(this.searchWord) > -1),
          }
          if (filteredItem.record.length == 0) filteredItem.record = null;
        }
        reFiltered.push(filteredItem);
      });

      // 検索語にヒットするレコードが1件もない場合は、そのメッセージを出すためのフラグを有効化する
      this.setSearchedHitByFiltered(reFiltered);
      this.filtered = reFiltered;
    },
    setSearchedHitByFiltered(object) {
      // オブジェクト内にnullでないrecordがあればisNonSearchedHitをfalseにする
      this.isNonSearchedHit = true;
      for (let i = 0; i < object.length; i++){
        if (object[i].record != null) {
          this.isNonSearchedHit = false;
          break;
        }
      }
    },
    setStylesOfDl() {
      const deepBlueBorder = "3px solid rgb(20 40 200);";
      const lightBlueBorder = "3px solid rgb(85 155 255);";
      const borderSet = `border-top: ${lightBlueBorder} border-left: ${lightBlueBorder} border-bottom: ${deepBlueBorder} border-right: ${deepBlueBorder}`;
      const one_month_setting = "1em; padding: 1em 0.5em 0.5em; border: dotted 2px rgb(20 40 200);";
      this.styles.pattern_non_mono = one_month_setting;
      this.styles.dt_border = borderSet;
      this.styles.dt_blank = "margin-left: 3em; padding: 0.25em 1em;";
      this.styles.dt_inner = "display: inline-block; font-weight: 700; font-size: 16px;";
      this.styles.dd_border = borderSet;
      this.styles.dd_blank = "margin: -1em 1.5em 1em 1.5em; padding: 1em;";
      this.styles.dd_inner = "background-color: white; font-size: 16px;";
      this.styles.dtBacks = ["#ffdcba;", "#caffba;", "#bafaff;", "#baceff;", "#ffbaf0;"];
    },
  },
  mounted() {
    this.setStylesOfDl();
    this.getViewdayPulldowns();
    this.getScheduleRecordsByRequest(this.pullDown.selectItem.date);
  },
});

export default scheduleViewer;
