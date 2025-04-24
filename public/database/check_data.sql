-- Check data in users table
SELECT COUNT(*) as user_count FROM users;

-- Check data in api_keys table
SELECT COUNT(*) as api_key_count FROM api_keys;

-- Check data in api_requests table
SELECT COUNT(*) as api_request_count FROM api_requests;

-- Check data in rate_limits table
SELECT COUNT(*) as rate_limit_count FROM rate_limits;

-- Check data in remember_tokens table
SELECT COUNT(*) as remember_token_count FROM remember_tokens;

-- Check sample data from each table
SELECT * FROM users LIMIT 5;
SELECT * FROM api_keys LIMIT 5;
SELECT * FROM api_requests LIMIT 5;
SELECT * FROM rate_limits LIMIT 5;
SELECT * FROM remember_tokens LIMIT 5; 