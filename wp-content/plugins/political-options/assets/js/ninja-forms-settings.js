var PayPalOptionsController = Marionette.Object.extend({

    initialize: function () {
        this.appChannel = Backbone.Radio.channel('app');

        this.dynamicSettings = [
            'political_pp_amount',
            'political_pp_first_name',
            'political_pp_last_name',
            'political_pp_email',
            'political_pp_phone',
            'political_pp_address1',
            'political_pp_address2',
            'political_pp_city',
            'political_pp_state',
            'political_pp_zip',
            'political_pp_country'
        ];

        this.listenTo(Backbone.Radio.channel('settings'), 'click:edit', this.updateSelectOptions);
        this.listenTo(this.appChannel, 'response:updateDB', this.updateSettingsAfterSave);
    },

    updateSettingsAfterSave: function (response) {
        var formModel = this.getFormModel(),
            newFieldIds = {},
            newFormIds = {},
            formId = formModel.get('id'),
            needUpdate = false,
            fieldModels,
            settings;

        if ('publish' === response.action
            && 'undefined' !== typeof response.data
            && 'undefined' !== typeof response.data.new_ids) {

            // check if from is new
            if (typeof response.data.new_ids.forms !== 'undefined') {
                newFormIds = response.data.new_ids.forms;

                if (formId in newFormIds) {
                    formModel.set('id', newFormIds[formId]);
                }
            }

            // check if new fields exist
            if (typeof response.data.new_ids.fields !== 'undefined') {
                newFieldIds = response.data.new_ids.fields;
                settings = formModel.get('settings');
                fieldModels = formModel.get('fields').models;

                // update field models
                _.each(fieldModels, function (fieldModel) {
                    var id = fieldModel.get('id');

                    if (id in newFieldIds) {
                        fieldModel.set('id', newFieldIds[id].toString());
                    }
                });

                _.each(this.dynamicSettings, function (setting) {
                    var value = settings.get(setting);

                    if (value in newFieldIds) {
                        settings.set(setting, newFieldIds[value].toString());
                        needUpdate = true;
                    }
                }, this);

                if (needUpdate) {
                    settings = formModel.set('settings', settings);
                    this.appChannel.request('update:db', 'publish');
                }

            }

        }

    },

    updateSelectOptions: function (e, typeModel) {

        if (typeModel.get('id') === 'paypal') {

            var formModel = this.getFormModel(),
                settingModels = typeModel.get('settingGroups').models[0].get('settings').models,
                fieldModels = formModel.get('fields').models,
                fieldOptions = [{
                    label: (fieldModels.length > 0) ? '' : info.add_fields_text,
                    value: ''
                }];

            // create option list
            _.each(fieldModels, function (fieldModel) {
                fieldOptions.push({
                    label: fieldModel.get('label'),
                    value: fieldModel.get('id')
                });
            });

            // set new options
            _.each(settingModels, function (settingModel) {
                if (this.dynamicSettings.indexOf(settingModel.get('name')) > -1) {
                    settingModel.set('options', fieldOptions);
                }
            }, this);

        }

    },

    getFormModel: function () {
        return this.appChannel.request('get:formModel');
    }

});

jQuery(document).ready(function ($) {

    new PayPalOptionsController();

});
