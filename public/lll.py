import psycopg2
from psycopg2.extras import RealDictCursor
from sklearn.tree import DecisionTreeClassifier
from sklearn.preprocessing import StandardScaler
import numpy as np
import joblib
import json

# Function to fetch waypoints from PostgreSQL
def fetch_waypoints_from_db():
    try:
        # Replace placeholders with your PostgreSQL connection details
        connection = psycopg2.connect(
            host='127.0.0.1',         # Replace with your PostgreSQL host
            database='qgis',          # Replace with your PostgreSQL database name
            user='postgres',          # Replace with your PostgreSQL username
            password='Welcome@123'     # Replace with your PostgreSQL password
        )

        cursor = connection.cursor(cursor_factory=RealDictCursor)
        cursor.execute('SELECT * FROM waypoints')

        waypoints = cursor.fetchall()

        connection.close()

        return waypoints

    except psycopg2.Error as e:
        raise ValueError(f"Error connecting to PostgreSQL: {e}")

# Function to extract features and labels
def extract_features_labels(waypoints, start_id, end_id):
    features = []
    labels = []

    for waypoint in waypoints:
        x = waypoint['x']
        y = waypoint['y']
        num_connections = len(waypoint['connected'])

        features.append([x, y, num_connections])

        if waypoint['id'] == end_id:
            labels.append(1)
        else:
            labels.append(0)

    return np.array(features), np.array(labels)

# Function to train the Decision Tree classifier
def train_model(features, labels):
    scaler = StandardScaler()
    features_scaled = scaler.fit_transform(features)

    clf = DecisionTreeClassifier()
    clf.fit(features_scaled, labels)

    return clf, scaler

# Function to predict path using BFS and machine learning model
# Function to predict path using BFS and machine learning model
def predict_path(start_id, end_id, model, waypoints, scaler):
    # Perform graph traversal to find path between start_id and end_id
    queue = [[start_id]]  # Initialize queue with the starting node
    visited = set([start_id])  # Track visited nodes to avoid cycles

    while queue:
        path = queue.pop(0)  # Get the first path from the queue
        node = path[-1]  # Get the last node from the path

        if node == end_id:
            # If we reach the end node, return the path of waypoints
            return [waypoint for waypoint in waypoints if waypoint['id'] in path]

        # Explore neighbors (connected waypoints) of the current node
        for neighbor_id in waypoints[node]['connected']:
            if neighbor_id not in visited:
                visited.add(neighbor_id)  # Mark neighbor as visited
                new_path = path + [neighbor_id]  # Extend the path to neighbor
                queue.append(new_path)  # Add new path to the queue for exploration

    # If no path found, raise an error
    raise ValueError(f"Path not found between start_id={start_id} and end_id={end_id}.")

# Main function to orchestrate the training and path prediction
def main():
    try:
        # Fetch waypoints from the database
        waypoints = fetch_waypoints_from_db()

        # Define start and end IDs (adjust as needed)
        start_id = 1
        end_id = 24

        # Extract features and labels from waypoints
        features, labels = extract_features_labels(waypoints, start_id, end_id)

        # Train the model
        model, scaler = train_model(features, labels)

        # Save the trained model and scaler
        joblib.dump(model, 'path_prediction_model.pkl')
        joblib.dump(scaler, 'path_prediction_scaler.pkl')

        print("Model trained and saved successfully.")

        # Predict path using the trained model
        path = predict_path(start_id, end_id, model, waypoints, scaler)
       
        # Format path to match the expected JSON response (excluding 'id' field)
        formatted_path = [{k: v for k, v in waypoint.items() if k != 'id'} for waypoint in path]

        # Print the formatted path as JSON response
        print(json.dumps(formatted_path, default=str, indent=2))

    except ValueError as e:
        print(f"Error: {e}")

if __name__ == "__main__":
    main()
