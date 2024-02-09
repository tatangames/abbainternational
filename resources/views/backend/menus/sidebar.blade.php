<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="#" class="brand-link">
        <img src="{{ asset('images/abba-logo.jpg') }}" alt="Logo" class="brand-image img-circle elevation-3" >
        <span class="brand-text font-weight" style="color: white">Abba</span>
    </a>

    <div class="sidebar">

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="true">


                <!-- ROLES Y PERMISOS -->

                @can('sidebar.roles.y.permisos')
                    <li class="nav-item">

                        <a href="#" class="nav-link nav-">
                            <i class="far fa-edit"></i>
                            <p>
                                Roles y Permisos
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>

                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('admin.roles.index') }}" target="frameprincipal" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Roles</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('admin.permisos.index') }}" target="frameprincipal" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Usuarios</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('admin.idioma.sistema.index') }}" target="frameprincipal" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Idioma Sistema</p>
                                </a>
                            </li>

                        </ul>
                    </li>
                @endcan

                <!-- DASHBOARD -->

                @can('sidebar.dashboard')
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard.index') }}" target="frameprincipal" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Estadísticas</p>
                    </a>
                </li>
                @endcan



                <!-- REGIONES -->
                @can('sidebar.regiones')
                <li class="nav-item">

                    <a href="#" class="nav-link nav-">
                        <i class="far fa-edit"></i>
                        <p>
                            Regiones
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>

                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.region.pais.index') }}" target="frameprincipal" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>País</p>
                            </a>
                        </li>



                    </ul>
                </li>
                @endcan



                <li class="nav-item">

                    <a href="#" class="nav-link nav-">
                        <i class="far fa-edit"></i>
                        <p>
                            Usuarios
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>

                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.usuarios.pais.index') }}" target="frameprincipal" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Usuarios País</p>
                            </a>
                        </li>



                    </ul>
                </li>


                <li class="nav-item">

                    <a href="#" class="nav-link nav-">
                        <i class="far fa-edit"></i>
                        <p>
                            Insignias
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>

                    <ul class="nav nav-treeview">

                        <li class="nav-item">
                            <a href="{{ route('admin.tipoinsignias.index') }}" target="frameprincipal" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Lista Insignias</p>
                            </a>
                        </li>

                    </ul>
                </li>



                <li class="nav-item">

                    <a href="#" class="nav-link nav-">
                        <i class="far fa-edit"></i>
                        <p>
                            Recursos
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>

                    <ul class="nav nav-treeview">

                        <li class="nav-item">
                            <a href="{{ route('admin.devo.inicio.index') }}" target="frameprincipal" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Devocional Día</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('admin.imagenes.dia.index') }}" target="frameprincipal" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Imagenes Día</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('admin.imagenes.preguntas.index') }}" target="frameprincipal" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Imagenes Preguntas</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('admin.videos.hoy.index') }}" target="frameprincipal" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Videos URL</p>
                            </a>
                        </li>


                    </ul>
                </li>

                <li class="nav-item">

                    <a href="#" class="nav-link nav-">
                        <i class="far fa-edit"></i>
                        <p>
                            Devocionales
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>

                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.planes.index') }}" target="frameprincipal" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Nuevo Devocional</p>
                            </a>
                        </li>

                    </ul>
                </li>



                <li class="nav-item">

                    <a href="#" class="nav-link nav-">
                        <i class="far fa-edit"></i>
                        <p>
                            Información
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>

                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.comparte.app.index') }}" target="frameprincipal" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Comparte App</p>
                            </a>
                        </li>


                    </ul>
                </li>

            </ul>
        </nav>

    </div>
</aside>
