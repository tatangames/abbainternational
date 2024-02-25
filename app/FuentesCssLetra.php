<?php

namespace App;


class FuentesCssLetra
{

    public function retornaFuentesCss(){

        $fuentes = "

                @font-face {
                    font-family: 'Fuente1';
                    src: url('file:///android_res/font/notosans_light.ttf') format('truetype'); /* Ruta de la tercera fuente */
                 }

                @font-face {
                    font-family: 'Fuente2';
                    src: url('file:///android_res/font/notosans_condensed_medium.ttf') format('truetype'); /* Ruta de la tercera fuente */
                }

                @font-face {
                    font-family: 'Fuente3';
                    src: url('file:///android_res/font/times_new_normal_regular.ttf') format('truetype'); /* Ruta de la tercera fuente */
                }

                @font-face {
                    font-family: 'Fuente4';
                    src: url('file:///android_res/font/recolecta_medium.ttf') format('truetype'); /* Ruta de la cuarta fuente */
                }

                @font-face {
                    font-family: 'Fuente5';
                    src: url('file:///android_res/font/recolecta_regular.ttf') format('truetype'); /* Ruta de la quinta fuente */
                }

                /* Utilizar las fuentes según sea necesario */
                .texto-fuente1 {
                    font-family: 'Fuente1', sans-serif;
                }

                .texto-fuente2 {
                    font-family: 'Fuente2', sans-serif;
                }

                .texto-fuente3 {
                    font-family: 'Fuente3', sans-serif;
                }

                .texto-fuente4 {
                    font-family: 'Fuente4', sans-serif;
                }

                .texto-fuente5 {
                    font-family: 'Fuente5', sans-serif;
                }

        ";

        return $fuentes;
    }
}
