/**
 * コンポーネント：カードセクション（エリア用）
 */
let cardSection = Vue.component("card-sec", {
  template: `<section class="content" :style="{
    padding: '19px',
    marginBottom: '20px',
    border: borderSetting,
    borderRadius: '6px',
    backgroundColor: backColor,
    overflow: 'hidden',
    fontSize: '14px',
  }">
    <slot name="title" />
    <article class="areaContents">
      <slot name="contents" />
    </article>
  </section>`,
  props: {
    backColor: {
      type: String,
      default: '#efebde',
    },
    borderSetting: {
      type: String,
      default: '1px solid #ebebeb',
    },
  },
});
export default cardSection;