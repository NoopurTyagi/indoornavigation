import sys
import json

# Example function to fetch waypoints from a data source (replace with your actual implementation)
def fetch_waypoints():
    waypoints = {
        1: {'id': 1, 'name': 'Entrance', 'x': -8, 'y': 0, 'z': -3, 'connected': [2]},
        2: {'id': 2, 'name': 'Corridor Intersection', 'x': -6, 'y': 0, 'z': -3, 'connected': [1, 3, 4, 5, 6, 7, 8, 9]},
        3: {'id': 3, 'name': 'Faculty Room 03', 'x': -6, 'y': 0, 'z': -10, 'connected': [2]},
        4: {'id': 4, 'name': 'Faculty Room 004', 'x': -2, 'y': 0, 'z': 2, 'connected': [2]},
        5: {'id': 5, 'name': 'Faculty Room 005', 'x': -6, 'y': 0, 'z': 2, 'connected': [2]},
        6: {'id': 6, 'name': 'Restroom 1', 'x': -4, 'y': 0, 'z': -10, 'connected': [2]},
        7: {'id': 7, 'name': 'Restroom 2', 'x': -2, 'y': 0, 'z': -10, 'connected': [2]},
        8: {'id': 8, 'name': 'Pantry', 'x': -1, 'y': 0, 'z': -7, 'connected': [2]},
        9: {'id': 9, 'name': 'Ground Floor Stairs', 'x': -1, 'y': 2, 'z': -5, 'connected': [2, 10]},
        10: {'id': 10, 'name': 'Ground Floor Stairs End', 'x': 0, 'y': 4, 'z': -6, 'connected': [9, 11, 18]},
        11: {'id': 11, 'name': 'First Floor Stairs', 'x': 0, 'y': 4, 'z': -4, 'connected': [10, 12, 18]},
        12: {'id': 12, 'name': 'Corridor', 'x': -5, 'y': 3.5, 'z': -4, 'connected': [11, 13, 16, 17]},
        13: {'id': 13, 'name': 'First Floor Corridor Intersection', 'x': -6, 'y': 3.5, 'z': 3, 'connected': [12, 14]},
        14: {'id': 14, 'name': 'LH1', 'x': -6, 'y': 3.5, 'z': 5, 'connected': [13, 15]},
        15: {'id': 15, 'name': 'LH2', 'x': -6, 'y': 3.5, 'z': 8, 'connected': [14]},
        16: {'id': 16, 'name': 'LH3', 'x': -6, 'y': 3.5, 'z': -10, 'connected': [12, 17]},
        17: {'id': 17, 'name': 'LH4', 'x': -6, 'y': 3.5, 'z': -12, 'connected': [12, 16]},
        18: {'id': 18, 'name': 'Second Floor Stairs', 'x': 0, 'y': 5, 'z': -4, 'connected': [10, 11, 19]},
        19: {'id': 19, 'name': 'Second Floor Stairs End', 'x': 0, 'y': 7, 'z': -4, 'connected': [18, 20]},
        20: {'id': 20, 'name': 'Third Floor Corridor', 'x': -5, 'y': 7, 'z': -3, 'connected': [19, 21, 24]},
        21: {'id': 21, 'name': 'Third Floor Corridor Intersection', 'x': -5, 'y': 7, 'z': -2, 'connected': [20, 22]},
        22: {'id': 22, 'name': 'LH5', 'x': -5, 'y': 7, 'z': 5, 'connected': [21, 23]},
        23: {'id': 23, 'name': 'LH6', 'x': -5, 'y': 7, 'z': 8, 'connected': [22]},
        24: {'id': 24, 'name': 'Restroom3', 'x': -5, 'y': 7, 'z': -5, 'connected': [20]}
    }
    return waypoints

# Function to find the path using BFS algorithm
def find_path(waypoints, start_id, end_id):
    queue = [[start_id]]
    visited = set([start_id])

    while queue:
        path = queue.pop(0)
        node = path[-1]

        if node == end_id:
            # Return the full path details from start to end
            return [waypoints[node] for node in path]

        for neighbor_id in waypoints[node]['connected']:
            if neighbor_id not in visited:
                visited.add(neighbor_id)
                new_path = path + [neighbor_id]
                queue.append(new_path)

    return []

# Main function to execute when script is run
if __name__ == "__main__":
    try:
        # Reading input data from command line arguments (expected to be JSON encoded)
        input_data = json.loads(sys.argv[1])
        start_id = input_data.get('start_id')
        end_id = input_data.get('end_id')

        if not start_id or not end_id:
            raise ValueError("Both start_id and end_id must be provided.")

        # Fetch waypoints
        waypoints = fetch_waypoints()

        if start_id not in waypoints or end_id not in waypoints:
            raise ValueError("Invalid start_id or end_id provided.")

        # Find the path between start_id and end_id
        path = find_path(waypoints, start_id, end_id)

        if not path:
            raise ValueError("Path not found between the specified waypoints.")

        # Constructing response
        predicted_result = {
            'path': path,
            'exception': None
        }

        # Printing the predicted result JSON object (to be captured by Laravel controller)
        print(json.dumps(predicted_result))

    except Exception as e:
        # Print any exceptions raised during execution
        print(json.dumps({
            'path': None,
            'exception': str(e)
        }))
