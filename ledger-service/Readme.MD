# Multi-Currency Ledger Service

## 🚀 Overview
This is a Symfony-based API for managing multi-currency financial ledgers. It supports ledger creation, transactions (debit/credit), and balance retrieval.

## 📦 Setup & Installation

### **1️⃣ Prerequisites**
Ensure you have the following installed:
- Docker & Docker Compose
- Make (optional, for convenience)

### **2️⃣ Clone the Repository**
```sh
git clone https://github.com/Saidalo/multi-currency-ledger.git
cd multi-currency-ledger
```

### **3️⃣ Start the Application**
```sh
docker-compose up -d --build
```

### **4️⃣ Run Database Migrations**
```sh
docker-compose exec php bin/console doctrine:migrations:migrate
```

### **5️⃣ Run Tests**
```sh
docker-compose exec php bin/phpunit tests/
```

## 📖 API Endpoints

### **1️⃣ Create a Ledger**
```http
POST /ledgers
```
#### **Request Body:**
```json
{
  "currency": "USD"
}
```
#### **Response:**
```json
{
  "ledgerId": "uuid",
  "currency": "USD",
  "createdAt": "2025-02-17T00:00:00Z"
}
```

### **2️⃣ Create a Transaction**
```http
POST /transactions
```
#### **Request Body:**
```json
{
  "ledgerId": "uuid",
  "transactionType": "credit",
  "amount": 100,
  "currency": "USD"
}
```
#### **Response:**
```json
{
  "transactionId": "uuid",
  "ledgerId": "uuid",
  "amount": 100,
  "currency": "USD",
  "transactionType": "credit",
  "createdAt": "2025-02-17T00:00:00Z"
}
```

### **3️⃣ Get Ledger Balance**
```http
GET /balances/{ledgerId}
```
#### **Response:**
```json
{
  "USD": 100,
  "EUR": 50
}
```

## 🏛️ Architecture Overview
- **Symfony 7.2** (Framework)
- **PostgreSQL** (Database)
- **Docker & Docker Compose** (Containerized setup)
- **Nginx** (Reverse Proxy)
- **PHP-FPM** (Application runtime)
- **Doctrine ORM** (Database abstraction)


## 🚀 Performance Testing with Locust

1️⃣ Install Locust

Ensure you have Python and pip installed, then run:

pip install locust

2️⃣ Locust Test File

A performance test file named locustfile.py already exists in the project directory with the following content:

from locust import HttpUser, task, between
import uuid

class LedgerUser(HttpUser):
    wait_time = between(1, 2)

    def on_start(self):
        response = self.client.post("/ledgers", json={"currency": "USD"})
        if response.status_code == 201:
            self.ledger_id = response.json()["ledgerId"]

    @task
    def create_transaction(self):
        self.client.post("/transactions", json={
            "ledgerId": self.ledger_id,
            "transactionType": "credit",
            "amount": 100,
            "currency": "USD"
        })

3️⃣ Run Locust

Start the performance test by running:

locust -f locustfile.py --host=http://localhost:8081

Then, open a browser and go to http://localhost:8089 to access the Locust web UI.

4️⃣ Configure and Start the Test

Set the Number of Users (e.g., 100)

Set the Spawn Rate (e.g., 10 users per second)

Click Start Swarming

5️⃣ Monitor Results

Locust will display real-time statistics on request count, failures, response times, and more.

6️⃣ Simulating 1,000 Transactions per Minute

To demonstrate the application's ability to handle 1,000 transactions per minute, use the following configuration:

locust -f locustfile.py --host=http://localhost:8081 --users 200 --spawn-rate 20

--users 200: Number of concurrent users

--spawn-rate 20: Users spawned per second

This configuration ensures a total of 1,000 transactions per minute (200 users x 5 transactions per minute each).

📜 License

MIT

