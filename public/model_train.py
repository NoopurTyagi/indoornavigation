import sys
import json
import psycopg2
from psycopg2.extras import RealDictCursor
from datetime import datetime
from sklearn.linear_model import LinearRegression
import traceback

# Function to fetch waypoints from PostgreSQL
def fetch_waypoints():
    try:
        connection = psycopg2.connect(
            host='127.0.0.1',         # Replace with your PostgreSQL host
            database='qgis',          # Replace with your PostgreSQL database name
            user='postgres',          # Replace with your PostgreSQL username
            password='Welcome@123'    # Replace with your PostgreSQL password
        )

        cursor = connection.cursor(cursor_factory=RealDictCursor)
        cursor.execute('SELECT * FROM waypoints')

        waypoints = {waypoint['id']: waypoint for waypoint in cursor.fetchall()}

        # Convert datetime objects to string format and coordinates to numeric values
        for waypoint in waypoints.values():
            for key, value in waypoint.items():
                if isinstance(value, datetime):
                    waypoint[key] = value.strftime('%Y-%m-%dT%H:%M:%S.%fZ')
                elif key in ['x', 'y']:
                    waypoint[key] = float(value)  # Convert to float if necessary

        connection.close()

        return waypoints

    except psycopg2.Error as e:
        raise ValueError(f"Error connecting to PostgreSQL: {e}")

# Function to predict path using machine learning model
def predict_path_ml(waypoints, start_id, end_id, model):
    # Perform graph traversal to find path between start_id and end_id
    queue = [[start_id]]
    visited = set([start_id])

    while queue:
        path = queue.pop(0)
        node = path[-1]

        if node == end_id:
            # Found the path, return list of waypoints
            return [waypoints[id] for id in path]

        for neighbor_id in waypoints[node]['connected']:
            if neighbor_id not in visited:
                visited.add(neighbor_id)
                new_path = path + [neighbor_id]
                queue.append(new_path)

    raise ValueError(f"Path not found between start_id={start_id} and end_id={end_id}.")

# Main function to execute when script is run
if __name__ == "__main__":
    try:
        # Validate command-line arguments
        if len(sys.argv) < 2:
            raise ValueError("JSON input argument missing.")

        input_data = json.loads(sys.argv[1])
        start_id = input_data.get('start_id')
        end_id = input_data.get('end_id')

        if not isinstance(start_id, int) or not isinstance(end_id, int):
            raise ValueError("Both start_id and end_id must be integers.")

        waypoints = fetch_waypoints()

        if start_id not in waypoints or end_id not in waypoints:
            raise ValueError("Invalid start_id or end_id provided.")

        # Example of using a simple linear regression model
        model = LinearRegression()

        # Predict path using machine learning model
        predicted_paths = predict_path_ml(waypoints, start_id, end_id, model)

        # Print the predicted result JSON object
        print(json.dumps(predicted_paths))

    except ValueError as ve:
        print(json.dumps({
            'path': None,
            'exception': str(ve)
        }))

    except Exception as e:
        # Print detailed exception traceback for debugging
        traceback.print_exc()

        print(json.dumps({
            'path': None,
            'exception': str(e)
        }))