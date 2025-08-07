<template>
    <div>
      <csv-import v-model="csv" headers canIgnore autoMatchFields:map-fields="map_fields">
        <template slot="hasHeaders" slot-scope="{headers, toggle}">
          <label>
            <input type="checkbox" id="hasHeaders" :value="headers" @change="toggle">
            {{ $t('Headers?') }}
          </label>
        </template>

        <template slot="error">
          {{ $t('File type is invalid') }}
        </template>

        <template slot="thead">
          <tr>
            <th>{{ $t('My Fields') }}</th>
            <th>{{ $t('Column') }}</th>
          </tr>
        </template>

        <template slot="submit" slot-scope="{submit}">
          <button @click.prevent="submit">send!</button>
        </template>
      </csv-import>
      <button v-if="csv" type="button" @click.prevent="postCsvRecordsToApiAndCloseModal" class="col btn mb-1 btn-primary">
        {{ $t('Import Records') }}
      </button>
    </div>
</template>
<script>
export default {
  name: 'import-csv-file-component',
  props: {
      csv: {},
      map_fields: [],
      postCsvRecordsToApiAndCloseModal: {}
  }
}
</script>
