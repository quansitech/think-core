function initValidator(validator, formDom){
    const subTableValidator = initSubTableValidator(validator)

    $.validator.setDefaults({
        ignore:[],
        // 配置错误处理
        errorClass: "qscmf-validation-form-error fail-alert",
        // validClass: "qscmf-validation-form-valid success-alert",
        errorPlacement: function(error, element) {
            const isSubTable =  element.closest('.data-row').length > 0
            if (!isSubTable){
                element.closest('.right').append(error);
            }else{
                error.appendTo(element.parent());
            }
        },
        // success: function(label) {
        //     label.addClass("valid").text("Ok!")
        // },
        onfocusin:null,
        onfocusout:null,
        onkeyup:null,
        onclick:null,
    });

    $.extend($.validator.messages, {
        required: "此字段必填",
        remote: "请修正此字段",
        email: "请输入有效的电子邮件地址",
        url: "请输入有效的网址",
        // date: "请输入有效的日期",
        dateISO: "请输入有效的日期 (YYYY-MM-DD)",
        number: "请输入有效的数字",
        digits: "请输入大于0的整数",
        creditcard: "请输入有效的信用卡号码",
        equalTo: "你的输入不相同",
        extension: "请输入有效的后缀",
        maxlength: $.validator.format( "最多可以输入 {0} 个字符" ),
        minlength: $.validator.format( "最少要输入 {0} 个字符" ),
        rangelength: $.validator.format( "请输入长度在 {0} 到 {1} 之间的字符串" ),
        range: $.validator.format( "请输入范围在 {0} 到 {1} 之间的数值" ),
        step: $.validator.format( "请输入 {0} 的整数倍值" ),
        max: $.validator.format( "请输入不大于 {0} 的数值" ),
        min: $.validator.format( "请输入不小于 {0} 的数值" )
    })

    const opt = {
        // debug: true,
        // 配置验证规则
        rules:{...validator?.rules},
        messages:{...validator?.messages},
    }

    formDom.validate(opt);

    formDom.validate().settings.subTableName = validator.sub_table_name
    formDom.validate().settings.subTableKeys = validator.sub_table_field
    formDom.validate().settings.subTableRules = subTableValidator?.rules
    formDom.validate().settings.subTableMessages = subTableValidator?.messages
}

function reKeyValidator(validator, index){
    let newValidator = {
        rules:{},
        messages:{},
    }
    for (const [key, value] of Object.entries(validator?.rules)) {
        const name = `${key}[${index}]`;
        newValidator.rules[name] = value;
        if(validator?.messages?.[key]) newValidator.messages[name] = validator?.messages?.[key];
    }

    return newValidator;
}

function extractOneRowRules(settings,column_keys, index){
    const formValidator = {
        rules:filterObject(settings.subTableRules
            , (item, key) => column_keys.indexOf(key) !== -1),

        messages:filterObject(settings.subTableMessages
            , (item, key) => column_keys.indexOf(key) !== -1),
    }

    return reKeyValidator(formValidator, index)
}

function initSubTableValidator(validator){
    const validatorBk = {rules:{...validator.rules}, messages:{...validator.messages}}

    const subTableRules = filterObject(validatorBk?.rules, function(value, key, object) {
        const hasExists = validator.sub_table_field.indexOf(key) !== -1;
        if (hasExists){
            const newKey = `${key}[0]`
            delete validator.rules[key]
            validator.rules[newKey] = value;
        }
        return hasExists;
    });

    const subTableMessages = filterObject(validatorBk?.messages, function(value, key, object) {
        const hasExists = validator.sub_table_field.indexOf(key) !== -1;
        if (hasExists){
            const newKey = `${key}[0]`
            delete validator.messages[key]
            validator.messages[newKey] = value;
        }
        return hasExists;
    });

    return {rules: subTableRules, messages: subTableMessages}
}

function unSerializeFormData(serializedString){
    const str = decodeURIComponent(serializedString); // 对编码的字符串进行解码

    const pairs = str.split('&');
    let obj = {}, p, idx, val;

    let i = 0, n = pairs.length;
    for (; i < n; i++) {
        p = pairs[i].split('=');
        idx = p[0];
        if (idx.length === 0) {
            continue;
        }
        val = p[1] === undefined ? null : p[1];
        obj[idx] = val;
    }

    return obj;
}

function rebuildKeys(formData, subTableKey){
    let output = {...formData};
    subTableKey.forEach(key => {
        // 提取出所有符合当前key的条目
        let filteredKeys = Object.keys(formData).filter(k => k.startsWith(key));
        filteredKeys.forEach((originalKey, index) => {
            // 创造新的键名称，基于index重构
            let newKey = `${key}[${index}]`;
            // 根据新键名称和原始键值对重构输出对象
            delete output[originalKey];
            output[newKey] = formData[originalKey];
        });
    });
    return output;
}

function reIndexSubTable(serializedString, subTableKey){
    const formData = unSerializeFormData(serializedString);

    return rebuildKeys(formData, subTableKey);
}

function injectSubTableNameInput(formDom){
    const name = formDom.validate().settings.subTableName;
    const value = formDom.validate().settings.subTableKeys;

    const inputDom = $('input[name="' + name + '"]');

    if (inputDom.length > 0) {
        inputDom.val(value);
    } else {
        formDom.append('<input type="hidden" name="' + name + '" value="' + value + '">');
    }
}

function validateForm(form){
    const validateRes = form.valid();
    const customValidateRes = customValidateForm(form);

    return !(validateRes === false || customValidateRes === false);
}

function customValidateForm(form){
    let customValidateErrorMap = {};
    form.trigger('customValidatorItem', [form, customValidateErrorMap])

    return Object.keys(customValidateErrorMap).length === 0;
}

function validateVisibilityDom(form, hiddenDom, visibilityDom){
    const name = $(visibilityDom).data('qs-validator-name');
    const rules = form.validate().settings.rules?.[name];
    const messages = form.validate().settings.messages?.[name];

    $(visibilityDom).rules('add', {
        ...rules,
        messages:{...messages}
    });

    $(hiddenDom).rules('remove');
}

function hasSetValidate(form, name) {
    return form.validate().settings.rules.hasOwnProperty(name);
}

function hasError(form, name){
    return form.validate().errorMap.hasOwnProperty(name);
}

function updateDomErrorClass(form, name, dom){
    if(hasSetValidate(form, name)){
        const errorClass = form.validate().settings.errorClass

        if(hasError(form, name)){
            dom.addClass(errorClass)
            return false
        }else{
            dom.removeClass(errorClass)
            return true
        }
    }

    return true
}
