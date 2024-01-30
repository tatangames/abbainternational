<!DOCTYPE html>
<html lang="es">

<head>
    <title>Abba - Panel</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"
          integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">

    <link href="{{ asset('images/icono-sistema.png') }}" rel="icon">
    <!--Fontawesome CDN.-->
    <script defer src="https://use.fontawesome.com/releases/v5.0.6/js/all.js"></script>

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
                    <p class="text-center lead">Panel de Administración</p>
                    <form class=" validate-form">

                        <div class="input-group form-group" style="margin-top: 25px">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                            </div>
                            <input id="correo" maxlength="100" type="text" class="form-control" required placeholder="Correo electrónico" autocomplete="off">
                        </div>

                        <div class="input-group form-group" style="margin-top: 25px">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-key"></i></span>
                            </div>
                            <input id="password" maxlength="25" type="password" class="form-control" required placeholder="Contraseña" autocomplete="off">
                        </div>

                        <div class="form-group text-left">
                            <a id="myLink" style="color: black; font-size: 15px" href="#" onclick="modalRecuperar()"> Contraseña olvidada?
                            </a>
                        </div>


                        <br>
                        <div class="form-group text-center">
                            <input type="button" value="ACCEDER" onclick="login()" id="btnLogin" class="btn btn-lg w-100 shadow-lg" style="background: #010066; color: white">
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

    var inputPassword = document.getElementById("password");
    var inputUsuario = document.getElementById("usuario");

    inputPassword.addEventListener("keyup", function(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            login();
        }
    });

    inputUsuario.addEventListener("keyup", function(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            login();
        }
    });

    function login() {

        var correo = document.getElementById('correo').value;
        var password = document.getElementById('password').value;

        if(correo === ''){
            toastr.error("Correo es requerido");
            return;
        }

        if(password === ''){
            toastr.error("Contraseña es requerido");
            return;
        }

        if(correo.length > 100){
            toastr.error("Máximo 100 caracteres para Correo");
            return;
        }

        if(password.length > 25){
            toastr.error("Máximo 25 caracteres para Contraseña");
            return;
        }

        openLoading()

        let formData = new FormData();
        formData.append('email', correo);
        formData.append('password', password);

        axios.post('admin/login', formData, {
        })
            .then((response) => {


                closeLoading()
                verificar(response);
            })
            .catch((error) => {
                closeLoading()

                console.log(error)
                toastr.error("Error en la respuesta");
            });
    }

    function verificar(response) {

        if (response.data.success === 0) {
            toastr.error("Validación incorrecta");
        } else if (response.data.success === 1) {
            window.location = response.data.ruta;
        } else if (response.data.success === 2) {
            toastr.error("Contraseña incorrecta");
        }else if (response.data.success === 3) {
            toastr.error("Correo no encontrado");
        }
        else {
            toastr.error('Error');
        }
    }


    // modal para recuperar contrasena
    function modalRecuperar(){
        window.location.href="{{ url('/admin/ingreso/de/correo') }}";
    }


</script>
