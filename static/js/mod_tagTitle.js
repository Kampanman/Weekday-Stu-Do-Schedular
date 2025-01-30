/**
 * コンポーネント：付箋タイトル
 */
 let tagTitle = Vue.component("tag-title", {
  template: `<h3 class="areaTitle heading" style="
    display:inline-block;
    padding-left:7px;
    padding-right:12px;
    margin-bottom:10px;
    font-size:16px;
    border-left:5px solid #8d0000;
    color:#8d0000;
    background: #fff7a5;
    box-shadow: 5px 5px 5px rgb(0 0 0 / 22%);
  ">
    <slot />
  </h3>`,
});

export default tagTitle;