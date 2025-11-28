#!/bin/bash

# Test Script for Task 4: Automatic Account Locking
# This script tests failed login attempts, auto-lock, and reset functionality

API_URL="http://localhost:8000/api"
TEST_EMAIL="testlock@example.com"
CORRECT_PASSWORD="password123"
WRONG_PASSWORD="wrongpassword"

echo "========================================="
echo "Task 4: Account Locking Verification"
echo "========================================="
echo ""

# Create a test user
echo "1. Creating test user..."
curl -s -X POST $API_URL/register \
  -H "Content-Type: application/json" \
  -d "{\"name\":\"Test Lock User\",\"email\":\"$TEST_EMAIL\",\"password\":\"$CORRECT_PASSWORD\"}" \
  | jq -r '.message' 2>/dev/null || echo "User might already exist"

echo ""
echo "2. Testing failed login attempts..."
echo "-----------------------------------"

for i in {1..5}; do
  echo "Attempt $i: Wrong password"
  RESPONSE=$(curl -s -w "\nHTTP_STATUS:%{http_code}" -X POST $API_URL/login \
    -H "Content-Type: application/json" \
    -d "{\"email\":\"$TEST_EMAIL\",\"password\":\"$WRONG_PASSWORD\"}")
  
  HTTP_STATUS=$(echo "$RESPONSE" | grep HTTP_STATUS | cut -d: -f2)
  BODY=$(echo "$RESPONSE" | sed -e 's/HTTP_STATUS.*//')
  
  echo "  Status: $HTTP_STATUS"
  echo "  Response: $(echo $BODY | jq -r '.message')"
  
  if [ "$i" -eq 5 ]; then
    if [ "$HTTP_STATUS" = "423" ]; then
      echo "  ✅ Account locked after 5 attempts!"
    else
      echo "  ❌ Expected status 423, got $HTTP_STATUS"
    fi
  else
    if [ "$HTTP_STATUS" = "401" ]; then
      echo "  ✅ Correct - invalid credentials"
    else
      echo "  ❌ Expected status 401, got $HTTP_STATUS"
    fi
  fi
  echo ""
done

echo "3. Testing locked account cannot login (even with correct password)..."
echo "-----------------------------------------------------------------------"
RESPONSE=$(curl -s -w "\nHTTP_STATUS:%{http_code}" -X POST $API_URL/login \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"$TEST_EMAIL\",\"password\":\"$CORRECT_PASSWORD\"}")

HTTP_STATUS=$(echo "$RESPONSE" | grep HTTP_STATUS | cut -d: -f2)
BODY=$(echo "$RESPONSE" | sed -e 's/HTTP_STATUS.*//')

echo "Status: $HTTP_STATUS"
echo "Response: $(echo $BODY | jq -r '.message')"

if [ "$HTTP_STATUS" = "423" ]; then
  echo "✅ Locked account correctly rejects login!"
else
  echo "❌ Expected status 423, got $HTTP_STATUS"
fi

echo ""
echo "4. Checking database state..."
echo "------------------------------"
php -r "
require 'vendor/autoload.php';
\$app = require_once 'bootstrap/app.php';
\$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

\$user = App\Models\User::where('email', '$TEST_EMAIL')->first();
if (\$user) {
    echo 'Email: ' . \$user->email . PHP_EOL;
    echo 'Failed attempts: ' . \$user->failed_login_attempts . PHP_EOL;
    echo 'Is locked: ' . (\$user->is_locked ? 'Yes' : 'No') . PHP_EOL;
    echo 'Locked at: ' . (\$user->locked_at ? \$user->locked_at : 'null') . PHP_EOL;
    
    if (\$user->failed_login_attempts >= 5 && \$user->is_locked) {
        echo '✅ Database state is correct!' . PHP_EOL;
    } else {
        echo '❌ Database state incorrect!' . PHP_EOL;
    }
} else {
    echo '❌ User not found!' . PHP_EOL;
}
"

echo ""
echo "========================================="
echo "Test Summary Complete"
echo "========================================="
