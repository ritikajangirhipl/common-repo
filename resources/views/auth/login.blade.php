
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <title>{{$data['appName']}}</title>
        <link href="{{$data['favicon']}}" rel="icon">
        <!-- font-awesome Start  -->
        <link rel="stylesheet" href="{{ asset('vendor/common-repo/css/font-awesome.min.css') }}">
        <!-- Bootstrap css -->
        <link rel="stylesheet" type="text/css" href="{{ asset('vendor/common-repo/css/bootstrap.min.css') }}"> 
        <!-- toastr css -->
        <link rel="stylesheet" href="{{ asset('vendor/common-repo/toastr/toastr.min.css') }}">
        <!-- style css -->
        <link rel="stylesheet" href="{{ asset('assets/css/theme.css')}}">
        <link rel="stylesheet" href="{{ asset('vendor/common-repo/css/login.css') }}">
    </head>

    <body class="{{($data['appEnv'] == 'staging') ? 'staging-logged-in' : ''}}">
        @if($data['appEnv'] == 'staging')
            @include('common::banner.button')
        @endif
        <div class="login-wrapper">
            <img src="{{asset('assets/images/loginbg.jpg')}}" class="img-fluid login-background" alt="">
            <div class="container">
                <form class="form_wrapper" id="loginForm" method="POST">
                    <div class="top-content-login">
                    <img src="{{asset('assets/images/logo.png')}}" alt="logo" title="logo-img">
                        <h3 class="login-title">{{__('global.login')}}</h3>
                    </div>
                    <div class="mb-3">
                        <label for="exampleInputEmail1" class="form-label">{{__('global.email')}}<span class="mailstar text-danger">*</span></label>
                        <input type="text" class="form-control" placeholder="{{__('global.enter').' '.__('global.email')}}" id="exampleInputEmail1" name="email" value="{{$credentials['email'] ?? ''}}">
                    </div>
                    <div class="mb-3">
                        <label for="loginPassword" class="tooltiplabel">{{__('global.password')}}<span class="mailstar text-danger">*</span> 
                        <span class="tooltipIcon" data-bs-toggle="tooltip" data-bs-placement="top" title="{{__('global.password_hint',['min' => $data['passwordMinLength'], 'max' => $data['passwordMaxLength']])}}"><img src="{{ asset('assets/images/information.png') }}"/></span></label>

                        <div class="input-password-wrap">
                            <input type="password" placeholder="{{__('global.enter').' '.__('global.password')}}" class="form-control" id="loginPassword" name="password" maxlength="32" autocomplete="new-password" value="{{$credentials['password'] ?? ''}}">
                            <i class="fa fa-eye-slash" id="togglePassword" style="margin-left: -30px; cursor: pointer;"></i>
                        </div>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="exampleCheck1" name="remember_me" {{ $remember ? 'checked' : '' }}>
                        <label class="form-check-label" for="exampleCheck1">{{__('global.remember_me')}}</label>
                    </div>
                    <button type="submit" class="nbtn nextstepbtn" id="loginSubmit">{{__('global.submit')}}</button>
                </form>
            </div>
        </div>
        @if($data['appEnv'] == 'staging')
            @include('common::banner.modal')
        @endif
        
        <!-- Jquery Library -->
        <script src="{{asset('vendor/common-repo/js/jquery-3.7.1.min.js')}}"></script>
        <!-- toastr js -->
        <script src="{{ asset('vendor/common-repo/toastr/toastr.min.js') }}"></script>
        <!-- Bootstrap Js -->
        <script src="{{asset('vendor/common-repo/js/bootstrap.bundle.min.js')}}"></script>
        <script src="{{asset('vendor/common-repo/js/jquery.validate.min.js')}}"></script> 

       <script>
        $(document).ready(function(){
            var minPasswordLength  = {{ $data['passwordMinLength'] }};
            var maxPasswordLength  = {{ $data['passwordMaxLength'] }};

            toastr.options = {
                'closeButton': true,
                'debug': false,
                'newestOnTop': false,
                'progressBar': false,
                'positionClass': 'toast-top-right',
                'preventDuplicates': false,
                'showDuration': '1000',
                'hideDuration': '1000',
                'timeOut': '5000',
                'extendedTimeOut': '1000',
                'showEasing': 'swing',
                'hideEasing': 'linear',
                'showMethod': 'fadeIn',
                'hideMethod': 'fadeOut',
            }
            
            @if(Session::has('message'))

                var type = "{{ Session::get('alert-type', 'info') }}";

                switch (type) {
                    case 'info':
                        toastr.info("{{ Session::get('message') }}", 'Info!');
                        break;

                    case 'warning':
                        toastr.warning("{{ Session::get('message') }}", 'Warning!');
                        break;
                    case 'success':
                        toastr.success("{{ Session::get('message') }}", 'Success!');
                        break;
                    case 'error':
                        toastr.error("{{ Session::get('message') }}", 'Error');
                        break;
                }
            @endif

            $('#togglePassword').on('click', function(e) {
                const password = $('#loginPassword');
                const type = password.attr('type') === 'password' ? 'text' : 'password';
                password.attr('type', type);
                $(this).toggleClass('fa-eye');
            });

            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })

            $('input[name=email], input[name=password]').on('input',function(){
                $(this).val($(this).val().replace(/\s/g, ''));
            });

            $('#loginForm').validate({
                ignore: '.ignore',
                focusInvalid: false,
                rules: {
                    'email': {
                        required: true,
                    },
                    'password': {
                        required: true,
                        minlength: minPasswordLength,
                        maxlength: maxPasswordLength
                    }
                },

                errorElement: 'span',
                errorPlacement: function(error, element) {
                    $('body').find('.contactError').remove();
                    error.addClass('invalid-feedback');
                    error.insertAfter(element);
                },
                highlight: function(element, errorClass, validClass) {
                    $('body').find('.contactError').remove();
                    $(element).addClass('is-invalid');
                },
                unhighlight: function(element, errorClass, validClass) {
                    $(element).removeClass('is-invalid');
                },
            });

            $('#loginPassword').on('keyup', function() {
                var passwordValue = $(this).val();
                if(passwordValue.length >= minPasswordLength && passwordValue.length <= maxPasswordLength){
                    $('#loginSubmit').prop('disabled', false);
                } else {
                    $('#loginForm').valid();
                    $('#loginSubmit').prop('disabled', true);
                }
            });

            $("body").on("submit","#loginForm",function(e){
                e.preventDefault();
                $('body').find('.contactError').remove();
                if($(this).valid()){
                    var url = '{{route("login_submit")}}';
                    var email = $('input[name=email]').val();
                    var password = $('input[name=password]').val();
                    var remember_me = $('input[name=remember_me]').is(":checked");
                    $('body').find('#loginSubmit').prop('disabled', true);
                    $.ajax({
                        type: "POST",
                        url: url,
                        data: {
                            email : email,
                            password : password,
                            remember_me : remember_me,
                        },
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}"
                        },
                        beforeSend:function(){
                            $('.overlay').show();
                        },
                        success: function(response) {
                            $("#loginForm")[0].reset();
                            if (typeof response === "string" && response.includes("<html")) window.location.href = '{{route("admin.2fa")}}';
                            else window.location.replace(response.url);
                        },
                        error: function(response) {
                            var result = response.responseJSON.message; 
                            if(response.status == 403 || result.code == 1010 || result.code == 1009 || result.code == 1006 || result.code == 500){
                                toastr.error(result.data ? result.data.message : result, 'Error');
                            }else{
                                $.each(result, function(key, value) {
                                    var nameAttr = value.field.split(".")[1].toLowerCase();
                                    
                                    var $field = $('[name="' + nameAttr + '"]');
                                    $field.addClass('is-invalid');
                                    $field.after('<span class="text-danger contactError"> This ' + nameAttr  + ' field is '+value.tag+'</span>');
                                });
                            }
                        },
                        complete: function() {
                            $('body').find('#loginSubmit').prop('disabled', false);
                        }
                    });

                } 
            });
        });
        </script>
    </body>
</html>

