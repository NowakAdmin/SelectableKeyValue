<template>
  <DefaultField
    :field="currentField"
    :errors="errors"
    :full-width-content="
      fullWidthContent || ['modal', 'action-modal'].includes(mode)
    "
    :show-help-text="showHelpText"
  >
    <template #field>
      <FormTable
        v-show="theData.length > 0"
        :edit-mode="!currentlyIsReadonly"
        :can-delete-row="currentField.canDeleteRow"
      >
        <FormHeader
          :key-label="currentField.keyLabel"
          :value-label="currentField.valueLabel"
        />

        <div class="bg-white dark:bg-gray-800 overflow-hidden key-value-items">
          <FormItem
            v-for="(item, index) in theData"
            :index="index"
            @remove-row="removeRow"
            :item.sync="item"
            :key="item.id"
            :ref="item.id"
            :read-only="currentlyIsReadonly"
            :read-only-keys="currentField.readonlyKeys"
            :can-delete-row="currentField.canDeleteRow"
          />
        </div>
      </FormTable>

      <div class="flex items-center justify-center">
        <Button
          v-if="
            !currentlyIsReadonly &&
            !currentField.readonlyKeys &&
            currentField.canAddRow
          "
          @click="addRowAndSelect"
          :dusk="`${field.attribute}-add-key-value`"
          leading-icon="plus-circle"
          variant="link"
        >
          {{ currentField.actionText }}
        </Button>
      </div>
    </template>
  </DefaultField>
</template>

<script>
import findIndex from 'lodash/findIndex'
import fromPairs from 'lodash/fromPairs'
import reject from 'lodash/reject'
import tap from 'lodash/tap'
import { DependentFormField, HandlesValidationErrors } from 'laravel-nova'
import { Button } from 'laravel-nova-ui'

function guid() {
  var S4 = function () {
    return (((1 + Math.random()) * 0x10000) | 0).toString(16).substring(1)
  }
  return (
    S4() +
    S4() +
    '-' +
    S4() +
    '-' +
    S4() +
    '-' +
    S4() +
    '-' +
    S4() +
    S4() +
    S4()
  )
}

export default {
  mixins: [HandlesValidationErrors, DependentFormField],

  components: {
    Button,
  },

  data: () => ({ theData: [] }),

  mounted() {
    this.populateKeyValueData()
  },

  methods: {
    /*
     * Set the initial value for the field
     */
    populateKeyValueData() {
      this.theData = Object.entries(this.value || {}).map(([key, value]) => ({
        id: guid(),
        key: `${key}`,
        value,
      }))
    },

    /**
     * Provide a function that fills a passed FormData object with the
     * field's internal value attribute.
     */
    fill(formData) {
      this.fillIfVisible(
        formData,
        this.fieldAttribute,
        JSON.stringify(this.finalPayload)
      )
    },

    /**
     * Add a row to the table.
     */
    addRow() {
      // Use the first available option as default key
      const options = this.currentField.meta && this.currentField.meta.options ? this.currentField.meta.options : {}
      const firstKey = Object.keys(options)[0] || ''
      return tap(guid(), id => {
        this.theData = [...this.theData, { id, key: firstKey, value: '' }]
        return id
      })
    },

    /**
     * Add a row to the table and select its first field.
     */
    addRowAndSelect() {
      return this.selectRow(this.addRow())
    },

    /**
     * Remove the row from the table.
     */
    removeRow(id) {
      return tap(
        findIndex(this.theData, row => row.id === id),
        index => this.theData.splice(index, 1)
      )
    },

    /**
     * Select the first field in a row with the given ref ID.
     */
    selectRow(refId) {
      return this.$nextTick(() => {
        const ref = this.$refs[refId]
        if (ref && ref[0] && typeof ref[0].handleKeyFieldFocus === 'function') {
          ref[0].handleKeyFieldFocus()
        } else if (ref && ref[0] && ref[0].$refs && ref[0].$refs.keyField) {
          // Fallback: focus the select element directly if handleKeyFieldFocus is not present
          ref[0].$refs.keyField.focus()
        }
      })
    },

    onSyncedField() {
      this.populateKeyValueData()
    },
  },

  computed: {
    /**
     * Return the final filtered json object
     */
    finalPayload() {
      return fromPairs(
        reject(
          this.theData.map(row =>
            row && row.key ? [row.key, row.value] : undefined
          ),
          row => row === undefined
        )
      )
    },
  },
}
</script>
