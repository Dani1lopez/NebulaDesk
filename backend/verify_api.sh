#!/bin/bash

BASE_URL="http://localhost:8000/api"

echo "--- 1. Login as Admin ---"
LOGIN_RESPONSE=$(curl -s -X POST "$BASE_URL/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@nebuladesk.com","password":"Admin123!"}')

TOKEN=$(echo $LOGIN_RESPONSE | grep -o '"token":"[^"]*' | cut -d'"' -f4)
ORG_ID=$(echo $LOGIN_RESPONSE | grep -o '"organization_id":[^,]*' | cut -d':' -f2)

if [ -z "$TOKEN" ]; then
  echo "Login failed. Response: $LOGIN_RESPONSE"
  exit 1
fi

echo "Token: $TOKEN"
echo "Org ID: $ORG_ID"

echo -e "\n--- 2. Create Ticket (Admin) ---"
curl -s -X POST "$BASE_URL/tickets" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "subject":"Test Ticket from Script",
    "description":"This is a test ticket created via curl",
    "priority":"high",
    "organization_id":'$ORG_ID'
  }'

echo -e "\n\n--- 3. List Tickets (Admin) ---"
curl -s -X GET "$BASE_URL/tickets?organization_id=$ORG_ID" \
  -H "Authorization: Bearer $TOKEN"

echo -e "\n\n--- 4. Create Organization (Admin) ---"
curl -s -X POST "$BASE_URL/organizations" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"name":"New Test Org '$(date +%s)'"}'

echo -e "\n\n--- 5. Invite User (Admin) ---"
curl -s -X POST "$BASE_URL/organization/users" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "name":"Test Agent",
    "email":"agent_test_'$(date +%s)'@example.com",
    "role":"agent"
  }'

echo -e "\n\n--- Verification Complete ---"
