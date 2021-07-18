<template>
  <table cellspacing="0" cellpadding="5" border="0" class="bg-base-100 w-full">
    <!--
          <colgroup>
            <col name="el-table_1_column_1" />
            <col name="el-table_1_column_2" />
            <col name="el-table_1_column_3" />
          </colgroup>
          -->

    <thead>
      <tr>
        <th colspan="1" rowspan="1">
          <button @click="onHeaderCellClick('id')" class="btn btn-ghost btn-sm flex-nowrap">
            ID
            <Icon
              v-if="sortHeader.select === 'id'"
              glyph="chevron-down"
              class="chevron-icon inline-block w-4 stroke-current ml-2"
              :class="{ 'reverse-icon': sortHeader.reverse }"
            />
          </button>
        </th>
        <th colspan="1" rowspan="1">
          <button @click="onHeaderCellClick('date')" class="btn btn-ghost btn-sm flex-nowrap">
            Date
            <Icon
              v-if="sortHeader.select === 'date'"
              glyph="chevron-down"
              class="chevron-icon inline-block w-4 stroke-current ml-2"
              :class="{ 'reverse-icon': sortHeader.reverse }"
            />
          </button>
        </th>
        <th colspan="1" rowspan="1" class="text-left">Tags</th>
        <th colspan="1" rowspan="1" class="text-left">
          <div :data-tip="isAllCollapse ? 'extend' : 'collapse'" class="tooltip">
            <button @click="extendCollapse()" class="btn btn-ghost btn-sm">Url / Text</button>
          </div>
        </th>
      </tr>
    </thead>

    <tbody>
      <tr
        v-for="item in localItems"
        :key="item.id"
        @click="onRowClick(item)"
        class="rounded-lg cursor-pointer hover:bg-primary hover:text-primary-content"
      >
        <td rowspan="1" colspan="1" class="text-center">{{ item.id }}</td>

        <td rowspan="1" colspan="1" class="text-center" :title="item.date.toUTCString()">
          {{ item.date.toDateString().slice(4) }}
        </td>

        <td rowspan="1" colspan="1">
          <Badge v-for="(tag, index2) in item.tags" :key="index2" class="badge-ghost mr-1" :text="tag" :toHash="tag" />
        </td>

        <td rowspan="1" colspan="1" class="max-w-xl p-0 cursor-default" @click.stop="">
          <div v-if="item.type === 'URL'" class="pl-4">
            <a :href="item.url" target="_blank" rel="noreferrer" class="link link-secondary">
              {{ item.url }}
            </a>
          </div>

          <p v-else-if="item.type === 'TEXT'" class="pl-4">
            {{ item.text }}
          </p>

          <div v-else-if="item.type === 'TEXT_LONG'" class="custom-collapse collapse collapse-arrow">
            <input :id="'item-' + item.id" :checked="!item.collapse" type="checkbox" />
            <label :for="'item-' + item.id" class="collapse-title text-md font-medium"> {{ item.text.substr(0, 50) }}... </label>
            <div class="collapse-content">
              <p>{{ item.text }}</p>
            </div>
          </div>

          <div v-else-if="item.type === 'URL_AND_TEXT'" class="custom-collapse collapse collapse-arrow">
            <input :id="'item-' + item.id" :checked="!item.collapse" type="checkbox" />
            <label :for="'item-' + item.id" class="collapse-title text-md font-medium z-10">
              <a :href="item.url" target="_blank" rel="noreferrer"  class="link link-secondary">
                {{ item.url }}
              </a>
            </label>
            <div class="collapse-content">
              <p>{{ item.text }}</p>
            </div>
          </div>

          <div v-else-if="item.type === 'CODE' || item.type === 'URL_AND_CODE'" class="custom-collapse collapse collapse-arrow">
            <input :id="'item-' + item.id" :checked="!item.collapse" type="checkbox" />
            <label v-if="item.url" :for="'item-' + item.id" class="collapse-title text-md font-medium z-10">
              <a :href="item.url" target="_blank" rel="noreferrer" class="link link-secondary">
                {{ item.url }}
              </a>
            </label>
            <label v-else :for="'item-' + item.id" class="collapse-title text-md font-medium">
              {{ item.code.substr(0, 50) }}...
            </label>
            <div class="collapse-content collapse-content-code cursor-auto">
              <div class="mockup-code-custom bg-base-300">
                <Prism language="bash" :code="item.code" :plugins="[]" />
              </div>
            </div>
          </div>
        </td>

        <td rowspan="1" colspan="1">
          <button @click.stop="" class="btn btn-square btn-sm" aria-label="edit row">
            <Icon glyph="pencil-alt" class="inline-block w-4 stroke-current" />
          </button>
        </td>
      </tr>
    </tbody>
  </table>
</template>

<script lang="ts">
import { Options, Vue } from 'vue-class-component'
import Icon from '@/components/Icon.vue'
import Badge from '@/components/Badge.vue'
import Prism from '@/components/Prism.vue'
import 'prismjs/themes/prism-tomorrow.css'

enum DataType {
  URL = 'URL',
  URL_AND_TEXT = 'URL_AND_TEXT',
  URL_AND_CODE = 'URL_AND_CODE',
  TEXT = 'TEXT',
  TEXT_LONG = 'TEXT_LONG',
  CODE = 'CODE',
}

type Qnote = { id: number; date: Date; tags: string[]; url?: string; text?: string; code?: string }
type LocalQnote = Qnote & { collapse: boolean; type: DataType }

@Options({
  components: {
    Icon,
    Badge,
    Prism,
  },
  props: {
    items: Array,
  },
})
export default class Table extends Vue {
  items: Qnote[] = []
  localItems: LocalQnote[] = []
  sortHeader = { select: '', reverse: false }
  isAllCollapse = true

  beforeMount() {
    this.$watch('items', () => this.syncWithProps(), { deep: true })
    this.syncWithProps()
  }

  syncWithProps() {
    this.localItems = this.items.map((val) => {
      return { ...val, collapse: true, type: this.parseQnoteType(val) }
    })
    this.sortItems('id')
    this.sortHeader = { select: '', reverse: false }
  }

  parseQnoteType(qnote: Qnote) {
    if (qnote.url && qnote.text) return DataType.URL_AND_TEXT
    if (qnote.url && qnote.code) return DataType.URL_AND_CODE
    if (qnote.url) return DataType.URL
    if (qnote.text && qnote.text.length > 60) return DataType.TEXT_LONG
    if (qnote.text) return DataType.TEXT
    if (qnote.code) return DataType.CODE
    qnote.text = ''
    return DataType.TEXT
  }

  onHeaderCellClick(headerSelect: string) {
    if (this.sortHeader.select === headerSelect) {
      this.sortHeader.reverse = !this.sortHeader.reverse
    } else {
      this.sortHeader.select = headerSelect
      this.sortHeader.reverse = false
    }
    this.sortItems(headerSelect as any, this.sortHeader.reverse)
  }

  sortItems(by: 'id' | 'date', reverse = false) {
    let getValue = (itm: LocalQnote) => itm.id
    if (by === 'date') getValue = (itm: LocalQnote) => itm.date.getTime()

    this.localItems.sort((itm1, itm2) => {
      const a = getValue(itm1)
      const b = getValue(itm2)
      if (reverse) return b - a
      else return a - b
    })
  }

  extendCollapse() {
    this.isAllCollapse = !this.isAllCollapse
    this.localItems.forEach((val) => {
      val.collapse = this.isAllCollapse
    })
  }

  onRowClick(row: Qnote) {
    this.$emit('rowClick', row)
  }
}
</script>

<style scoped>
thead,
tbody {
  --table-boder-color: hsl(0deg 0% 40%/40%);
}

thead tr {
  border-top: 1px solid var(--table-boder-color, black);
  border-bottom: 1px solid var(--table-boder-color, black);
}

tbody tr {
  border-bottom: 1px solid var(--table-boder-color, black);
}

tbody tr:hover {
  --tw-bg-opacity: 0.7;
}

tbody tr:last-child {
  border-bottom: none;
}

/*
th,
td {
  border-right: 1px solid var(--table-boder-color, black);
}

th:first-child,
td:first-child {
  border-left: 1px solid var(--table-boder-color, black);
}
*/

/* Header */

.chevron-icon {
  transition: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

.reverse-icon {
  transform: rotateZ(180deg);
}

/* Row content */

.custom-collapse {
  border-radius: 0.8rem;
}

.custom-collapse .collapse-title {
  padding: 0.5rem 1rem;
  min-height: auto;
}

.custom-collapse .collapse-title::after {
  top: 0.8rem;
}

.custom-collapse .collapse-content-code {
  padding-left: 0.5rem;
  padding-right: 0.5rem;
}

.custom-collapse input:checked ~ .collapse-content-code {
  padding-bottom: 0.5rem !important;
}

.mockup-code-custom {
  overflow: hidden;
  overflow-x: auto;
  position: relative;
  padding-top: 1rem;
  border-radius: var(--rounded-box, 1rem);
}

.mockup-code-custom::before {
  content: '';
  border-radius: 9999px;
  display: block;
  height: 0.75rem;
  margin-bottom: 0.5rem;
  opacity: 0.3;
  width: 0.75rem;
  box-shadow: 1em 0, 2.4em 0, 3.8em 0;
}

.link-secondary {
  text-decoration-color: hsla(var(--a) / 0.5);
}
</style>
