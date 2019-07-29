<template>
    <div class="form-row" :class="{ 'edit-mode': field.isedit }">
        <template v-if="'text' === field.type">
            <label for="text_field_1">{{ field.label }}</label>
            <input type="text" :name="field.name" class="regular-text" :class="field.settings.class" :id="field.settings.id" :placeholder="field.settings.placeholder">
        </template>

        <template v-if="'textarea' === field.type">
            <label for="text_field_1">{{ field.label }}</label>
            <textarea :name="field.name" :id="field.settings.id" class="regular-textarea" :class="field.settings.class" :placeholder="field.settings.placeholder"></textarea>
        </template>

        <template v-if="'checkbox' === field.type">
            <label for="checkbox_1">
                <input type="checkbox" :name="field.name" :id="field.settings.id" class="checkbox-field" :class="field.settings.class">
                <span class="checkbox-label">{{ field.label }}</span>
            </label>
        </template>

        <template v-if="'select' === field.type">
            <label for="select_field">{{ field.label }}</label>
            <select :name="field.name" :id="field.settings.id" class="select-field" :class="field.settings.class">
                <option v-if="field.settings.emptyOption != ''" value="" v-html="field.settings.emptyOption"></option>
                <option v-for="option in field.settings.options" :key="option.value" :value="option.value">{{ option.label }}</option>
            </select>
        </template>

        <template v-if="'html' === field.type">
            <div :class="field.settings.class" :id="field.settings.id">
                <h2 :style="{ 'font-size': field.settings.headingFontSize + 'px' }" v-if="field.label != ''">{{ field.label }}</h2>
                <p :style="{ 'font-size': field.settings.paraFontSize + 'px' }" v-if="field.settings.description != ''">{{ field.settings.description }}</p>
            </div>
        </template>

        <p class="desc" v-if="field.settings.description && 'html' != field.type">{{ field.settings.description }}</p>

        <div class="action">
            <span class="dashicons dashicons-admin-generic" @click.prevent="$emit( 'loadsettings', field )"></span>
            <span class="dashicons dashicons-no-alt" v-if="!isloadsettings" @click.prevent="$emit( 'deletefield', field )"></span>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'Field',

        props: {
            field: {
                type: Object,
                default() {
                    return {};
                }
            },
            isloadsettings: {
                type: Boolean,
                default() {
                    return false;
                }
            },
        },

        watch: {
            field: {
                handler( newVal, oldVal ) {
                    this.field.name = this.field.label.replace(/[_\s+-]/g, '_').toLowerCase();
                },
                deep: true
            },
        },

    };
</script>

<style lang="less">
    .form-priview {
        .form-row {
            padding: 15px;
            position: relative;

            label {
                display: block;
                margin-bottom: 5px;
                font-size: 14px;
            }

            p.desc {
                margin: 5px 0px;
                font-size: 13px;
                font-style: italic;
            }

            input[type="text"], input[type="number"], select {
                width: 50%;
            }

            textarea {
                width: 60%;
                height: 100px;
            }

            .action {
                position: absolute;
                top: 10px;
                right: 10px;
                visibility: hidden;
                span {
                    cursor: pointer;
                }
            }

            &.edit-mode {
                background: #fafafa;
            }

            &:hover {
                background: #fafafa;
                .action {
                    visibility: visible;
                }
            }

        }
    }

</style>