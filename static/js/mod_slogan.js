/**
 * コンポーネント：スローガンメッセージ
 */

let slogan = Vue.component("slogan", {
  template: `<div class="slogan" align="center">
  <h3 class="sloganSub">
    <span><slot name="sub" /></span>
  </h3>
  <h2 class="sloganMain">
    <span><slot name="main" /></span>
  </h2>
</div>`,
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

export default slogan;