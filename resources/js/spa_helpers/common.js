export function gbNumber(num)
{
    var fractionalPart = '.';
    var delimiter = ',';
    if (num === undefined || num === null || isNaN(num)) {
        return '0';
    }

    return roundPlus(num,0).toFixed(0).toString().replace(',','.').replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
}

export function isEmpty(val)
{
    return (val ===  null || val === '' || val === undefined || val === 0 || val === '0' || val === '.00' || val === '0.00');
}

export function isEmptyObj(obj)
{
    for (var prop in obj) {
        if (obj.hasOwnProperty(prop)) {
            return false;
        }
    }

    return JSON.stringify(obj) === JSON.stringify({});
}

export function roundPlus(x, n)
{
    //x - The number, n - The number of decimals after a comma
    if (isNaN(x) || isNaN(n)) {
        return 0;
    }
    var m = Math.pow(10,n);
    return Math.round(x * m) / m;
}

export function getObjectVal(obj,is, value)
{
    if (is === undefined) {
        return undefined;
    }
    if (typeof is == 'string') {
        return getObjectVal(obj,is.split('.'), value);
    } else if (is.length == 1 && value !== undefined) {
        return obj[is[0]] = value;
    } else if (is.length == 0) {
        return obj;
    } else if (!isEmpty(obj[is[0]])) {
        return getObjectVal(obj[is[0]],is.slice(1), value);
    } else {
        return undefined;
    }
}
