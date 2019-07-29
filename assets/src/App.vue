<template>
    <div id="wcrw-form-builder">
        <div id="message" class="info notice notice-info is-dismissible" v-if="message">
            <p>{{ message }}</p>
            <button type="button" class="notice-dismiss" @click.prevent="message = ''">
                <span class="screen-reader-text">Dismiss this notice.</span>
            </button>
        </div>
        <div class="form-section">
            <div class="form-priview">
                <draggable v-model="form" :sort="!isLoadSettings">
                    <field v-for="(field,index) in form" :key="index" :isloadsettings="isLoadSettings" :field="field" @loadsettings="loadSettings" @deletefield="deleteFormField"></field>
                </draggable>
                <div class="no-form-element-found" v-if="form.length <= 0">
                    No form element found
                </div>
            </div>
            <div class="form-elements" v-if="! isLoadSettings">
                <h3>Form Fields</h3>
                <div class="form-field-button">
                    <template v-for="formField in formFields">
                        <button class="button button-default" @click.prevent="addField(formField)" v-html="formField.label"></button>
                    </template>
                </div>
            </div>
            <div class="form-elements" v-if="isLoadSettings">
                <h3>
                    Field Settings
                    <a href="#" @click.prevent="cancelLoadSettings">&larr; Back to Fields</a>
                </h3>
                <div class="form-field-settings">
                    <div class="form-row">
                        <label for="field-name">Label</label>
                        <input type="text" class="regular-text" v-model="form[selectedFieldIndex].label">
                    </div>
                    <div class="form-row">
                        <label for="field-name">Description</label>
                        <input type="text" class="regular-text" v-model="form[selectedFieldIndex].settings.description">
                    </div>
                    <div class="form-row" v-if="['select', 'checkbox', 'html'].indexOf( form[selectedFieldIndex].type ) == '-1'">
                        <label for="field-name">Placeholder</label>
                        <input type="text" class="regular-text" v-model="form[selectedFieldIndex].settings.placeholder">
                    </div>
                    <div class="form-row">
                        <label for="field-name">Class attribute</label>
                        <input type="text" class="regular-text" v-model="form[selectedFieldIndex].settings.class">
                    </div>
                    <div class="form-row">
                        <label for="field-name">ID attribute</label>
                        <input type="text" class="regular-text" v-model="form[selectedFieldIndex].settings.id">
                    </div>
                    <div class="form-row">
                        <label for="field-name">Wrapper Class</label>
                        <input type="text" class="regular-text" v-model="form[selectedFieldIndex].settings.wrapperClass">
                    </div>
                    <div class="form-row" v-if="['html'].indexOf( form[selectedFieldIndex].type ) == '-1'">
                        <label for="required-field" class="checkbox">
                            <input type="checkbox" id="required-field" class="checkbox" v-model="form[selectedFieldIndex].settings.required">
                            Is Required ?
                        </label>
                    </div>
                    <template v-if="form[selectedFieldIndex].type == 'select'">
                        <div class="form-row">
                            <label for="field-name">Options</label>
                            <textarea rows="4" v-model="selectFieldOption" class="regular-text"></textarea>
                        </div>
                        <div class="form-row">
                            <label for="empty-option">Select option text (Leave empty of no need)</label>
                            <input type="text" id="empty-option" v-model="form[selectedFieldIndex].settings.emptyOption" class="regular-text">
                        </div>
                    </template>
                    <template v-if="form[selectedFieldIndex].type == 'textarea'">
                        <div class="form-row">
                            <label for="row-option">Rows</label>
                            <input type="text" id="row-option" v-model="form[selectedFieldIndex].settings.row" class="regular-text">
                        </div>
                    </template>
                    <template v-if="form[selectedFieldIndex].type == 'html'">
                        <div class="form-row">
                            <label for="heading-type">Heading Font Size (px)</label>
                            <input type="number" v-model="form[selectedFieldIndex].settings.headingFontSize" class="regular-text">
                        </div>
                        <div class="form-row">
                            <label for="heading-type">Paragraph Font Size (px)</label>
                            <input type="number" v-model="form[selectedFieldIndex].settings.paraFontSize" class="regular-text">
                        </div>
                    </template>
                    <template v-if="form[selectedFieldIndex].type == 'number'">
                        <div class="form-row">
                            <label for="field-name">Min value</label>
                            <input type="text" class="regular-text" v-model="form[selectedFieldIndex].settings.min">
                        </div>
                        <div class="form-row">
                            <label for="field-name">Max value</label>
                            <input type="text" class="regular-text" v-model="form[selectedFieldIndex].settings.max">
                        </div>
                        <div class="form-row">
                            <label for="field-name">Step</label>
                            <input type="text" class="regular-text" v-model="form[selectedFieldIndex].settings.step">
                        </div>
                    </template>
                </div>
            </div>
        </div>
        <button class="button button-primary form-save-button" @click.prevent="saveFormSettings">Save Form Settings</button>
    </div>
</template>

<script>

import Field from './components/Field';
import draggable from 'vuedraggable'

export default {
    name: 'App',

    components: {
        Field,
        draggable
    },

    data() {
        return {
            message: '',
            formFields: [],
            selectFieldOption: '',
            isSelectEmptyVal: false,
            isLoadSettings: false,
            selectedFieldIndex: null,
            form: []
        }
    },

    watch: {
        isSelectEmptyVal(val) {
            if ( val ) {
                this.form[this.selectedFieldIndex].settings.options.unshift({
                    value: '',
                    label: 'Select a option'
                });
            }
        },
        selectFieldOption( val ) {
            var options = val.split("\n");
            this.form[this.selectedFieldIndex].settings.options = [];
            options.forEach( ( option ) => {
                if ( option.toString().trim() == '' ) {
                    return;
                }
                this.form[this.selectedFieldIndex].settings.options.push({
                    value: option.replace(/[_\s+-]/g, '_').toLowerCase(),
                    label: option
                });
            } )
        }
    },

    methods: {
        getFromFields() {
            this.formFields = wcrwForms.form_fields;

            jQuery('.form-section').block({ message: null, overlayCSS: { background: '#fff url(' + wcrwForms.ajax_loader + ') no-repeat center', opacity: 0.4 } });

            var self = this,
                data = {
                    action: 'wcrw_get_builder_form_data',
                    nonce: wcrwForms.nonce,
                };

            jQuery.post( wcrwForms.ajaxurl, data, function(resp) {
                if ( resp.success ) {
                    self.form = resp.data;
                    jQuery('.form-section').unblock();
                }
            });
        },

        addField( formField ) {
            this.form.push( formField );
        },

        loadSettings( field ) {
            this.isLoadSettings = true;
            var index = this.form.indexOf(field);
            this.selectedFieldIndex = index;
            this.form.forEach( ( form ) => {
                form.isedit = false;
            } );
            this.form[index].isedit = true;

            // If filed is select then handle options and default values
            if ( field.type == 'select' ) {
                var optionString = '';
                this.form[index].settings.options.forEach( ( form, key ) => {
                    optionString += form.label + '\n';
                } );
                this.selectFieldOption = optionString;
            }
        },

        cancelLoadSettings() {
            this.isLoadSettings = false;
            this.selectedFieldIndex = null;
            this.form.forEach( ( form ) => {
                form.isedit = false;
            } );
        },

        deleteFormField(field) {
            this.form.splice( this.form.indexOf(field), 1 );
        },

        saveFormSettings() {
            jQuery('.form-section').block({ message: null, overlayCSS: { background: '#fff url(' + wcrwForms.ajax_loader + ') no-repeat center', opacity: 0.4 } });

            var self = this;
            var data = {
                action: 'wcrw_save_builder_form_data',
                nonce: wcrwForms.nonce,
                formData: JSON.stringify( this.form )
            };

            jQuery.post( wcrwForms.ajaxurl, data, function(resp) {
                if ( resp.success ) {
                    self.message = resp.data;
                    jQuery('.form-section').unblock();
                }
            });
        }
    },

    created() {
        this.getFromFields();
    }
};

</script>

<style lang="less">
    #wcrw-form-builder {

        .form-save-button {
            position: absolute;
            top: 20px;
            right: 20px;
        }

        .form-section {
            display: flex;
            .form-priview {
                flex: 2;
                background: #fff;
                .no-form-element-found {
                    padding: 20px;
                    font-size: 15px;
                }
            }

            .form-elements {
                flex: 1;
                padding: 20px;
                background: #fafafa;

                h3 {
                    margin: 0px 0px 15px;
                    padding: 0px 0px 15px 0px;
                    border-bottom: 1px solid #eee;

                    a {
                        font-size: 14px;
                        float: right;
                        font-weight: normal;
                        text-decoration: none;
                    }
                }

                .form-field-button {
                    button {
                        margin-right: 10px;
                        margin-bottom: 10px;
                    }
                }

                .form-field-settings {
                    .form-row {
                        margin: 10px 0px;

                        label {
                            display: block;
                            margin-bottom: 5px;
                        }
                    }
                }
            }
        }
    }
</style>