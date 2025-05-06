import pandas as pd
import random
from faker import Faker

fake = Faker()

def generate_tags():
    tags = ["Ashland Wall", "nl.com", "Tucker Carlson", "Hosting Client", "VIP", "follow-up", "Driving Client", "Kauai", "Reddit free Consul", "opt-out"]
    return "|".join(random.sample(tags, random.randint(1, 3)))

# Define the number of demo rows requested
num_demo_rows = 20000

# Generate the demo data
demo_data = {
    "user_id": ["" for _ in range(num_demo_rows)],
    "first_name": [fake.first_name() for _ in range(num_demo_rows)],
    "last_name": [fake.last_name() for _ in range(num_demo_rows)],
    "email": [fake.email() for _ in range(num_demo_rows)],
    "phone": [fake.phone_number() if random.random() > 0.3 else "" for _ in range(num_demo_rows)],
    "mobile_phone": [fake.phone_number() if random.random() > 0.3 else "" for _ in range(num_demo_rows)],
    "address": [fake.address() if random.random() > 0.5 else "" for _ in range(num_demo_rows)],
    "address2": [fake.secondary_address() if random.random() > 0.5 else "" for _ in range(num_demo_rows)],
    "city": [fake.city() if random.random() > 0.4 else "" for _ in range(num_demo_rows)],
    "state": [fake.state_abbr() if random.random() > 0.4 else "" for _ in range(num_demo_rows)],
    "zip_code": [fake.zipcode() if random.random() > 0.4 else "" for _ in range(num_demo_rows)],
    "country": [fake.country() if random.random() > 0.4 else "" for _ in range(num_demo_rows)],
    "company": [fake.company() if random.random() > 0.3 else "" for _ in range(num_demo_rows)],
    "job_title": [fake.job() if random.random() > 0.3 else "" for _ in range(num_demo_rows)],
    "date_of_birth": [fake.date_of_birth().isoformat() if random.random() > 0.6 else "" for _ in range(num_demo_rows)],
    "website": [fake.url() if random.random() > 0.4 else "" for _ in range(num_demo_rows)],
    "linkedin_profile": [fake.url() if random.random() > 0.5 else "" for _ in range(num_demo_rows)],
    "twitter_handle": ["@" + fake.user_name() if random.random() > 0.5 else "" for _ in range(num_demo_rows)],
    "lead_source": [random.choice(["Referral", "Website", "Cold Call", "Email Marketing", "Social Media", "Other"]) if random.random() > 0.4 else "" for _ in range(num_demo_rows)],
    "lead_status": [random.choice(["New", "Contacted", "Qualified", "Lost", "Won"]) if random.random() > 0.5 else "" for _ in range(num_demo_rows)],
    "notes": [fake.sentence(nb_words=10) if random.random() > 0.6 else "" for _ in range(num_demo_rows)],
    "tags": [generate_tags() if random.random() > 0.4 else "" for _ in range(num_demo_rows)]
}

# Create a DataFrame and save it as a CSV file
demo_df = pd.DataFrame(demo_data)
demo_df.to_csv("LovingCRM_1_Dummy_Contacts.csv", index=False)
