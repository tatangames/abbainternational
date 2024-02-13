<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use OneSignal;

class EnviarNotificacion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    protected $arrayOnesignal;
    protected $titulo;
    protected $descripcion;

    /**
     * Create a new job instance.
     */
    public function __construct($arrayOnesignal, $titulo, $descripcion)
    {
        $this->arrayOnesignal = $arrayOnesignal;
        $this->titulo = $titulo;
        $this->descripcion = $descripcion;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $tituloNoti = $this->titulo;
        $mensajeNoti = $this->descripcion;

        $AppId = config('googleapi.IdApp_Cliente');

        $contents = array(
            "en" => $mensajeNoti
        );

        $params = array(
            'app_id' => $AppId,
            'contents' => $contents,
            'include_player_ids' => is_array($this->arrayOnesignal) ? $this->arrayOnesignal : array($this->arrayOnesignal)
        );

        $params['headings'] = array(
            "en" => $tituloNoti
        );

        OneSignal::sendNotificationCustom($params);
    }
}
