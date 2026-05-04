import mysql.connector
from datetime import datetime

conn = mysql.connector.connect(
    user='root',
    password='',
    host='localhost',
    database='house_rental_system'
)

cur = conn.cursor()
now = datetime.now().strftime('%Y-%m-%d %H:%M:%S')

sql = f"INSERT INTO rentals (user_id, house_id, status, stay_decision, lease_status, created_at, updated_at) VALUES (5, 1, 'active', 'yes', 'not_requested', '{now}', '{now}')"

cur.execute(sql)
conn.commit()

print("Rental created successfully!")
cur.close()
conn.close()
