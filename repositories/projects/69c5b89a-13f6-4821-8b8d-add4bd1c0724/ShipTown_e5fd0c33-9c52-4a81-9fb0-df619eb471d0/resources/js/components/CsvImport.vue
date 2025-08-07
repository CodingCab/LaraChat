<template>
    <div class="vue-csv-uploader">
        <div class="form">
            <div class="vue-csv-uploader-part-one">
                <div class="form-check form-group csv-import-checkbox" v-if="headers === null">
                    <slot name="hasHeaders" :headers="hasHeaders" :toggle="toggleHasHeaders">
                        <input
                            :class="checkboxClass" type="checkbox"
                            :id="makeId('hasHeaders')"
                            :value="hasHeaders"
                            @change="toggleHasHeaders"
                        />
                        <label class="form-check-label" :for="makeId('hasHeaders')">
                            File Has Headers
                        </label>
                    </slot>
                </div>
                <div v-if="!csv" :class="{ 'drop-zone-active': isDragging }" @click="triggerFileInput" class="custom-file-upload drop-zone" @dragover.prevent="onDragOver" @dragleave.prevent="onDragLeave" @drop.prevent="onFileDrop">
                    <div class="drop-zone-prompt">
                        <i class="fas fa-cloud-upload-alt mb-2"></i>
                        <p>{{ $t('Drop your CSV file here') }}</p>
                        <p class="drop-zone-separator">or</p>
                    </div>
                    <label class="btn btn-outline-primary file-upload-label">
                        <i class="fas fa-upload mr-2"></i>{{ $t("Select CSV File") }}
                    </label>
                    <span v-if="fileSelected" class="selected-file-name ml-2">
                        {{ $refs.csv && $refs.csv.files[0] ? $refs.csv.files[0].name : '' }}
                    </span>
                </div>

                <input @change.prevent="validateFile" ref="csv" type="file" class="file-input-hidden" name="csv" accept=".csv"/>

                <button v-if="csv" type="button" @click.prevent="postCsvRecordsToApiAndCloseModal" class="col btn mb-1 btn-primary">
                    {{ $t('Import Records') }}
                </button>

                <slot name="error" v-if="showErrorMessage">
                    <div class="invalid-feedback d-block">
                        {{ $t(errorMessage) }}
                    </div>
                </slot>
            </div>
            <div class="vue-csv-uploader-part-two">
                <div class="vue-csv-mapping" v-if="sample">
                    <table :class="tableClass">
                        <slot name="thead">
                            <thead>
                            <tr>
                                <th>{{ $t('My Fields') }}</th>
                                <th>{{ $t('Column') }}</th>
                            </tr>
                            </thead>
                        </slot>
                        <tbody>
                        <tr v-for="(field, key) in fieldsToMap" :key="key">
                            <td>
                                {{ field.label }}
                            </td>
                            <td>
                                <select
                                    :class="tableSelectClass"
                                    :name="`csv_uploader_map_${key}`"
                                    v-model="map[field.key]"
                                >
                                    <option :value="null" v-if="canIgnore">{{ ignoreOptionText }}</option>
                                    <option v-for="(column, key) in firstRow" :key="key" :value="key">
                                        {{ column }}
                                    </option>
                                </select>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <div class="form-group" v-if="url">
                        <slot name="submit" :submit="submit">
                            <input type="submit" :class="buttonClass" @click.prevent="submit" :value="submitBtnText"/>
                        </slot>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import {drop, every, forEach, get, isArray, map, set} from "lodash";
import axios from 'axios';
import Papa from 'papaparse';
import mimeTypes from 'mime-types';

export default {
    props: {
        value: {
            type: [Array, Object],
            default: () => [],
        },
        url: {
            type: String,
        },
        mapFields: {
            required: true,
        },
        callback: {
            type: Function,
            default: () => ({}),
        },
        catch: {
            type: Function,
            default: () => ({}),
        },
        finally: {
            type: Function,
            default: () => ({}),
        },
        parseConfig: {
            type: Object,
            default() {
                return {};
            },
        },
        headers: {
            default: null,
        },
        loadBtnText: {
            type: String,
            default: "Next",
        },
        submitBtnText: {
            type: String,
            default: "Submit",
        },
        ignoreOptionText: {
            type: String,
            default: "",
        },
        autoMatchFields: {
            type: Boolean,
            default: false,
        },
        autoMatchIgnoreCase: {
            type: Boolean,
            default: false,
        },
        tableClass: {
            type: String,
            default: "table",
        },
        checkboxClass: {
            type: String,
            default: "form-check-input",
        },
        buttonClass: {
            type: String,
            default: "btn btn-primary",
        },
        inputClass: {
            type: String,
            default: "form-control-file",
        },
        validation: {
            type: Boolean,
            default: true,
        },
        fileMimeTypes: {
            type: Array,
            default: () => {
                return ["text/csv", "text/x-csv", "application/vnd.ms-excel", "text/plain"];
            },
        },
        tableSelectClass: {
            type: String,
            default: "form-control",
        },
        canIgnore: {
            type: Boolean,
            default: false,
        },
        resetKey: {
            type: [String, Number],
            default: null,
        },
    },

    data: () => ({
        form: {
            csv: null,
        },
        fieldsToMap: [],
        map: {},
        hasHeaders: true,
        csv: null,
        sample: null,
        isValidFileMimeType: false,
        isValidFileSize: false,
        fileSelected: false,
        isDragging: false,
    }),

    created() {
        this.initializeFromProps();
    },

    beforeDestroy() {
        this.resetComponent();
    },

    methods: {
        resetComponent() {
            this.form.csv = null;
            this.fieldsToMap = [];
            this.map = {};
            this.csv = null;
            this.sample = null;
            this.isValidFileMimeType = false;
            this.isValidFileSize = false;
            this.fileSelected = false;
            this.isDragging = false;

            if (this.$refs.csv) {
                this.$refs.csv.value = '';
            }

            this.initializeFromProps();
        },

        postCsvRecordsToApiAndCloseModal() {
            this.$emit("fileUpload", {
                csv: this.csv,
                map: this.map,
                file: this.$refs.csv,
            });
        },

        triggerFileInput() {
            this.$refs.csv.click();
        },

        onDragOver() {
            this.isDragging = true;
        },

        onDragLeave() {
            this.isDragging = false;
        },

        onFileDrop(e) {
            this.isDragging = false;
            if (e.dataTransfer.files.length) {
                // Manually set the file to the input element so existing logic works
                this.$refs.csv.files = e.dataTransfer.files;
                this.validateFile();
            }
        },
        initializeFromProps() {
            this.hasHeaders = this.headers;

            if (isArray(this.mapFields)) {
                this.fieldsToMap = map(this.mapFields, (item) => {
                    return {
                        key: item,
                        label: item,
                    };
                });
            } else {
                this.fieldsToMap = map(this.mapFields, (label, key) => {
                    return {
                        key: key,
                        label: label,
                    };
                });
            }
        },
        submit() {
            const _this = this;
            this.form.csv = this.buildMappedCsv();
            this.$emit("input", {
                csv: this.form.csv,
                map: this.map,
                file: this.$refs.csv,
            });

            if (this.url) {
                axios
                    .post(this.url, this.form)
                    .then((response) => {
                        _this.callback(response);
                    })
                    .catch((response) => {
                        _this.catch(response);
                    })
                    .finally((response) => {
                        _this.finally(response);
                    });
            } else {
                _this.callback(this.form.csv);
            }
        },
        buildMappedCsv() {
            const _this = this;

            let csv = this.hasHeaders ? drop(this.csv) : this.csv;

            return map(csv, (row) => {
                let newRow = {};

                forEach(_this.map, (column, field) => {
                    if (get(row, column)) {
                        set(newRow, field, get(row, column));
                    }
                });

                return newRow;
            });
        },
        validateFile() {
            console.log('Validating file...');
            let file = this.$refs.csv.files[0];

            console.log('File:', file);

            const mimeType = file.type === "" ? mimeTypes.lookup(file.name) : file.type;

            if (file) {
                this.fileSelected = true;
                this.isValidFileMimeType = this.validation ? this.validateMimeType(mimeType) : true;
                this.isValidFileSize = file.size < 10000000; // 10MB
            } else {
                this.isValidFileMimeType = !this.validation;
                this.fileSelected = false;
            }

            if (this.isValidFileMimeType) {
                this.load();
            }

        },
        validateMimeType(type) {
            return this.fileMimeTypes.indexOf(type) > -1;
        },

        load() {
            console.log('Loading file...');
            const _this = this;

            this.readFile((output) => {
                console.log('File read successfully:', output);
                try {
                    let parsedSample = Papa.parse(output, {preview: 2, skipEmptyLines: true});
                    let parsedFull = Papa.parse(output, {skipEmptyLines: true});
                    
                    console.log('Parsed sample:', parsedSample);
                    console.log('Parsed full:', parsedFull);
                    
                    if (parsedSample.errors && parsedSample.errors.length > 0) {
                        console.error('CSV parsing errors:', parsedSample.errors);
                        this.$emit('error', 'Failed to parse CSV file. Please check the file format.');
                        return;
                    }
                    
                    let sample = get(parsedSample, "data");
                    let csv = get(parsedFull, "data");
                    
                    console.log('Sample data:', sample);
                    console.log('CSV data:', csv);
                    
                    if (!sample || sample.length === 0) {
                        console.error('No data found in CSV file');
                        this.$emit('error', 'CSV file appears to be empty.');
                        return;
                    }
                    
                    _this.sample = sample;
                    _this.csv = csv;
                    
                    // Force Vue to update by using $set
                    _this.$set(_this, 'sample', sample);
                    _this.$set(_this, 'csv', csv);
                    
                    console.log('Data set successfully. CSV length:', _this.csv ? _this.csv.length : 0);
                } catch (error) {
                    console.error('Error parsing CSV:', error);
                    this.$emit('error', 'An error occurred while parsing the CSV file.');
                }
            });
        },

        readFile(callback) {
            let file = this.$refs.csv.files[0];

            if (file) {
                let reader = new FileReader();
                reader.readAsText(file, "UTF-8");
                reader.onload = function (evt) {
                    callback(evt.target.result);
                };
                reader.onerror = function () {
                };
            }
        },
        toggleHasHeaders() {
            this.hasHeaders = !this.hasHeaders;
        },
        makeId(id) {
            return `${id}${this._uid}`;
        },
    },
    watch: {
        map: {
            deep: true,
            handler: function (newVal) {
                if (!this.url) {
                    let hasAllKeys = Array.isArray(this.mapFields)
                        ? every(this.mapFields, function (item) {
                            return Object.prototype.hasOwnProperty.call(newVal, item);
                        })
                        : every(this.mapFields, function (item, key) {
                            return Object.prototype.hasOwnProperty.call(newVal, key);
                        });

                    if (hasAllKeys) {
                        this.submit();
                    }
                }
            },
        },
        sample(newVal) {
            this.initializeFromProps();

            if (newVal !== null) {
                this.fieldsToMap.forEach((field) => {
                    this.$set(this.map, field.key, null);

                    if (this.autoMatchFields && newVal[0]) {
                        newVal[0].forEach((columnName, index) => {
                            const fieldLabel = field.label.trim();
                            const colName = columnName.trim();

                            if (this.autoMatchIgnoreCase) {
                                if (fieldLabel.toLowerCase() === colName.toLowerCase()) {
                                    this.$set(this.map, field.key, index);
                                }
                            } else if (fieldLabel === colName) {
                                this.$set(this.map, field.key, index);
                            }
                        });
                    }
                });
            }
        },
        mapFields() {
            this.resetComponent();
        },
        resetKey() {
            this.resetComponent();
        }
    },
    computed: {
        firstRow() {
            return get(this, "sample.0");
        },
        showErrorMessage() {
            return this.fileSelected && (!this.isValidFileMimeType || !this.isValidFileSize);
        },
        errorMessage() {
            if (!this.isValidFileMimeType) {
                return `Invalid file type. Allowed types: ${this.fileMimeTypes.join(", ")}`;
            }
            if (!this.isValidFileSize) {
                return "File size exceeds the limit of 5MB.";
            }
            return "";
        }
    },
};
</script>

<style scoped>
.custom-file-upload {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
}
.file-upload-label {
    cursor: pointer;
    display: inline-block;
    padding: 0.375rem 0.75rem;
    margin-bottom: 0;
}
.file-input-hidden {
    position: absolute;
    left: -9999px;
    opacity: 0;
    width: 1px;
    height: 1px;
}
.selected-file-name {
    display: inline-block;
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    vertical-align: middle;
}
.drop-zone {
    padding: 20px;
    text-align: center;
    transition: all 0.3s;
    flex-direction: column;
}
.drop-zone-active {
    border-color: #007bff;
    background-color: rgba(0, 123, 255, 0.05);
}
.drop-zone-prompt {
    margin-bottom: 15px;
}
.drop-zone-prompt i {
    font-size: 2rem;
    color: #6c757d;
    display: block;
}
.drop-zone-separator {
    margin: 5px 0;
    color: #6c757d;
    font-size: 0.9rem;
}
</style>
