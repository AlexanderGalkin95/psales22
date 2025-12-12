import { ValidationProvider, ValidationObserver, extend } from 'vee-validate';
import  * as rules from 'vee-validate/dist/rules';
import ru from 'vee-validate/dist/locale/ru';
import * as VeeValidate from 'vee-validate';
import _ from 'lodash'

// Install the Plugin.
Vue.use(VeeValidate);

// Localize takes the locale object as the second argument (optional) and merges it.
VeeValidate.localize('ru', ru);

Object.keys(rules).forEach(function (rule) {
    extend(rule, rules[rule]);
});

extend('totaltimelimit', {
    params: ['target'],
    validate(value, { target }) {
        return target === 'false'
    },
    message: 'Сумма минут менеджеров не должна превышать лимит на проект'
});

extend('valid_phone', {
    params: ['target'],
    validate(value, { target }) {
        return target === 'true'
    },
    message: 'Введенный номер телефона неправильный'
});

extend('confirm', {
    params: ['target'],
    validate(value, { target }) {
        return value === target;
    },
    message: 'Пароли не совпадают'
});

extend('unique', {
    params: ['columns'],
    validate: (value, {columns},) => {
        columns = _.split(columns, ',')
        if(columns.length > 1) {
            let col = _.clone(columns)
            col.splice(col.indexOf(value),1)
            let valid = _.find(col, item => {
                return item === value;
            })
            return !valid;
        } else {
            return true;
        }

    },
    message: 'Такое значение поля уже существует.'
});

Vue.component('validation-provider', ValidationProvider);
Vue.component('validation-observer', ValidationObserver);
