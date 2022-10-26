<?php

namespace Core\Helpers;

use Core\Helpers\Redis;

class Queue {

    private $name;
    private $cluster = "";

    private $try_delay = 0;
    
    public function __construct($name, $cluster="")
    {
        $this->name = $name;
        $this->cluster = $cluster;
    }

    public function add(array $job) {
        $job["__tentativas"] = 0;
        $job["__last_error"] = "";
        $job["__next_try"] = time();
        $job["__try_delay"] = $this->try_delay;
        $job = json_encode($job);
        return Redis::lPush($this->name . $this->cluster . ":queue", $job);
    }

    public function try_delay($seconds) {
        $this->try_delay = $seconds;
    }

    public function worker($queue, $cluster="") {
        $worker = $queue;
        $queue = $queue . $cluster;
        while(true) {

            // Capturar um job que esteja pronto pra uso
            while(true) {
                $job = Redis::lPop($queue . ":queue");
                if($job === null) {
                    sleep(1);
                    continue;
                }
                $job = json_decode($job, true);
                if(time() < $job["__next_try"]) {
                    Redis::lPush($queue . ":queue", json_encode($job));
                    continue;
                }
                break;
            }
            //

            $job["__tentativas"]++;
            try {
                call_user_func(["\App\Workers\\" . $worker, "worker"], $job);
            } catch(\Throwable $e) {
                $job["__last_error"] = $e->getMessage() . ". File: " . $e->getFile() . "Line: " . $e->getLine();
                if($job["__tentativas"] == 3) {
                    Redis::lPush($queue . ":queue:failed_jobs", json_encode($job));
                } else {
                    $job["__next_try"] = time() + $job["__try_delay"];
                    Redis::lPush($queue . ":queue", json_encode($job));
                }
            }
            gc_collect_cycles();
        }
    }

}