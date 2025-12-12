import bootbox from "bootbox";
import js_settings from './js_settings'
import moment from "moment"

let srx = {
    bootboxAlert(msg) {
        bootbox.alert({
            size: 'small',
            message: msg,
            closeButton: false
        });
    },
    bootboxDialog(title, message, buttons) {
        bootbox.dialog({
            title: title,
            message: message,
            buttons: buttons
        });
    },
    bootboxDialogSmall(title, message, buttons)
    {
        bootbox.dialog({
            title: title,
            message: message,
            buttons: buttons,
            size: 'small'
        });
    },
    getRandomId: function (){
        return Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
    },

    explode ( delimiter, string ) {
        var emptyArray = {0: ''};
        if (arguments.length !== 2
            || typeof arguments[0] == 'undefined'
            || typeof arguments[1] == 'undefined') {
            return null;
        }
        if (delimiter === ''
            || delimiter === false
            || delimiter === null) {
            return false;
        }
        if (typeof delimiter == 'function'
            || typeof delimiter == 'object'
            || typeof string == 'function'
            || typeof string == 'object') {
            return emptyArray;
        }
        if (delimiter === true) {
            delimiter = '1';
        }
        return string.toString().split(delimiter.toString());
    },

    isInt(n){
        return Number(n) === n && n % 1 === 0;
    },

    customDate(dt) {
        if (srx.isInt(dt)) {
            return moment.unix(dt).format(js_settings.formats.date);
        }
        return  (dt === undefined || dt === null || dt === '') ? '' : moment(dt).format(js_settings.formats.date);
    },
    customDateTime(dt) {
        if (srx.isInt(dt)) {
            return moment.unix(dt).format(js_settings.formats.datetime);
        }
        return  (dt === undefined || dt === null || dt === '') ? '' : moment(dt).format(js_settings.formats.datetime);
    },
    customDateTimeMS(dt) {
        if (srx.isInt(dt)) {
            return moment.unix(dt).format(js_settings.formats.datetimems);
        }
        return  (dt === undefined || dt === null || dt === '') ? '' : moment(dt).format(js_settings.formats.datetimems);
    },
    customDateTimeFull(dt) {
        if (srx.isInt(dt)) {
            return moment.unix(dt).format(js_settings.formats.longdatetime);
        }
        return  (dt === undefined || dt === null || dt === '') ? '' : moment(dt).format(js_settings.formats.longdatetime);
    }
}


export default srx;
