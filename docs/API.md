# API Standards

The system is primarily a server-rendered Laravel web application using Blade. Version 1.0 does not require a public JSON API, and the architecture should avoid introducing one prematurely.

## 1. API Scope

- Use Laravel routes and controllers for all requests
- Keep the web application session-based for the initial release
- If an API layer is introduced later, version it under /api/v1
- Do not add API endpoints for features that are not described in the PRD
- Use JSON responses only for AJAX or future API-driven features, not as the primary interface for the initial release

## 2. Response Format

All JSON responses should use a consistent envelope:

```json
{
  "success": true,
  "message": "Operation completed successfully",
  "data": {}
}
```

For list endpoints:

```json
{
  "success": true,
  "message": "Records retrieved successfully",
  "data": [
    { "id": 1, "name": "JSS1A" }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 42
  }
}
```

## 3. Error Handling

Errors should be returned in a predictable structure:

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."]
  }
}
```

### Standard HTTP status codes
- 200 OK for successful read and update operations
- 201 Created for successful creation
- 204 No Content for successful delete operations where appropriate
- 400 Bad Request for malformed requests
- 401 Unauthorized for unauthenticated access
- 403 Forbidden for insufficient permissions
- 404 Not Found for missing resources
- 422 Unprocessable Entity for validation failures
- 500 Internal Server Error for unexpected application failures

## 4. Validation Response Format

Validation failures should be handled by Form Requests and returned as structured JSON errors when API endpoints are used.

Recommended behavior:
- Return 422 for validation errors
- Provide clear field-level messages
- Avoid leaking sensitive implementation details

## 5. Authentication Conventions

For the initial release:
- Use session-based authentication through the web guard
- Keep authentication and authorization tied to the Laravel web experience
- Use middleware and policies for role enforcement
- Apply rate limiting to login and password-reset endpoints

If a future JSON API is introduced:
- Keep the authentication mechanism consistent with the existing Laravel auth guard
- Avoid introducing unnecessary token complexity in version 1.0

## 6. Pagination Standard

All list endpoints should paginate results.

Recommended default:
- 15 records per page
- Use Laravel paginate()
- Preserve query string parameters when building pagination links
- Return page metadata in the response body

## 7. Controller and Response Conventions

- Keep controllers thin and use services for business logic
- Return resources or arrays from services in a consistent shape
- Use route names and resource conventions for maintainability
- Prefer explicit response shapes over ad-hoc arrays
- Ensure that role-based access rules are enforced before data is returned
