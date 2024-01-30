<!DOCTYPE html>
<html lang="es">

<head>
    <title>Astro Pollo - Panel</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"
          integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">

    <link href="{{ asset('images/icono-sistema.png') }}" rel="icon">
    <!--Fontawesome CDN.-->
    <script defer src="https://use.fontawesome.com/releases/v5.0.6/js/all.js"></script>
    <!-- comprimido de librerias -->
    <!-- libreria para alertas -->
    <link rel="stylesheet" href="{{asset('css/login/styleLogin.css')}}">

    <link href="{{ asset('css/toastr.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/sweetalert2.min.css') }}" rel="stylesheet">

</head>

<style>

    @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap');
    html, body {
        height: 100%;
    }
    body {
        font-family: 'Roboto', sans-serif;
        background-image: url({{ asset('images/fondo-hojas.jpg') }});
    }
    .demo-container {
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .btn-lg {
        padding: 12px 26px;
        font-size: 14px;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
    }
    ::placeholder {
        font-size:14px;
        letter-spacing:0.5px;
    }
    .form-control-lg {
        font-size: 16px;
        padding: 25px 20px;
    }
    .font-500{
        font-weight:500;
    }
    .image-size-small{
        width:140px;
        margin:0 auto;
    }
    .image-size-small img{
        width:140px;
        margin-bottom:-70px;
    }
    .icon-camera{
        position: absolute;
        right: -1px;
        top: 21px;
        width: 30px;
        height: 30px;
        background-color: #FFF;
        border-radius: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

</style>

<body>


<div class="demo-container">
    <div class="container">
        <div class="row" style="margin-top: 60px">
            <div class="col-lg-6 col-12 mx-auto">
                <div class="text-center image-size-small position-relative">
                    <img src="{{ asset('images/abba-logo.jpg') }}" class="rounded-circle p-2 bg-white">
                </div>
                <div class="p-5 bg-white rounded shadow-lg">
                    <h3 class="mb-2 text-center pt-5">Mi Caminar con Dios</h3>
                    <p class="text-center lead">El Link ha Expirado</p>
                    <form class=" validate-form">

                        <div class="form-group text-center">
                            <input type="button" value="INICIAR SESIÓN" onclick="regresar()" id="btnLogin" class="btn btn-lg w-100 shadow-lg" style="background: #010066; color: white">
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>



<script src="{{ asset('js/jquery.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('js/toastr.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('js/axios.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('js/sweetalert2.all.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('js/alertaPersonalizada.js') }}" type="text/javascript"></script>

</body>

</html>
<script>

    function regresar(){


        window.location.href="{{ url('/') }}";


    }

</script>
