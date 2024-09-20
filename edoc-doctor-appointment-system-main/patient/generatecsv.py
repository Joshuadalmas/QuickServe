import csv
import random

def generate_data(num_samples=500000):
    data = []
    for _ in range(num_samples):
        # Generate random age between 18 and 100
        age = random.randint(18, 100)
        
        # Generate random county ID (1 to 47)
        county_id = random.randint(1, 47)
        
        # Generate random doctor specialty ID (1 to 56)
        doctor_specialty_id = random.randint(1, 56)

        # Calculate appointment price based on age, county ID, and random Gaussian noise
        appointment_price = round(50 + (age * 1.5) + county_id * 50 + random.gauss(0, 10), 2)
        
        # Append generated data to the list
        data.append([age, county_id, doctor_specialty_id, appointment_price])

    return data

# Define header for the CSV file
header = ["age", "county_id", "doctor_specialty_id", "appointment_price"]

# Generate appointment data
data = generate_data()

# Write data to CSV file
with open('appointmentPriceModel.csv', 'w', newline='') as csvfile:
    csv_writer = csv.writer(csvfile)
    csv_writer.writerow(header)
    csv_writer.writerows(data)
