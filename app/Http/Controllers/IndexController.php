<?php

namespace App\Http\Controllers;


use App\Games;
use Illuminate\Http\JsonResponse;

class IndexController extends Controller
{
    public function index()
    {
        return Games::all();
    }

    /**
     * save the data log in the database
     *
     * @return JsonResponse
     */
    public function load()
    {
        $game = $this->readFromLogFile();

        array_map(function ($arr) {
            $keys = array_unique(array_keys($arr['clients']));
            $data['name'] = $arr['name'];
            $data['players'] = json_encode($keys);
            $data['kills'] = json_encode($arr['clients']);

            Games::create($data);
        }, $game);

        return new JsonResponse("sucesso", 200);

    }

    /**
     * read the log file and filter the data in arrays to persist them
     *
     * @return array
     */
    private function readFromLogFile()
    {
        $game = [];
        if ($file = fopen(public_path('games.log'), "r")) {
            $count = 0;
            $key = 0;
            while(!feof($file)) {
                $line = fgets($file);

                switch ($line) {

                    case strpos($line, 'InitGame') !== false:
                        ($count === 0) ?: $key++;
                        $game[$key]['name'] = "Game ".($key+1);
                        $game[$key]['totalKills'] = 0;
                        $game[$key]['guns'] = [];
                        $count++;
                        break;

                    case strpos($line, 'ClientUserinfoChanged') !== false:
                        preg_match("/n\\\(.*?)\\\\t/", $line, $match);
                        $game[$key]['clients'][$match[1]] = 0;

                        break;

                    case strpos($line, 'Kill') !== false:
                        preg_match('/[\s\d]:(.*?) killed/', $line, $match, null, 3);
                        preg_match('/<(.*?)>/', $match[1], $matchWorld);
                        if(empty($matchWorld))
                            $game[$key]['clients'][trim($match[1])]++;

                        preg_match('/killed(.*?)by/', $line, $match);
                        $game[$key]['clients'][trim($match[1])] = $game[$key]['clients'][trim($match[1])] - 1;

                        preg_match('/by(.*?)$/', $line, $match);

                        if(array_key_exists(trim($match[1]), $game[$key]['guns']))
                            $game[$key]['guns'][trim($match[1])]++;
                        else
                            $game[$key]['guns'][trim($match[1])] = 1;

                        $game[$key]['totalKills']++;

                        break;
                }
            }
            fclose($file);
        }

        return $game;
    }
}
