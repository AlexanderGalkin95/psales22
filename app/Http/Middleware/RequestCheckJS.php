<?php

namespace App\Http\Middleware;

//use App\Mail\SuspiciousActivity;
use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class RequestCheckJS
{
    private $errors = [];
    const RESTRICTED_SYMBOLS = [
        '/javascript',
        'javascript:',
        '</a',
        '<a',
        '</script',
        '<script',
        'onactivate',
        'onafterprint',
        'onafterscriptexecute',
        'onanimationcancel',
        'onanimationend',
        'onanimationiteration',
        'onanimationstart',
        'onauxclick',
        'onbeforeactivate',
        'onbeforecopy',
        'onbeforecut',
        'onbeforedeactivate',
        'onbeforepaste',
        'onbeforeprint',
        'onbeforescriptexecute',
        'onbeforeunload',
        'onbegin',
        'onblur',
        'onbounce',
        'oncanplay',
        'oncanplaythrough',
        'onchange',
        'onclick',
        'onclose',
        'oncontextmenu',
        'oncopy',
        'oncuechange',
        'oncut',
        'ondblclick',
        'ondeactivate',
        'ondrag',
        'ondragend',
        'ondragenter',
        'ondragleave',
        'ondragover',
        'ondragstart',
        'ondrop',
        'ondurationchange',
        'onend',
        'onended',
        'onerror',
        'onfinish',
        'onfocus',
        'onfocusin',
        'onfocusout',
        'onfullscreenchange',
        'onhashchange',
        'oninput',
        'oninvalid',
        'onkeydown',
        'onkeypress',
        'onkeyup',
        'onload',
        'onloadeddata',
        'onloadedmetadata',
        'onloadend',
        'onloadstart',
        'onmessage',
        'onmousedown',
        'onmouseenter',
        'onmouseleave',
        'onmousemove',
        'onmouseout',
        'onmouseover',
        'onmouseup',
        'onmousewheel',
        'onmozfullscreenchange',
        'onpagehide',
        'onpageshow',
        'onpaste',
        'onpause',
        'onplay',
        'onplaying',
        'onpointerdown',
        'onpointerenter',
        'onpointerleave',
        'onpointermove',
        'onpointerout',
        'onpointerover',
        'onpointerrawupdate',
        'onpointerup',
        'onpopstate',
        'onprogress',
        'onreadystatechange',
        'onrepeat',
        'onreset',
        'onresize',
        'onscroll',
        'onsearch',
        'onseeked',
        'onseeking',
        'onselect',
        'onselectionchange',
        'onselectstart',
        'onshow',
        'onstart',
        'onsubmit',
        'ontimeupdate',
        'ontoggle',
        'ontouchend',
        'ontouchmove',
        'ontouchstart',
        'ontransitioncancel',
        'ontransitionend',
        'ontransitionrun',
        'ontransitionstart',
        'onunhandledrejection',
        'onunload',
        'onvolumechange',
        'onwaiting',
        'onwebkitanimationend',
        'onwebkitanimationiteration',
        'onwebkitanimationstart',
        'onwebkittransitionend',
        'onwheel'
    ];

    public function handle($request, Closure $next)
    {
//            $params = $request->all();
//            $params_convert = [];
//
//            $this->checkParam($params, $params_convert, null);
//
//            if (count($this->errors) > 0) {
//                $request->merge($params_convert);
//                $request_info = [
//                    'path' => $request->path(),
//                    'method' => $request->method()
//                ];
//                $message = 'Suspicious activity, or possibly a system error. User ' . (Auth::user() ? Auth::user()->fullname : '') . ' (ID = ' . (Auth::user() ? Auth::user()->id : '?') . ') was sent request with suspicious parameters';
//                Mail::to(config('mail.sysadmin_email_address'))
//                    ->queue(new SuspiciousActivity(
//                        $message,
//                        Carbon::now(),
//                        $request_info,
//                        $this->errors
//                    ));
//            }

        return $next($request);
    }

    private function checkParam($params, &$params_convert, $index)
    {
        foreach ($params as $i => $param) {
            if (is_array($param)) {
                $params_convert[$i] = [];
                $this->checkParam($param, $params_convert[$i], ($index ? $index . '.' : '') . $i);
                continue;
            }
            if (is_string($param) && $this->suspiciousString($param)) {
                $this->errors[($index ? $index . '.' : '') . $i] = $param;
                $params_convert[$i] = htmlspecialchars($param);
                continue;
            }
            $params_convert[$i] = $param;
        }
    }

    private function suspiciousString($param)
    {

        foreach (self::RESTRICTED_SYMBOLS as $item) {
            if (stripos($param, $item) !== false) {
                return true;
            }
        }

        return false;
    }
}
