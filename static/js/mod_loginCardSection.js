/**
 * コンポーネント：カードセクション（エリア用）
 */
let loginCardSection = Vue.component("login-card-sec", {
  template: `<section class="content" style="
    padding:19px;
    margin-bottom:20px;
    border:1px solid #ebebeb;
    border-radius:6px;
    background:#facdcd;
    overflow:hidden;
    font-size:14px;
  ">
    <slot name="title" />
    <article class="areaContents">
      <slot name="contents" />
    </article>
  </section>`,
  data: function(){
    return {
      // 
    }
  },
  created: function () {
    this.init();
  },
  methods: {
    // 画面初期表示処理
    async init() {
      // 
    },
  },
});

export default loginCardSection;