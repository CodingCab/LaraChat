<?php

namespace App\Modules\TestVideoRecordings\src\Services;

use App\Modules\ElevenLabs\src\Services\ElevenLabs;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ScreenRecordingService
{
    protected mixed $recordingProcess = null;

    protected ?Carbon $recordingStartedAt = null;

    public function getScriptFileName(): string
    {
        return storage_path('/dusk-recordings/') . Str::replace('Tests/Browser/', '', str_replace('\\', '/', get_called_class())) . '_script.txt';
    }

    public function startRecording(?string $fileName = null): self
    {
        if (env('DUSK_RECORDING', false) === false) {
            return $this;
        }

        $languageCode = env('ELEVENLABS_LANGUAGE', 'en');
        ElevenLabs::setSpeakingLanguage($languageCode);

        $fileName = storage_path('/dusk-recordings/') . $languageCode . '/' . ($fileName ?? str_replace('\\', '/', get_called_class())) . '.mov';
        $fileName = Str::replace('Tests/Browser/', '', $fileName);

        ray($fileName);
        $folderPath = dirname($fileName);

        if (!is_dir($folderPath)) {
            mkdir($folderPath, 0755, true);
        }

        $this->recordingStartedAt = now();

//        $this->recordingProcess = proc_open(
//            "ffmpeg -y -f avfoundation -framerate 20 -video_size 1920x1080 -i 1 -vf 'crop=800:1000:50:380' '{$fileName}'",
//            [STDIN, STDOUT, STDERR],
//            $pipes
//        );
//
//        $this->recordingProcess = proc_open(
//            "ffmpeg -y -f avfoundation -pixel_format uyvy422 -framerate 30 -probesize 100M -video_size 1280x720 -i ':1' -filter:v 'crop=500:400:100:100'  '{$fileName}'",
//            [STDIN, STDOUT, STDERR],
//            $pipes
//        );

        $command = './bin/recording.sh start -rect=22:190:501:655 -file="' . $fileName . '"';

        ray($command);
        echo(exec($command));
//        $this->recordingProcess = proc_open(
//            "ffmpeg -y -f avfoundation  -pix_fmt uyvy422 -probesize 100M -analyzeduration 200M -framerate 60 -video_size 1920x1080 -i '2:3' -b:a 192k -vf 'crop=990:1320:50:380' -pix_fmt nv12 '{$fileName}'",
//            [STDIN, STDOUT, STDERR],
//            $pipes
//        );
        return $this;
    }

    public function stopRecording(): void
    {
//        exec('./bin/recording.sh stop');
//        if ($this->recordingProcess) {
//            proc_terminate($this->recordingProcess);
//            $this->recordingProcess = null;
//        }
    }
}
