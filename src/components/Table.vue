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
          <button @click="onHeaderCellClick('id')" class="btn btn-ghost btn-sm">
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
          <button @click="onHeaderCellClick('date')" class="btn btn-ghost btn-sm">
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
        <th colspan="1" rowspan="1" class="text-left pl-4">Url / Text</th>
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

        <td rowspan="1" colspan="1" class="text-center">{{ item.date.toDateString().slice(4) }}</td>

        <td rowspan="1" colspan="1">
          <div v-for="(tag, index2) in item.tags" :key="index2" class="mr-1 badge badge-ghost">{{ tag }}</div>
        </td>

        <td rowspan="1" colspan="1" class="max-w-xl p-0" @click.stop="">
          <a v-if="item.url" :href="item.url" target="_blank" class="pl-4 link link-secondary">
            {{ item.url }}
          </a>

          <ul v-else-if="item.text" class="accordion-arrow">
            <li class="accordion-item">
              <input :id="'item-' + item.id" type="checkbox" />
              <label :for="'item-' + item.id" class="text-md font-medium accordion-title">
                {{ item.text.substr(0, 15) }}...
              </label>
              <div class="accordion-body">
                <p>{{ item.text }}</p>
              </div>
            </li>
          </ul>

          <div v-else-if="item.code" class="accordion-arrow">
            <div class="accordion-item">
              <input :id="'item-' + item.id" type="checkbox" />
              <label :for="'item-' + item.id" class="text-md font-medium accordion-title">
                {{ item.code.substr(0, 15) }}...
              </label>
              <div class="accordion-body accordion-body-code cursor-auto">
                <div class="mockup-code-custom bg-base-300">
                  <Prism language="bash" :code="'npm install vue-prismjs --save\ncp ./cul abc\ntest\ntest'" :plugins="[]"></Prism>
                </div>
              </div>
            </div>
          </div>
        </td>

        <td rowspan="1" colspan="1">
          <button @click.stop="" class="btn btn-square btn-sm">
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
import Prism from '@/components/Prism.vue'
import 'prismjs/themes/prism-tomorrow.css'

type Qnote = { id: number; date: Date; tags: string[]; url?: string; text?: string; code?: string }

@Options({
  components: {
    Icon,
    Prism,
  },
  props: {
    items: Array,
  },
})
export default class Table extends Vue {
  items: Qnote[] = []
  localItems: Qnote[] = []
  sortHeader = { select: '', reverse: false }

  beforeMount() {
    this.$watch('items', () => this.syncWithProps(), { deep: true })
    this.syncWithProps()
  }

  syncWithProps() {
    this.localItems = this.items.map((val) => val)
    this.sortItems('id')
    this.sortHeader = { select: '', reverse: false }
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
    let getValue = (itm: Qnote) => itm.id
    if (by === 'date') getValue = (itm: Qnote) => itm.date.getTime()

    this.localItems.sort((itm1, itm2) => {
      const a = getValue(itm1)
      const b = getValue(itm2)
      if (reverse) return b - a
      else return a - b
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

.accordion-arrow .accordion-item .accordion-title {
  padding: 0.5rem 1rem;
}

.accordion-arrow .accordion-item .accordion-title::after {
  top: 0.8rem;
}

.accordion-item input:checked ~ .accordion-body-code {
  padding-bottom: 0.5rem;
}

.accordion-item .accordion-body-code {
  padding-left: 0.5rem;
  padding-right: 0.5rem;
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

.chevron-icon {
  transition: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

.reverse-icon {
  transform: rotateZ(180deg);
}
</style>
