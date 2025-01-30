/**
 * コンポーネント：ダイアログフレーム（通常タイプ）
 */
const dialogFrame = Vue.component('dialog-frame', {
  template: `<div class="dialog">
    <v-container>
      <v-row justify="center">
        <!-- persistentを設定するとモーダルになる -->
        <v-dialog v-model="target" max-width="500" persistent>
          <v-card>
            <v-card-title>
              <tag-title>{{ title }}</tag-title>
            </v-card-title><br /><br />
            <v-card-text>
              <p align="center">{{ contents }}</p>
            </v-card-text>
            <v-card-actions>
              <v-spacer></v-spacer>
                <slot />
              <v-spacer></v-spacer>
            </v-card-actions><br />
          </v-card>
        </v-dialog>
      </v-row>
    </v-container>
  </div>`,
  props: ['target', 'title', 'contents'],
});

export default dialogFrame;