@component('mail::message')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => config('app.url')])
            {{ config('app.name') }}
        @endcomponent
    @endslot

    {{-- Body --}}
    <div style="max-width: 100%!important;">
        <p>{{ $date }}</p>
        <h4 class="title">{{ $message->message }}</h4>
        <p>Class: {{ $message->class }}</p>
        <p>Method: {{ $message->method }}</p>
        <p>Path: {{ $message->path }}</p>
        <p>Query: {{ $message->query }}</p>
        <p>IP: {{ $message->ip }}</p>
        <p>URI: {{ $message->uri }}</p>
        <p>Origin: {{ $message->origin }}</p>
        <p>Referer: {{ $message->referer }}</p>
        <p>X-Forwarded-For: {{ $message->forwarded }}</p>
        <p>User-Agent: {{ $message->agent }}</p>
        <p>File: {{ $message->file }}</p>
        <p>Error in line #{{ $message->line }}</p>
        <p>Trace:</p>
        <div class="code">{!! nl2br($message->trace , true) !!} </div>
    </div>

    {{-- Footer --}}
    @slot('footer')
        @component('mail::footer')
            © {{ date('Y') }} {{ config('app.name') }}. @lang('All rights reserved.')
        @endcomponent
    @endslot
@endcomponent
<style>
.title {
    background-color: #e77676;
    width: 100%;
    padding: 10px;
}
.code {
    border: 1px solid antiquewhite;
    background-color: antiquewhite;
    padding: 10px;
    border-radius: 10px;
    overflow-wrap: anywhere!important;
}
p {
    overflow-wrap: anywhere!important;
}
</style>
