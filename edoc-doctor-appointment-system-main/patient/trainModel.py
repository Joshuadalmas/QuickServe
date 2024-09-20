import pandas as pd
from sklearn.model_selection import train_test_split
from sklearn.linear_model import LinearRegression
from sklearn.metrics import mean_squared_error
from sklearn.preprocessing import StandardScaler
from joblib import dump

# 1. Load Data
data = pd.read_csv('appointmentPriceModel.csv')

# 2. Preprocess Data
# No preprocessing needed for this example

# 3. Split Data
X = data.drop('appointment_price', axis=1)
y = data['appointment_price']
X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

# 4. Train Model
model = LinearRegression()
model.fit(X_train, y_train)

# 5. Evaluate Model
y_pred = model.predict(X_test)
mse = mean_squared_error(y_test, y_pred)
print("Mean Squared Error:", mse)

# 6. Save Model
dump(model, 'appointmentPriceModel.pkl')