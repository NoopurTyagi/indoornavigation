import psycopg2
from psycopg2.extras import RealDictCursor
from sklearn.tree import DecisionTreeClassifier
from sklearn.preprocessing import StandardScaler
import numpy as np
import joblib
import json
import sys

def fetch_waypoints_from_db():
    try:
        connection = psycopg2.connect(
            host='127.0.0.1',
            database='qgis',
            user='postgres',
            password='Welcome@123'
        )

        cursor = connection.cursor(cursor_factory=RealDictCursor)
        cursor.execute('SELECT * FROM waypoints')
        waypoints = cursor.fetchall()
        connection.close()

        waypoints_dict = {waypoint['id']: waypoint for waypoint in waypoints}
        return waypoints_dict

    except psycopg2.Error as e:
        raise ValueError(f"Error connecting to PostgreSQL: {e}")

def extract_features_labels(waypoints, end_id):
    features = []
    labels = []

    for waypoint in waypoints.values():
        x = waypoint['x']
        y = waypoint['y']
        num_connections = len(waypoint['connected'])
        features.append([x, y, num_connections])
        labels.append(1 if waypoint['id'] == end_id else 0)

    return np.array(features), np.array(labels)

def train_model(features, labels):
    scaler = StandardScaler()
    features_scaled = scaler.fit_transform(features)
    clf = DecisionTreeClassifier()
    clf.fit(features_scaled, labels)
    return clf, scaler

def predict_path(start_id, end_id, model, waypoints, scaler):
    queue = [[start_id]]
    visited = set([start_id])

    while queue:
        path = queue.pop(0)
        node = path[-1]

        if node == end_id:
            return [waypoints[n] for n in path]

        for neighbor_id in waypoints[node]['connected']:
            if neighbor_id not in visited:
                visited.add(neighbor_id)
                new_path = path + [neighbor_id]
                queue.append(new_path)

    raise ValueError(f"Path not found between start_id={start_id} and end_id={end_id}.")

def main():
    try:
        if len(sys.argv) > 1:
            input_data = json.loads(sys.argv[1])
        else:
            raise ValueError("No input data provided.")

        start_id = int(input_data.get('start_id'))
        end_id = int(input_data.get('end_id'))

        if not start_id or not end_id:
            raise ValueError("Both start_id and end_id must be provided.")

        waypoints = fetch_waypoints_from_db()

        features, labels = extract_features_labels(waypoints, end_id)
        model, scaler = train_model(features, labels)

        joblib.dump(model, 'path_prediction_model.pkl')
        joblib.dump(scaler, 'path_prediction_scaler.pkl')

        # print("Model trained and saved successfully.")

        path = predict_path(start_id, end_id, model, waypoints, scaler)
        # formatted_path = [{k: v for k, v in waypoint.items() if k != 'id'} for waypoint in path]
        print(json.dumps(path, default=str, indent=2))

    except Exception as e:
        print(json.dumps({'path': None, 'exception': str(e)}))

if __name__ == "__main__":
    main()
