<?php

namespace Database\Seeders;

use App\Models\Waypoint;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WaypointsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $waypoints = [
            ['id' => 1, 'name' => 'Entrance', 'x' => -8, 'y' => 0, 'z' => -3, 'connected' => [2]],
            ['id' => 2, 'name' => 'Corridor Intersection', 'x' => -6, 'y' => 0, 'z' => -3, 'connected' => [1, 3, 4, 5, 6, 7, 8, 9]],
            ['id' => 3, 'name' => 'Faculty Room 03', 'x' => -6, 'y' => 0, 'z' => -10, 'connected' => [2]],
            ['id' => 4, 'name' => 'Faculty Room 004', 'x' => -2, 'y' => 0, 'z' => 2, 'connected' => [2]],
            ['id' => 5, 'name' => 'Faculty Room 005', 'x' => -6, 'y' => 0, 'z' => 2, 'connected' => [2]],
            ['id' => 6, 'name' => 'Restroom 1', 'x' => -4, 'y' => 0, 'z' => -10, 'connected' => [2]],
            ['id' => 7, 'name' => 'Restroom 2', 'x' => -2, 'y' => 0, 'z' => -10, 'connected' => [2]],
            ['id' => 8, 'name' => 'Pantry', 'x' => -1, 'y' => 0, 'z' => -7, 'connected' => [2]],
            ['id' => 9, 'name' => 'Ground Floor Stairs', 'x' => -1, 'y' => 2, 'z' => -5, 'connected' => [2, 10]],
            ['id' => 10, 'name' => 'Ground Floor Stairs End', 'x' => 0, 'y' => 4, 'z' => -6, 'connected' => [9, 11, 18]],
            ['id' => 11, 'name' => 'First Floor Stairs', 'x' => 0, 'y' => 4, 'z' => -4, 'connected' => [10, 12, 18]],
            ['id' => 12, 'name' => 'Corridor', 'x' => -5, 'y' => 3.5, 'z' => -4, 'connected' => [11, 13, 16, 17]],
            ['id' => 13, 'name' => 'First Floor Corridor Intersection', 'x' => -6, 'y' => 3.5, 'z' => 3, 'connected' => [12, 14]],
            ['id' => 14, 'name' => 'LH1', 'x' => -6, 'y' => 3.5, 'z' => 5, 'connected' => [13, 15]],
            ['id' => 15, 'name' => 'LH2', 'x' => -6, 'y' => 3.5, 'z' => 8, 'connected' => [14]],
            ['id' => 16, 'name' => 'LH3', 'x' => -6, 'y' => 3.5, 'z' => -10, 'connected' => [12, 17]],
            ['id' => 17, 'name' => 'LH4', 'x' => -6, 'y' => 3.5, 'z' => -12, 'connected' => [12, 16]],
            ['id' => 18, 'name' => 'Second Floor Stairs', 'x' => 0, 'y' => 5, 'z' => -4, 'connected' => [10, 11, 19]],
            ['id' => 19, 'name' => 'Second Floor Stairs End', 'x' => 0, 'y' => 7, 'z' => -4, 'connected' => [18, 20]],
            ['id' => 20, 'name' => 'Third Floor Corridor', 'x' => -5, 'y' => 7, 'z' => -3, 'connected' => [19, 21, 24]],
            ['id' => 21, 'name' => 'Third Floor Corridor Intersection', 'x' => -5, 'y' => 7, 'z' => -2, 'connected' => [20, 22]],
            ['id' => 22, 'name' => 'LH5', 'x' => -5, 'y' => 7, 'z' => 5, 'connected' => [21, 23]],
            ['id' => 23, 'name' => 'LH6', 'x' => -5, 'y' => 7, 'z' => 8, 'connected' => [22]],
            ['id' => 24, 'name' => 'Restroom3', 'x' => -5, 'y' => 7, 'z' => -5, 'connected' => [20]],   
        ];
        

        foreach ($waypoints as $waypoint) {
            Waypoint::create($waypoint);
        }
    }
}
