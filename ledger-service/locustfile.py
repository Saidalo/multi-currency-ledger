from locust import HttpUser, task, between
import json
import random
import uuid

class LedgerUser(HttpUser):
    wait_time = between(0.1, 0.5)
    host = "http://localhost:8081"

    def on_start(self):
        """Creates a ledger before testing transactions."""
        response = self.client.post("/ledgers", json={"currency": "USD"})
        if response.status_code == 201:
            self.ledger_id = response.json()["ledgerId"]
        else:
            self.ledger_id = None

    @task
    def create_transaction(self):
        """Send 1,000 transactions per minute."""
        if self.ledger_id:
            self.client.post("/transactions", json={
                "ledgerId": self.ledger_id,
                "transactionType": random.choice(["credit", "debit"]),
                "amount": round(random.uniform(1, 500), 2),
                "currency": random.choice(["USD", "EUR", "GBP", "CZK", "SEK"])
            })
