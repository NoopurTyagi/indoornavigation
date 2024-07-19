<?php

namespace App\Http\Controllers;

use App\Models\Waypoint;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class WaypointController extends Controller
{
    public function index()
    {
        // return Waypoint::all();
        $waypoints = Waypoint::all();
        return response()->json($waypoints);
    }

    public function show($id)
    {
        return Waypoint::find($id);
    }

    public function store(Request $request)
    {
        $waypoint = Waypoint::create($request->all());
        return response()->json($waypoint, 201);
    }

    public function update(Request $request, $id)
    {
        $waypoint = Waypoint::findOrFail($id);
        $waypoint->update($request->all());
        return response()->json($waypoint, 200);
    }

    public function delete($id)
    {
        Waypoint::findOrFail($id)->delete();
        return response()->json(null, 204);
    }


    public function getPath1(Request $request)
    {
        $startId = $request->input('start_id');
        $endId = $request->input('end_id');

        $waypoints = Waypoint::all()->keyBy('id');

        $path = $this->findPath($waypoints, $startId, $endId);

        return response()->json($path);
    }

    private function findPath($waypoints, $startId, $endId)
    {
        $queue = [[$startId]];
        $visited = collect([$startId]);

        while (count($queue) > 0) {
            $path = array_shift($queue);
            $node = end($path);

            if ($node == $endId) {
                return array_map(function ($id) use ($waypoints) {
                    return $waypoints[$id];
                }, $path);
            }

            foreach ($waypoints[$node]->connected as $neighborId) {
                if (!$visited->contains($neighborId)) {
                    $visited->push($neighborId);
                    $newPath = $path;
                    $newPath[] = $neighborId;
                    $queue[] = $newPath;
                }
            }
        }

        return [];
    }

    // PredictionController.php

    // public function getPath(Request $request)
    // {
    //     try {
    //         $startId = $request->input('start_id');
    //         $endId = $request->input('end_id');

    //         // Call Python script to predict path
    //         $pythonScriptPath = public_path('script.py'); // Adjust the path as necessary
    //         $command = "python3 $pythonScriptPath " . escapeshellarg(json_encode(['start_id' => 1, 'end_id' => 5]));

    //         // Execute the command and capture the output
    //         $output = shell_exec($command);

    //         // Process the output (assuming it's JSON) and return
    //         $predictedResult = json_decode($output, true);
    //         return $predictedResult;
    //         if (isset($predictedResult['path'])) {
    //             return response()->json($predictedResult);
    //         } else {
    //             return response()->json(['error' => 'Path not found'], 404);
    //         }
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => 'Internal server error'], 500);
    //     }
    // }
    public function getPath(Request $request)
    {
        $startId = (int)$request->start_id;
        $endId = (int)$request->end_id;

        // Validate the input
        if (!$startId || !$endId) {
            return response()->json(['path' => null, 'exception' => 'Both start_id and end_id must be provided.'], 400);
        }

        // Prepare data for the Python script
        $data = [
            'start_id' => $startId,
            'end_id' => $endId
        ];

        // Run the Python script
        // $pythonScriptPath = public_path('lll.py');
        $pythonScriptPath = public_path('model_train.py');
        $process = new Process(['python3', $pythonScriptPath, json_encode($data)]);
        $process->run();

        // Check for errors
        if (!$process->isSuccessful()) {
            return response()->json(['path' => null, 'exception' => $process->getErrorOutput()], 500);
        }

        // Decode the output from the Python script
        $result = json_decode($process->getOutput(), true);
        // return $process->getOutput();
        // Return the result
        return response()->json($result);
    }


    public function convert1(Request $request)
    {
        try {
            $sp1  = json_encode($request->candidates[0]["content"]["parts"][0]["text"]);
            $sp2 = str_replace(["\n", "\r", "\t"], '', $sp1);
            // return $sp2;
            $sp3 = explode(",", $sp2);
            $new = '';
            $hh = [];
            foreach ($sp3 as $key => $s) {
                if (!str_contains($s, ':')) {
                    $sp3[$key] = $s . ':""';
                    $hh = [
                        $key => ""
                    ];
                }

                $ss = explode(":", $s);
                foreach ($ss as $key1 => $r) {
                    $hh = [
                        $key1 => $r
                    ];
                }
            }

            return $hh;
            $result_string = implode(",", $sp3);
            $inputString = json_decode(json_encode(str_replace(["\n", "\r", "\t", "\\"], '', json_decode(json_encode($result_string)))));
            // return json_encode($inputString);

            $str = str_replace("\n", "", $inputString);

            return $str;

            return json_decode(json_encode(str_replace(["\n", "\r", "\t", "\\"], '', json_decode(json_encode($result_string)))));
        } catch (Exception $e) {
            dd($e);
        }
    }

    public function convert(Request $request)
    {
        try {
            $sp1  = $request->candidates[0]["content"]["parts"][0]["text"];
            // return json_decode($sp1);
            $sp2 = str_replace(["\n", "\r", "\t"], '', $sp1);
            
            $sp3 = explode(",", $sp2);

            foreach ($sp3 as $key => $s) {
                // $trimmedString = trim(preg_replace('/\s+/', ' ', $s));
                if (!str_contains($s, ':')) {
                    $sp3[$key] = $s . ':"12121212"';
                }
            }
            $result_string = implode(",", $sp3);
            $sk = json_decode(json_encode(str_replace(["\n", "\r", "\t", "\\"], '', json_decode(json_encode($result_string)))));
            // return $sk;
            $sk1 =  str_replace('"{', "", $sk);
            $sk2 =  str_replace('}"', "", $sk1);
            $ss = explode(",", $sk2);
            $hhh = null;
            foreach ($ss as $s) {
                
                $trimmedString = trim(preg_replace('/\s+/', ' ', $s));
                $parts = explode(':', $trimmedString, 2);
                $key = trim($parts[0], '"');
                $value = trim($parts[1], '" ');
                if(str_contains($key,'{"')){
                    $key = str_replace('{"',"",$key);
                } 
                if(str_contains($value,'"}')){
                    $value = str_replace('"}',"",$value);
                }
               
                $hhh[$key] = $value;                
            }

            $hh = [
                "asdfas"=>9999,
                "sadfsad" => 454
            ];
            echo "<pre>";
            print_r($hhh);
            echo "<pre>";die;
            return $hhh;
        } catch (Exception $e) {
            dd($e);
        }
    }
}
