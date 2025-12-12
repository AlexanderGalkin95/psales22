@extends('layouts.app')

@section('content')
<div id="app-container" class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Введите СМС код, отправленный на Ваш номер телефона</div>

                <div class="card-body">
                    <form v-show="!error_message"
                          style="display: none;"
                          method="POST"
                          action="{{ route('check.sms_code') }}">
                        @csrf
                        <div class="form-group row">
                            <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('SMS код') }}</label>

                            <div class="col-md-6">
                                <input id="sms_code"
                                       type="text"
                                       class="form-control @error('sms_code') is-invalid @enderror"
                                       name="sms_code"
                                       value="{{ old('sms_code') }}" required autofocus>

                                @error('sms_code')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    Проверить
                                </button>
                                <span id="time-wrapper" class="ml-4">
                                    <i style="font-size: inherit">
                                        <a href="javascript:void (0);"
                                           :class="{'timer': timer !== '00:00' }"
                                           style="pointer-events:auto;"
                                           @click="requestCode"
                                        >Отправить еще раз код</a>
                                        <span v-show="timer" style="display: none" id="time">@{{ timer }}</span></i></span>
                            </div>
                        </div>
                    </form>
                    <div v-show="error_message" style="color: red; text-align: center; display: none">
                        @{{error_message}}
                    </div>
                </div>
                <div v-show="error_message" class="card-footer text-right" style="color: red; text-align: center; display: none">
                    <form action="/logout" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-danger">
                            Выйти
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        new Vue({
            el: '#app-container',
            name: 'SmsCode',
            data() {
                return {
                    seconds: 60,
                    element: null,
                    tickTak: '01:00',
                    error_message: '{{$message}}'
                }
            },
            created: function () {
                this.startTimer(this.seconds)
            },
            computed: {
                timer() {
                    return this.tickTak
                }
            },
            methods: {
                startTimer: function (duration) {
                    let _this = this
                    let timer = duration, minutes, seconds
                    let interval = setInterval(function () {
                        minutes = parseInt(timer / 60, 10)
                        seconds = parseInt(timer % 60, 10)

                        minutes = minutes < 10 ? "0" + minutes : minutes
                        seconds = seconds < 10 ? "0" + seconds : seconds

                        _this.tickTak = minutes + ":" + seconds

                        if (--timer < 0) {
                            clearInterval(interval)
                        }
                    }, 1000)
                },
                requestCode() {
                    this.tickTak = '01:00'
                    this.startTimer(this.seconds)
                    axios.post('/api/smscode/request', {})
                        .then( response => {
                            this.error_message = null;
                            bootbox.alert({
                                size: 'small',
                                message: response.data.message,
                                closeButton: false
                            })
                        }, error => {
                        if(error.response.status === 403){
                            this.error_message = error.response.data.message;
                        }
                        if(error.response.status === 422){
                            this.$http.post('/logout')
                                .then(() => {
                                    window.location.href = "/login"
                                });
                        }
                    });
                }
            }
        })
    </script>
@endpush
<style>
    .timer {
        color: #B5B5C3;
        pointer-events: none!important;
    }
</style>
