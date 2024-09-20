import sys
import joblib  

# Load the model (you may need to adjust the path based on your project structure)
model = joblib.load('appointmentPriceModel.pkl')

# Get user input from command line arguments

age, county_id, doctor_specialty_id = map(float, sys.argv[1:])


# Predict appointment price
predicted_price = model.predict([[age, county_id, doctor_specialty_id ]])[0]

formatted_price = '{:.2f}'.format(predicted_price)
print(f'{formatted_price}')
