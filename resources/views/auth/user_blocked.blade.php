@extends('layouts.app')

@section('content')
<div id="app-container" class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header" style="font-weight: bold;"><i class="fa fa-exclamation-triangle" aria-hidden="true" style="color:red;"></i> Внимание!</div>

                <div class="card-body">
                    <div class="text-center">
                        Пользователь заблокирован. Обратитесь к системному администратору.
                    </div>
                </div>
                <div class="card-footer text-right">
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
            name: 'userBlocked',
            data() {
                return {
                }
            },
            methods: {

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
